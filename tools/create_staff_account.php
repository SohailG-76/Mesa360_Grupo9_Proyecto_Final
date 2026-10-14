<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (function_exists('posix_geteuid') && posix_geteuid() !== 0) {
    fwrite(STDERR, "Run with sudo: sudo php create_staff_account.php Cajera|Mesera\n"); exit(1);
}
$roleName = $argv[1] ?? '';
if (!in_array($roleName, ['Cajera','Mesera'], true)) {
    fwrite(STDERR, "Usage: sudo php create_staff_account.php Cajera|Mesera\n"); exit(1);
}
$pdo = new PDO('mysql:host=localhost;dbname=mesa360_database;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$roleStmt = $pdo->prepare('SELECT id_rol FROM ROL WHERE LOWER(nombre_rol)=LOWER(:role) ORDER BY id_rol');
$roleStmt->execute(['role'=>$roleName]);
$roleIds = array_map('intval', $roleStmt->fetchAll(PDO::FETCH_COLUMN));
if (count($roleIds) !== 1) {
    fwrite(STDERR, "Expected exactly one {$roleName} role. Run setup_role_permissions.php first and resolve duplicate role names if present.\n"); exit(1);
}
$roleId = $roleIds[0];

$cargo = $roleName;
$stmt = $pdo->prepare(
    'SELECT e.id_empleado, e.nombre, e.cargo, e.telefono, e.correo
       FROM EMPLEADO e
       LEFT JOIN USUARIO u ON u.id_empleado = e.id_empleado
      WHERE COALESCE(e.activo, 1) = 1
        AND LOWER(TRIM(e.cargo)) = LOWER(:cargo)
        AND u.id_usuario IS NULL
      ORDER BY e.nombre'
);
$stmt->execute(['cargo'=>$cargo]);
$employees = $stmt->fetchAll();
if ($employees === []) {
    $any = $pdo->prepare('SELECT COUNT(*) FROM EMPLEADO WHERE COALESCE(activo,1)=1 AND LOWER(TRIM(cargo))=LOWER(:cargo)');
    $any->execute(['cargo'=>$cargo]);
    if ((int)$any->fetchColumn() > 0) {
        fwrite(STDERR, "Every active employee with cargo {$roleName} already has a user account. No records changed.\n");
    } else {
        fwrite(STDERR, "No active employee with cargo '{$roleName}' was found. Add/activate an employee with that cargo in Mesa360 first, then rerun this command. No records changed.\n");
    }
    exit(1);
}

echo "Create a Mesa360 login for role: {$roleName}\n";
echo "Choose an active employee who does not already have a login:\n";
foreach ($employees as $emp) {
    echo "  ID {$emp['id_empleado']} — {$emp['nombre']} ({$emp['cargo']})";
    if (!empty($emp['correo'])) echo " — {$emp['correo']}";
    echo "\n";
}
function promptLine(string $prompt): string { fwrite(STDOUT, $prompt); $s=fgets(STDIN); return $s === false ? '' : trim($s); }
function promptHidden(string $prompt): string {
    fwrite(STDOUT, $prompt);
    $tty = function_exists('posix_isatty') ? @posix_isatty(STDIN) : false;
    if ($tty) @shell_exec('stty -echo');
    $s = fgets(STDIN);
    if ($tty) @shell_exec('stty echo');
    fwrite(STDOUT, PHP_EOL);
    return $s === false ? '' : rtrim($s, "\r\n");
}
$idText = promptLine('Employee ID to link: ');
$id = filter_var($idText, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
$selected = null;
foreach ($employees as $emp) if ((int)$emp['id_empleado'] === (int)$id) $selected = $emp;
if (!$selected) { fwrite(STDERR, "Invalid employee selection. No records changed.\n"); exit(1); }
$username = promptLine('New username (3-50 letters/numbers/._-): ');
if (!preg_match('/\A[A-Za-z0-9._-]{3,50}\z/', $username)) { fwrite(STDERR, "Invalid username. No records changed.\n"); exit(1); }
$check = $pdo->prepare('SELECT id_usuario FROM USUARIO WHERE nombre_usuario=:u OR id_empleado=:e LIMIT 1');
$check->execute(['u'=>$username, 'e'=>(int)$selected['id_empleado']]);
if ($check->fetch()) { fwrite(STDERR, "That username or employee already has an account. No records changed.\n"); exit(1); }
$password = promptHidden('Password (minimum 12 characters): ');
$confirm = promptHidden('Confirm password: ');
if (strlen($password) < 12) { fwrite(STDERR, "Password must contain at least 12 characters. No records changed.\n"); exit(1); }
if (!hash_equals($password,$confirm)) { fwrite(STDERR, "Passwords do not match. No records changed.\n"); exit(1); }
$hash = password_hash($password, PASSWORD_DEFAULT);
unset($password, $confirm);
if (!is_string($hash)) { fwrite(STDERR, "Password hashing failed. No records changed.\n"); exit(1); }
try {
    $pdo->beginTransaction();
    // Re-check inside the transaction so the employee cannot be linked twice.
    $check->execute(['u'=>$username, 'e'=>(int)$selected['id_empleado']]);
    if ($check->fetch()) throw new RuntimeException('Username or employee already has an account.');
    $insert = $pdo->prepare('INSERT INTO USUARIO (nombre_usuario, contrasena, id_empleado, id_rol, activo, fecha_creacion, ultimo_acceso, intentos_fallidos) VALUES (:username,:hash,:employee,:role,1,NOW(),NULL,0)');
    $insert->execute(['username'=>$username,'hash'=>$hash,'employee'=>(int)$selected['id_empleado'],'role'=>$roleId]);
    $pdo->commit();
    echo "Account created successfully.\nUsername: {$username}\nEmployee: {$selected['nombre']}\nRole: {$roleName}\nPassword stored as a password hash.\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "Account creation failed: " . $e->getMessage() . "\n"); exit(1);
}
