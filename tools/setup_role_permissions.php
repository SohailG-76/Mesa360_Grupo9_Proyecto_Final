<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (function_exists('posix_geteuid') && posix_geteuid() !== 0) {
    fwrite(STDERR, "Run with sudo: sudo php setup_role_permissions.php\n"); exit(1);
}

$pdo = new PDO('mysql:host=localhost;dbname=mesa360_database;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$counts = $pdo->query("SELECT
    (SELECT COUNT(*) FROM USUARIO) AS users,
    (SELECT COUNT(*) FROM EMPLEADO) AS employees,
    (SELECT COUNT(*) FROM ROL WHERE LOWER(nombre_rol) IN ('administrador','cajera','mesera')) AS target_roles,
    (SELECT COUNT(*) FROM USUARIO u JOIN ROL r ON r.id_rol=u.id_rol WHERE LOWER(r.nombre_rol) IN ('cajera','mesera')) AS staff_users")->fetch();
echo "Mesa360 role and permission setup\n";
echo "Current users: {$counts['users']}; employees: {$counts['employees']}; existing target roles: {$counts['target_roles']}; existing Cajera/Mesera accounts: {$counts['staff_users']}\n";
echo "This does not create or change user accounts. It ensures role records, assigns permission profiles, and gives the application read-only access to PERMISO.\n";
echo "The permission records for the Cajera and Mesera roles will be replaced by the profiles below. Existing staff accounts using those roles will receive the matching access profile.\n\n";
fwrite(STDOUT, "Type CONFIGURE MESA360 ROLES to continue: ");
$confirm = trim((string) fgets(STDIN));
if ($confirm !== 'CONFIGURE MESA360 ROLES') { echo "Cancelled. No role permissions were changed.\n"; exit(0); }

$allPermissions = [
    'dashboard' => ['ver'],
    'pedidos' => ['ver','crear','editar','eliminar'],
    'mesas' => ['ver','crear','editar','eliminar'],
    'menu' => ['ver','crear','editar','eliminar'],
    'inventario' => ['ver','crear','editar','eliminar'],
    'clientes' => ['ver','crear','editar','eliminar'],
    'reservas' => ['ver','crear','editar','eliminar'],
    'empleados' => ['ver','crear','editar','eliminar'],
    'reportes' => ['ver'],
    'recetas' => ['ver','crear','editar','eliminar'],
    'movimientos' => ['ver','registrar'],
    'pagos' => ['ver','registrar','gestionar_metodos','crear','editar','eliminar'],
];

$cajera = [
    'dashboard' => ['ver'],
    'pedidos' => ['ver','crear','editar'],
    'mesas' => ['ver'],
    'menu' => ['ver'],
    'clientes' => ['ver','crear','editar'],
    'reservas' => ['ver'],
    'reportes' => ['ver'],
    'pagos' => ['ver','registrar'],
];
$mesera = [
    'dashboard' => ['ver'],
    'pedidos' => ['ver','crear','editar'],
    'mesas' => ['ver','editar'], // Edit permission is restricted in the PHP handler to table status only.
    'menu' => ['ver'],
    'clientes' => ['ver','crear','editar'],
    'reservas' => ['ver','crear','editar'],
];

function ensureRole(PDO $pdo, string $name, string $description): int {
    $find = $pdo->prepare('SELECT id_rol FROM ROL WHERE LOWER(nombre_rol) = LOWER(:name) ORDER BY id_rol');
    $find->execute(['name' => $name]);
    $ids = array_map('intval', $find->fetchAll(PDO::FETCH_COLUMN));
    if (count($ids) > 1) {
        throw new RuntimeException("More than one role named {$name} exists. Resolve the duplicate roles manually before continuing.");
    }
    if ($ids !== []) return $ids[0];
    $insert = $pdo->prepare('INSERT INTO ROL (nombre_rol, descripcion) VALUES (:name, :description)');
    $insert->execute(['name' => $name, 'description' => $description]);
    return (int)$pdo->lastInsertId();
}
function permissionPairs(array $map): array {
    $pairs = [];
    foreach ($map as $module => $actions) foreach ($actions as $action) $pairs[] = [$module, $action];
    return $pairs;
}
function insertMissingPermissions(PDO $pdo, int $roleId, array $map): int {
    $check = $pdo->prepare('SELECT id_permiso FROM PERMISO WHERE id_rol = :role AND modulo = :module AND accion = :action LIMIT 1');
    $insert = $pdo->prepare('INSERT INTO PERMISO (id_rol, modulo, accion) VALUES (:role, :module, :action)');
    $added = 0;
    foreach (permissionPairs($map) as [$module, $action]) {
        $check->execute(['role'=>$roleId,'module'=>$module,'action'=>$action]);
        if (!$check->fetchColumn()) { $insert->execute(['role'=>$roleId,'module'=>$module,'action'=>$action]); $added++; }
    }
    return $added;
}

try {
    // This is the only additional privilege needed by the PHP app to enforce the role matrix.
    $pdo->exec("GRANT SELECT ON mesa360_database.PERMISO TO 'mesa360_app'@'localhost'");
    $pdo->beginTransaction();
    $adminId = ensureRole($pdo, 'Administrador', 'Acceso completo a la administración de Mesa360.');
    $cajeraId = ensureRole($pdo, 'Cajera', 'Gestiona pedidos, cobros y consultas de ventas.');
    $meseraId = ensureRole($pdo, 'Mesera', 'Gestiona pedidos, clientes, reservas y estados de mesa.');

    // Preserve any existing admin permission rows and add missing full access entries.
    $adminAdded = insertMissingPermissions($pdo, $adminId, $allPermissions);

    // These two profiles are intentionally exact: remove stale grants for only these role records.
    $wipe = $pdo->prepare('DELETE FROM PERMISO WHERE id_rol = :role');
    $wipe->execute(['role' => $cajeraId]);
    insertMissingPermissions($pdo, $cajeraId, $cajera);
    $wipe->execute(['role' => $meseraId]);
    insertMissingPermissions($pdo, $meseraId, $mesera);

    $pdo->commit();
    echo "Role and permission setup completed.\n";
    echo "Administrador role ID: {$adminId} (full access; existing permission entries preserved and missing ones added: {$adminAdded}).\n";
    echo "Cajera role ID: {$cajeraId} (orders, customers, read-only menu/tables/reservations, payments and reports).\n";
    echo "Mesera role ID: {$meseraId} (orders, customers, reservations, menu and table status only).\n";
    echo "GRANT SELECT on PERMISO assigned to mesa360_app.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "Role setup failed; transaction rolled back where applicable: " . $e->getMessage() . "\n");
    exit(1);
}
