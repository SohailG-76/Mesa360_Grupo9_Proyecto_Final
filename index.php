<?php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
session_name('mesa360_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/* MESA360_AUTH_GUARD */
if (empty($_SESSION['mesa360_user_id']) || empty($_SESSION['mesa360_username'])) {
    header('Location: /login.php', true, 303);
    exit;
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$pageTitles = [
    'dashboard' => 'Panel de control',
    'pedidos' => 'Pedidos',
    'mesas' => 'Mesas',
    'menu' => 'Menú y categorías',
    'inventario' => 'Inventario',
    'clientes' => 'Clientes',
    'reservas' => 'Reservas',
    'empleados' => 'Empleados',
    'reportes' => 'Reportes',
    'recetas' => 'Recetas',
    'movimientos' => 'Movimientos de inventario',
    'pagos' => 'Pagos',
];

$navigation = [
    ['page' => 'dashboard', 'label' => 'Panel de control', 'icon' => '⌂'],
    ['page' => 'pedidos', 'label' => 'Pedidos', 'icon' => '≡'],
    ['page' => 'mesas', 'label' => 'Mesas', 'icon' => '▦'],
    ['page' => 'menu', 'label' => 'Menú y categorías', 'icon' => '◈'],
    ['page' => 'inventario', 'label' => 'Inventario', 'icon' => '▤'],
    ['page' => 'clientes', 'label' => 'Clientes', 'icon' => '♙'],
    ['page' => 'reservas', 'label' => 'Reservas', 'icon' => '◷'],
    ['page' => 'empleados', 'label' => 'Empleados', 'icon' => '♧'],
    ['page' => 'recetas', 'label' => 'Recetas', 'icon' => '♨'],
    ['page' => 'movimientos', 'label' => 'Movimientos', 'icon' => '↕'],
    ['page' => 'pagos', 'label' => 'Pagos', 'icon' => '$'],
    ['page' => 'reportes', 'label' => 'Reportes', 'icon' => '▥'],
];


$entityModules = [
    'clientes' => [
        'table' => 'CLIENTE', 'pk' => 'id_cliente', 'title' => 'Clientes', 'singular' => 'cliente',
        'description' => 'Administra los datos de contacto y las observaciones de tus clientes.',
        'newLabel' => 'Nuevo cliente', 'searchPlaceholder' => 'Buscar por nombre, teléfono o correo...',
        'listSql' => 'SELECT c.id_cliente, c.nombre, c.telefono, c.correo, c.observaciones FROM CLIENTE c WHERE 1=1',
        'searchColumns' => ['c.nombre', 'COALESCE(c.telefono, "")', 'COALESCE(c.correo, "")'],
        'orderBy' => 'c.id_cliente DESC',
        'columns' => [
            ['key' => 'nombre', 'label' => 'Cliente', 'format' => 'primary'],
            ['key' => 'telefono', 'label' => 'Teléfono'],
            ['key' => 'correo', 'label' => 'Correo electrónico'],
        ],
        'fields' => [
            'nombre' => ['label' => 'Nombre completo', 'type' => 'text', 'required' => true, 'maxlength' => 255],
            'telefono' => ['label' => 'Teléfono', 'type' => 'text', 'required' => false, 'maxlength' => 255],
            'correo' => ['label' => 'Correo electrónico', 'type' => 'email', 'required' => false, 'maxlength' => 255],
            'observaciones' => ['label' => 'Observaciones', 'type' => 'textarea', 'required' => false, 'full' => true],
        ],
        'defaults' => ['nombre' => '', 'telefono' => '', 'correo' => '', 'observaciones' => ''],
    ],
    'empleados' => [
        'table' => 'EMPLEADO', 'pk' => 'id_empleado', 'title' => 'Empleados', 'singular' => 'empleado',
        'description' => 'Mantén actualizados los datos del personal y su situación laboral.',
        'newLabel' => 'Nuevo empleado', 'searchPlaceholder' => 'Buscar por nombre, cédula, cargo o correo...',
        'listSql' => 'SELECT e.id_empleado, e.cedula, e.nombre, e.telefono, e.correo, e.cargo, e.fecha_contratacion, e.fecha_nacimiento, e.direccion, e.activo FROM EMPLEADO e WHERE 1=1',
        'searchColumns' => ['e.nombre', 'COALESCE(e.cedula, "")', 'COALESCE(e.cargo, "")', 'COALESCE(e.correo, "")'],
        'orderBy' => 'e.id_empleado DESC',
        'columns' => [
            ['key' => 'nombre', 'label' => 'Empleado', 'format' => 'primary'],
            ['key' => 'cedula', 'label' => 'Cédula'],
            ['key' => 'cargo', 'label' => 'Cargo'],
            ['key' => 'telefono', 'label' => 'Teléfono'],
            ['key' => 'correo', 'label' => 'Correo electrónico'],
            ['key' => 'activo', 'label' => 'Estado', 'format' => 'active'],
        ],
        'fields' => [
            'nombre' => ['label' => 'Nombre completo', 'type' => 'text', 'required' => true, 'maxlength' => 100],
            'cedula' => ['label' => 'Cédula', 'type' => 'text', 'required' => false, 'maxlength' => 20],
            'cargo' => ['label' => 'Cargo', 'type' => 'text', 'required' => true, 'maxlength' => 50],
            'telefono' => ['label' => 'Teléfono', 'type' => 'text', 'required' => false, 'maxlength' => 20],
            'correo' => ['label' => 'Correo electrónico', 'type' => 'email', 'required' => false, 'maxlength' => 100],
            'fecha_contratacion' => ['label' => 'Fecha de contratación', 'type' => 'date', 'required' => false],
            'fecha_nacimiento' => ['label' => 'Fecha de nacimiento', 'type' => 'date', 'required' => false],
            'direccion' => ['label' => 'Dirección', 'type' => 'textarea', 'required' => false, 'full' => true],
            'activo' => ['label' => 'Empleado activo', 'type' => 'checkbox', 'required' => false, 'default' => 1, 'full' => true],
        ],
        'defaults' => ['nombre' => '', 'cedula' => '', 'cargo' => '', 'telefono' => '', 'correo' => '', 'fecha_contratacion' => '', 'fecha_nacimiento' => '', 'direccion' => '', 'activo' => 1],
    ],
    'mesas' => [
        'table' => 'MESA', 'pk' => 'id_mesa', 'title' => 'Mesas', 'singular' => 'mesa',
        'description' => 'Organiza las mesas del restaurante, su capacidad y disponibilidad.',
        'newLabel' => 'Nueva mesa', 'searchPlaceholder' => 'Buscar por número, ubicación o estado...',
        'listSql' => 'SELECT m.id_mesa, m.numero_mesa, m.capacidad, m.ubicacion, m.estado, m.activo FROM MESA m WHERE 1=1',
        'searchColumns' => ['CAST(m.numero_mesa AS CHAR)', 'COALESCE(m.ubicacion, "")', 'm.estado'],
        'orderBy' => 'm.numero_mesa ASC, m.id_mesa ASC',
        'columns' => [
            ['key' => 'numero_mesa', 'label' => 'Mesa', 'format' => 'primary'],
            ['key' => 'ubicacion', 'label' => 'Ubicación'],
            ['key' => 'capacidad', 'label' => 'Capacidad'],
            ['key' => 'estado', 'label' => 'Disponibilidad', 'format' => 'status'],
            ['key' => 'activo', 'label' => 'Estado', 'format' => 'active'],
        ],
        'fields' => [
            'numero_mesa' => ['label' => 'Número de mesa', 'type' => 'number', 'required' => true, 'min' => 1, 'integer' => true],
            'capacidad' => ['label' => 'Capacidad (personas)', 'type' => 'number', 'required' => true, 'min' => 1, 'integer' => true],
            'ubicacion' => ['label' => 'Ubicación', 'type' => 'text', 'required' => false, 'maxlength' => 255],
            'estado' => ['label' => 'Disponibilidad', 'type' => 'select', 'required' => true, 'options' => ['Disponible' => 'Disponible', 'Ocupada' => 'Ocupada', 'Reservada' => 'Reservada']],
            'activo' => ['label' => 'Mesa habilitada para el servicio', 'type' => 'checkbox', 'required' => false, 'default' => 1, 'full' => true],
        ],
        'defaults' => ['numero_mesa' => '', 'capacidad' => '2', 'ubicacion' => '', 'estado' => 'Disponible', 'activo' => 1],
    ],
    'reservas' => [
        'table' => 'RESERVA', 'pk' => 'id_reserva', 'title' => 'Reservas', 'singular' => 'reserva',
        'description' => 'Registra las reservas y consulta el cliente, la mesa, la fecha y el estado.',
        'newLabel' => 'Nueva reserva', 'searchPlaceholder' => 'Buscar por cliente, mesa o estado...',
        'listSql' => 'SELECT r.id_reserva, r.id_cliente, r.id_mesa, r.fecha_reserva, r.hora_reserva, r.cantidad_personas, r.estado, r.observaciones, r.fecha_creacion, c.nombre AS cliente_nombre, m.numero_mesa FROM RESERVA r LEFT JOIN CLIENTE c ON c.id_cliente = r.id_cliente LEFT JOIN MESA m ON m.id_mesa = r.id_mesa WHERE 1=1',
        'searchColumns' => ['COALESCE(c.nombre, "")', 'CAST(m.numero_mesa AS CHAR)', 'r.estado', 'CAST(r.fecha_reserva AS CHAR)'],
        'orderBy' => 'r.fecha_reserva DESC, r.hora_reserva DESC, r.id_reserva DESC',
        'columns' => [
            ['key' => 'fecha_reserva', 'label' => 'Fecha', 'format' => 'date', 'primary' => true],
            ['key' => 'hora_reserva', 'label' => 'Hora', 'format' => 'time'],
            ['key' => 'cliente_nombre', 'label' => 'Cliente'],
            ['key' => 'numero_mesa', 'label' => 'Mesa'],
            ['key' => 'cantidad_personas', 'label' => 'Personas'],
            ['key' => 'estado', 'label' => 'Estado', 'format' => 'reservation-status'],
        ],
        'fields' => [
            'id_cliente' => ['label' => 'Cliente', 'type' => 'select', 'required' => true, 'source' => 'clientes', 'sourceCheckSql' => 'SELECT 1 FROM CLIENTE WHERE id_cliente = :id', 'full' => false],
            'id_mesa' => ['label' => 'Mesa disponible', 'type' => 'select', 'required' => true, 'source' => 'mesas', 'sourceCheckSql' => 'SELECT 1 FROM MESA WHERE id_mesa = :id AND COALESCE(activo, 1) = 1', 'full' => false],
            'fecha_reserva' => ['label' => 'Fecha de reserva', 'type' => 'date', 'required' => true],
            'hora_reserva' => ['label' => 'Hora de reserva', 'type' => 'time', 'required' => true],
            'cantidad_personas' => ['label' => 'Cantidad de personas', 'type' => 'number', 'required' => true, 'min' => 1, 'integer' => true],
            'estado' => ['label' => 'Estado de la reserva', 'type' => 'select', 'required' => true, 'options' => ['Pendiente' => 'Pendiente', 'Confirmada' => 'Confirmada', 'Cancelada' => 'Cancelada']],
            'observaciones' => ['label' => 'Observaciones', 'type' => 'textarea', 'required' => false, 'full' => true],
        ],
        'defaults' => ['id_cliente' => '', 'id_mesa' => '', 'fecha_reserva' => '', 'hora_reserva' => '', 'cantidad_personas' => '2', 'estado' => 'Pendiente', 'observaciones' => ''],
        'insertCreatedAt' => true,
    ],
    'inventario' => [
        'table' => 'INSUMO', 'pk' => 'id_insumo', 'title' => 'Inventario', 'singular' => 'insumo',
        'description' => 'Administra los ingredientes, proveedores, costos y niveles mínimos de existencias.',
        'newLabel' => 'Nuevo insumo', 'searchPlaceholder' => 'Buscar por insumo, unidad o proveedor...',
        'listSql' => 'SELECT i.id_insumo, i.nombre_insumo, i.unidad_medida, i.stock_actual, i.stock_minimo, i.costo_unitario, i.proveedor_habitual, i.activo FROM INSUMO i WHERE 1=1',
        'searchColumns' => ['i.nombre_insumo', 'COALESCE(i.unidad_medida, "")', 'COALESCE(i.proveedor_habitual, "")'],
        'orderBy' => 'i.nombre_insumo ASC, i.id_insumo DESC',
        'columns' => [
            ['key' => 'nombre_insumo', 'label' => 'Insumo', 'format' => 'primary'],
            ['key' => 'unidad_medida', 'label' => 'Unidad'], ['key' => 'stock_actual', 'label' => 'Existencia'],
            ['key' => 'stock_minimo', 'label' => 'Stock mínimo'], ['key' => 'costo_unitario', 'label' => 'Costo unitario', 'format' => 'money'],
            ['key' => 'proveedor_habitual', 'label' => 'Proveedor'], ['key' => 'activo', 'label' => 'Estado', 'format' => 'active'],
        ],
        'fields' => [
            'nombre_insumo' => ['label' => 'Nombre del insumo', 'type' => 'text', 'required' => true, 'maxlength' => 255],
            'unidad_medida' => ['label' => 'Unidad de medida', 'type' => 'text', 'required' => true, 'maxlength' => 255],
            'stock_actual' => ['label' => 'Existencia actual (se actualiza mediante movimientos)', 'type' => 'number', 'required' => true, 'min' => 0, 'step' => '0.01'],
            'stock_minimo' => ['label' => 'Nivel mínimo de existencias', 'type' => 'number', 'required' => true, 'min' => 0, 'step' => '0.01'],
            'costo_unitario' => ['label' => 'Costo unitario (RD$)', 'type' => 'number', 'required' => true, 'min' => 0, 'step' => '0.01'],
            'proveedor_habitual' => ['label' => 'Proveedor habitual', 'type' => 'text', 'required' => false, 'maxlength' => 255],
            'activo' => ['label' => 'Insumo activo', 'type' => 'checkbox', 'required' => false, 'default' => 1, 'full' => true],
        ],
        'defaults' => ['nombre_insumo' => '', 'unidad_medida' => '', 'stock_actual' => '0.00', 'stock_minimo' => '0.00', 'costo_unitario' => '0.00', 'proveedor_habitual' => '', 'activo' => 1],
    ],
    'recetas' => [
        'table' => 'RECETA', 'pk' => 'id_receta', 'title' => 'Recetas', 'singular' => 'ingrediente de receta',
        'description' => 'Define qué insumos y cantidades necesita cada producto del menú.',
        'newLabel' => 'Agregar ingrediente', 'searchPlaceholder' => 'Buscar por producto o insumo...',
        'listSql' => 'SELECT r.id_receta, r.id_producto, r.id_insumo, r.cantidad_requerida, r.unidad_medida, p.nombre AS producto_nombre, i.nombre_insumo FROM RECETA r JOIN PRODUCTO p ON p.id_producto = r.id_producto JOIN INSUMO i ON i.id_insumo = r.id_insumo WHERE 1=1',
        'searchColumns' => ['p.nombre', 'i.nombre_insumo'], 'orderBy' => 'p.nombre ASC, i.nombre_insumo ASC',
        'columns' => [
            ['key' => 'producto_nombre', 'label' => 'Producto', 'format' => 'primary'],
            ['key' => 'nombre_insumo', 'label' => 'Insumo'], ['key' => 'cantidad_requerida', 'label' => 'Cantidad requerida'], ['key' => 'unidad_medida', 'label' => 'Unidad'],
        ],
        'fields' => [
            'id_producto' => ['label' => 'Producto del menú', 'type' => 'select', 'required' => true, 'source' => 'productos', 'sourceCheckSql' => 'SELECT 1 FROM PRODUCTO WHERE id_producto = :id AND COALESCE(activo, 1) = 1'],
            'id_insumo' => ['label' => 'Insumo', 'type' => 'select', 'required' => true, 'source' => 'insumos', 'sourceCheckSql' => 'SELECT 1 FROM INSUMO WHERE id_insumo = :id AND COALESCE(activo, 1) = 1'],
            'cantidad_requerida' => ['label' => 'Cantidad requerida', 'type' => 'number', 'required' => true, 'min' => 0.01, 'step' => '0.01'],
            'unidad_medida' => ['label' => 'Unidad de receta', 'type' => 'text', 'required' => true, 'maxlength' => 255],
        ],
        'defaults' => ['id_producto' => '', 'id_insumo' => '', 'cantidad_requerida' => '1.00', 'unidad_medida' => ''],
    ],
];

$page = isset($_GET['page']) && is_string($_GET['page']) ? $_GET['page'] : 'dashboard';
if (!array_key_exists($page, $pageTitles)) {
    $page = 'dashboard';
}

function h(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function inputString(array $source, string $key, string $default = ''): string
{
    $value = $source[$key] ?? $default;
    return is_scalar($value) ? trim((string) $value) : '';
}

function textLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function redirectMenu(string $tab, string $formType = '', int $editId = 0): never
{
    $allowedTabs = ['products', 'categories'];
    if (!in_array($tab, $allowedTabs, true)) {
        $tab = 'products';
    }

    $params = ['page' => 'menu', 'tab' => $tab];
    if ($formType === 'product') {
        $params[$editId > 0 ? 'edit_product' : 'new_product'] = $editId > 0 ? $editId : 1;
    } elseif ($formType === 'category') {
        $params[$editId > 0 ? 'edit_category' : 'new_category'] = $editId > 0 ? $editId : 1;
    }

    header('Location: ?' . http_build_query($params), true, 303);
    exit;
}

function flashAndRedirect(
    string $type,
    string $message,
    string $tab,
    string $formType = '',
    int $editId = 0,
    array $oldInput = []
): never {
    $_SESSION['mesa360_flash'] = ['type' => $type, 'message' => $message];
    if ($oldInput !== [] && in_array($formType, ['product', 'category'], true)) {
        $_SESSION['mesa360_old_input'] = ['type' => $formType, 'values' => $oldInput];
    } else {
        unset($_SESSION['mesa360_old_input']);
    }
    redirectMenu($tab, $formType, $editId);
}


function moduleFlashRedirect(string $page, string $type, string $message, string $formMode = '', int $recordId = 0, array $oldInput = []): never
{
    $_SESSION['mesa360_flash'] = ['type' => $type, 'message' => $message];
    if ($oldInput !== []) {
        $_SESSION['mesa360_old_input'] = ['type' => 'module', 'values' => $oldInput];
    } else {
        unset($_SESSION['mesa360_old_input']);
    }
    $params = ['page' => $page];
    if ($formMode === 'new') {
        $params['new'] = 1;
    } elseif ($formMode === 'edit' && $recordId > 0) {
        $params['edit'] = $recordId;
    }
    header('Location: ?' . http_build_query($params), true, 303);
    exit;
}

function paymentFlashRedirect(string $type, string $message, string $tab = 'payments'): never
{
    if (!in_array($tab, ['payments', 'methods'], true)) {
        $tab = 'payments';
    }
    $_SESSION['mesa360_flash'] = ['type' => $type, 'message' => $message];
    header('Location: ?page=pagos&tab=' . $tab, true, 303);
    exit;
}

function moduleStatusClass(string $value): string
{
    return match ($value) {
        'Disponible', 'Confirmada', 'Activo', 'Listo', 'Servido' => 'status-active',
        'Ocupada', 'Reservada', 'Pendiente', 'Preparando' => 'status-pending',
        'Cancelada', 'Inactivo' => 'status-inactive',
        default => 'status-inactive',
    };
}

function moduleDate(string $value): string
{
    if ($value === '') {
        return '—';
    }
    $timestamp = strtotime($value);
    return $timestamp === false ? $value : date('d/m/Y', $timestamp);
}

function validMoney(string $value): bool
{
    return preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $value) === 1;
}

function normalizedMoney(string $value): string
{
    $parts = explode('.', $value, 2);
    $whole = ltrim($parts[0], '0');
    if ($whole === '') {
        $whole = '0';
    }
    $fraction = $parts[1] ?? '';
    return $whole . '.' . str_pad($fraction, 2, '0');
}

$_SESSION['mesa360_csrf_token'] ??= bin2hex(random_bytes(32));
$csrfToken = (string) $_SESSION['mesa360_csrf_token'];

$currentDate = date('d/m/Y');
$currentTitle = $pageTitles[$page];
$pdo = null;
$dbError = null;
$flash = null;
$oldInputRecord = null;

if (in_array($page, ['dashboard', 'menu', 'clientes', 'empleados', 'mesas', 'reservas', 'pedidos', 'reportes', 'inventario', 'recetas', 'movimientos', 'pagos'], true)) {
    try {
        $configPath = '/etc/mesa360/database.php';
        if (!is_readable($configPath)) {
            throw new RuntimeException('Database configuration is not readable.');
        }
        $dbConfig = require $configPath;
        if (!is_array($dbConfig)) {
            throw new RuntimeException('Database configuration is invalid.');
        }
        $dsn = 'mysql:host=' . $dbConfig['host']
            . ';dbname=' . $dbConfig['database']
            . ';charset=' . ($dbConfig['charset'] ?? 'utf8mb4');
        $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Throwable $exception) {
        error_log('[Mesa360] Menu database connection failed: ' . $exception->getMessage());
        $dbError = 'No se pudo conectar con la base de datos en este momento. Intenta nuevamente más tarde.';
        $pdo = null;
    }

/* MESA360_RBAC_BEGIN */
$mesa360RolePermissions = [];
function mesa360RbacDeny(string $message, int $status = 403): never {
    http_response_code($status);
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Acceso restringido | Mesa360</title><link rel="stylesheet" href="/assets/css/app.css"><link rel="stylesheet" href="/assets/css/auth.css"></head><body class="login-page"><main class="login-form-panel" style="min-height:100vh;display:grid;place-items:center;padding:32px"><section class="login-form-wrap"><div class="login-heading"><span class="login-eyebrow">MESA360</span><h2>Acceso restringido</h2><p>' . h($message) . '</p></div><a class="login-submit" href="/?page=dashboard">Volver al panel</a></section></main></body></html>';
    exit;
}
function mesa360HasPermission(string $module, string $action): bool {
    global $mesa360RolePermissions;
    return !empty($mesa360RolePermissions[$module][$action]);
}
if (!$pdo instanceof PDO) {
    error_log('[Mesa360] RBAC could not validate permissions because the database connection is unavailable.');
    mesa360RbacDeny('No se pudieron validar tus permisos porque la base de datos no está disponible. Inténtalo de nuevo más tarde.', 503);
}
try {
    $mesa360RoleId = (int)($_SESSION['mesa360_user_role_id'] ?? 0);
    if ($mesa360RoleId < 1) mesa360RbacDeny('Tu sesión no incluye un rol válido. Cierra sesión e inicia de nuevo.', 403);
    $mesa360PermStmt = $pdo->prepare('SELECT modulo, accion FROM PERMISO WHERE id_rol = :role_id');
    $mesa360PermStmt->execute(['role_id' => $mesa360RoleId]);
    foreach ($mesa360PermStmt->fetchAll() as $mesa360Perm) {
        $mesa360RolePermissions[(string)$mesa360Perm['modulo']][(string)$mesa360Perm['accion']] = true;
    }
} catch (Throwable $mesa360RbacException) {
    error_log('[Mesa360] RBAC permission load failed: ' . $mesa360RbacException->getMessage());
    mesa360RbacDeny('No se pudieron cargar tus permisos. Contacta al administrador del sistema.', 503);
}

if (!mesa360HasPermission($page, 'ver')) {
    mesa360RbacDeny('Tu rol no tiene permiso para acceder a este módulo. Si necesitas acceso, contacta al administrador.');
}

$mesa360ExtraGetPermission = null;
if ($page === 'menu') {
    if (isset($_GET['new_product']) || isset($_GET['new_category'])) $mesa360ExtraGetPermission = 'crear';
    if (isset($_GET['edit_product']) || isset($_GET['edit_category'])) $mesa360ExtraGetPermission = 'editar';
}
if (isset($entityModules[$page])) {
    if (isset($_GET['new'])) $mesa360ExtraGetPermission = 'crear';
    if (isset($_GET['edit'])) $mesa360ExtraGetPermission = 'editar';
}
if ($page === 'pedidos') {
    if (isset($_GET['new'])) $mesa360ExtraGetPermission = 'crear';
    if (isset($_GET['edit'])) $mesa360ExtraGetPermission = 'editar';
}
if ($page === 'pagos' && (($_GET['tab'] ?? 'payments') === 'methods' || isset($_GET['new_method']) || isset($_GET['edit_method']))) {
    $mesa360ExtraGetPermission = 'gestionar_metodos';
}
if ($mesa360ExtraGetPermission !== null && !mesa360HasPermission($page, $mesa360ExtraGetPermission)) {
    mesa360RbacDeny('Tu rol no tiene permiso para realizar esta acción.');
}

$mesa360RequiredAction = 'ver';
$mesa360ExtraPostPermission = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $mesa360PostedAction = isset($_POST['action']) && is_scalar($_POST['action']) ? trim((string)$_POST['action']) : '';
    $mesa360RecordId = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $mesa360RecordId = $mesa360RecordId === false ? 0 : (int)$mesa360RecordId;
    if ($page === 'menu') {
        if (in_array($mesa360PostedAction, ['delete_product','delete_category'], true)) $mesa360RequiredAction = 'eliminar';
        elseif (in_array($mesa360PostedAction, ['save_product','save_category'], true)) $mesa360RequiredAction = $mesa360RecordId > 0 ? 'editar' : 'crear';
        else $mesa360RequiredAction = '__invalid__';
    } elseif ($page === 'pedidos') {
        if ($mesa360PostedAction === 'delete_order') $mesa360RequiredAction = 'eliminar';
        elseif ($mesa360PostedAction === 'save_order') $mesa360RequiredAction = $mesa360RecordId > 0 ? 'editar' : 'crear';
        else $mesa360RequiredAction = '__invalid__';
    } elseif (isset($entityModules[$page])) {
        if ($mesa360PostedAction === 'delete_record') $mesa360RequiredAction = 'eliminar';
        elseif ($mesa360PostedAction === 'save_record') $mesa360RequiredAction = $mesa360RecordId > 0 ? 'editar' : 'crear';
        else $mesa360RequiredAction = '__invalid__';
    } elseif ($page === 'movimientos') {
        $mesa360RequiredAction = $mesa360PostedAction === 'record_movement' ? 'registrar' : '__invalid__';
    } elseif ($page === 'pagos') {
        if ($mesa360PostedAction === 'record_payment') $mesa360RequiredAction = 'registrar';
        elseif ($mesa360PostedAction === 'save_method') {
            $mesa360MethodId = filter_var($_POST['id_metodo_pago'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $mesa360MethodId = $mesa360MethodId === false ? 0 : (int)$mesa360MethodId;
            $mesa360RequiredAction = $mesa360MethodId > 0 ? 'editar' : 'crear';
            $mesa360ExtraPostPermission = 'gestionar_metodos';
        } elseif ($mesa360PostedAction === 'delete_method') {
            $mesa360RequiredAction = 'eliminar';
            $mesa360ExtraPostPermission = 'gestionar_metodos';
        } else $mesa360RequiredAction = '__invalid__';
    } else {
        $mesa360RequiredAction = '__invalid__';
    }
    if ($mesa360RequiredAction === '__invalid__' || !mesa360HasPermission($page, $mesa360RequiredAction)) {
        mesa360RbacDeny('Tu rol no tiene permiso para realizar esta operación.');
    }
    if ($mesa360ExtraPostPermission !== null && !mesa360HasPermission($page, $mesa360ExtraPostPermission)) {
        mesa360RbacDeny('La configuración de métodos de pago está reservada al administrador.');
    }
}
if ($page === 'pagos' && !mesa360HasPermission($page, 'gestionar_metodos')) {
    // Cashiers may use the payments tab, but cannot browse payment-method administration.
    $navigation = array_values(array_filter($navigation, static fn(array $item): bool => $item['page'] !== 'pagos' || mesa360HasPermission('pagos', 'ver')));
}
$navigation = array_values(array_filter($navigation, static fn(array $item): bool => mesa360HasPermission($item['page'], 'ver')));
/* MESA360_RBAC_END */

    if ($page === 'menu' && $_SERVER['REQUEST_METHOD'] === 'POST' && $pdo instanceof PDO) {
        $action = inputString($_POST, 'action');
        $actionTab = str_contains($action, 'category') ? 'categories' : 'products';
        $actionForm = str_contains($action, 'category') ? 'category' : (str_contains($action, 'product') ? 'product' : '');
        $recordId = filter_var(inputString($_POST, 'id', '0'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $recordId = $recordId === false ? 0 : (int) $recordId;
        $postedToken = inputString($_POST, 'csrf_token');

        if (!hash_equals($csrfToken, $postedToken)) {
            flashAndRedirect('error', 'La sesión del formulario venció. Vuelve a intentarlo.', $actionTab);
        }

        try {
            if ($action === 'save_category') {
                $name = inputString($_POST, 'nombre_categoria');
                $description = inputString($_POST, 'descripcion');
                $orderText = inputString($_POST, 'orden');
                $order = filter_var($orderText, FILTER_VALIDATE_INT);
                $active = isset($_POST['activo']) ? 1 : 0;
                $old = [
                    'nombre_categoria' => $name,
                    'descripcion' => $description,
                    'orden' => $orderText,
                    'activo' => $active,
                ];

                if ($name === '' || textLength($name) > 255) {
                    flashAndRedirect('error', 'Escribe un nombre de categoría de hasta 255 caracteres.', 'categories', 'category', $recordId, $old);
                }
                if ($order === false || $order < 0) {
                    flashAndRedirect('error', 'El orden debe ser un número entero igual o mayor que cero.', 'categories', 'category', $recordId, $old);
                }

                if ($recordId > 0) {
                    $check = $pdo->prepare('SELECT 1 FROM CATEGORIA WHERE id_categoria = :id');
                    $check->execute(['id' => $recordId]);
                    if (!$check->fetchColumn()) {
                        flashAndRedirect('error', 'La categoría que intentas editar ya no existe.', 'categories');
                    }
                    $statement = $pdo->prepare(
                        'UPDATE CATEGORIA SET nombre_categoria = :nombre, descripcion = :descripcion, orden = :orden, activo = :activo WHERE id_categoria = :id'
                    );
                    $statement->execute([
                        'nombre' => $name,
                        'descripcion' => $description !== '' ? $description : null,
                        'orden' => $order,
                        'activo' => $active,
                        'id' => $recordId,
                    ]);
                    flashAndRedirect('success', 'La categoría se actualizó correctamente.', 'categories');
                }

                $statement = $pdo->prepare(
                    'INSERT INTO CATEGORIA (nombre_categoria, descripcion, orden, activo) VALUES (:nombre, :descripcion, :orden, :activo)'
                );
                $statement->execute([
                    'nombre' => $name,
                    'descripcion' => $description !== '' ? $description : null,
                    'orden' => $order,
                    'activo' => $active,
                ]);
                flashAndRedirect('success', 'La categoría se creó correctamente.', 'categories');
            }

            if ($action === 'delete_category') {
                if ($recordId < 1) {
                    flashAndRedirect('error', 'La categoría indicada no es válida.', 'categories');
                }
                $count = $pdo->prepare('SELECT COUNT(*) FROM PRODUCTO WHERE id_categoria = :id');
                $count->execute(['id' => $recordId]);
                if ((int) $count->fetchColumn() > 0) {
                    flashAndRedirect('error', 'No se puede eliminar esta categoría porque tiene productos asociados. Reasigna o elimina esos productos primero.', 'categories');
                }
                $statement = $pdo->prepare('DELETE FROM CATEGORIA WHERE id_categoria = :id');
                $statement->execute(['id' => $recordId]);
                flashAndRedirect(
                    $statement->rowCount() > 0 ? 'success' : 'error',
                    $statement->rowCount() > 0 ? 'La categoría se eliminó correctamente.' : 'La categoría ya no existe.',
                    'categories'
                );
            }

            if ($action === 'save_product') {
                $name = inputString($_POST, 'nombre');
                $description = inputString($_POST, 'descripcion');
                $categoryText = inputString($_POST, 'id_categoria');
                $categoryId = filter_var($categoryText, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $priceText = inputString($_POST, 'precio');
                $costText = inputString($_POST, 'costo');
                $active = isset($_POST['activo']) ? 1 : 0;
                $old = [
                    'nombre' => $name,
                    'descripcion' => $description,
                    'id_categoria' => $categoryText,
                    'precio' => $priceText,
                    'costo' => $costText,
                    'activo' => $active,
                ];

                if ($name === '' || textLength($name) > 255) {
                    flashAndRedirect('error', 'Escribe un nombre de producto de hasta 255 caracteres.', 'products', 'product', $recordId, $old);
                }
                if ($categoryId === false) {
                    flashAndRedirect('error', 'Selecciona una categoría válida para el producto.', 'products', 'product', $recordId, $old);
                }
                if (!validMoney($priceText)) {
                    flashAndRedirect('error', 'El precio debe ser positivo o cero, con hasta dos decimales y un máximo de 99,999,999.99.', 'products', 'product', $recordId, $old);
                }
                if ($costText !== '' && !validMoney($costText)) {
                    flashAndRedirect('error', 'El costo debe estar vacío o tener un valor de hasta dos decimales y un máximo de 99,999,999.99.', 'products', 'product', $recordId, $old);
                }

                $categoryCheck = $pdo->prepare('SELECT 1 FROM CATEGORIA WHERE id_categoria = :id');
                $categoryCheck->execute(['id' => (int) $categoryId]);
                if (!$categoryCheck->fetchColumn()) {
                    flashAndRedirect('error', 'La categoría seleccionada no existe. Actualiza la lista e inténtalo de nuevo.', 'products', 'product', $recordId, $old);
                }

                $values = [
                    'nombre' => $name,
                    'id_categoria' => (int) $categoryId,
                    'descripcion' => $description !== '' ? $description : null,
                    'precio' => normalizedMoney($priceText),
                    'costo' => $costText !== '' ? normalizedMoney($costText) : null,
                    'activo' => $active,
                ];

                if ($recordId > 0) {
                    $check = $pdo->prepare('SELECT 1 FROM PRODUCTO WHERE id_producto = :id');
                    $check->execute(['id' => $recordId]);
                    if (!$check->fetchColumn()) {
                        flashAndRedirect('error', 'El producto que intentas editar ya no existe.', 'products');
                    }
                    $statement = $pdo->prepare(
                        'UPDATE PRODUCTO SET id_categoria = :id_categoria, nombre = :nombre, descripcion = :descripcion, precio = :precio, costo = :costo, activo = :activo WHERE id_producto = :id'
                    );
                    $values['id'] = $recordId;
                    $statement->execute($values);
                    flashAndRedirect('success', 'El producto se actualizó correctamente.', 'products');
                }

                $statement = $pdo->prepare(
                    'INSERT INTO PRODUCTO (id_categoria, nombre, descripcion, precio, costo, activo) VALUES (:id_categoria, :nombre, :descripcion, :precio, :costo, :activo)'
                );
                $statement->execute($values);
                flashAndRedirect('success', 'El producto se creó correctamente.', 'products');
            }

            if ($action === 'delete_product') {
                if ($recordId < 1) {
                    flashAndRedirect('error', 'El producto indicado no es válido.', 'products');
                }
                $statement = $pdo->prepare('DELETE FROM PRODUCTO WHERE id_producto = :id');
                $statement->execute(['id' => $recordId]);
                flashAndRedirect(
                    $statement->rowCount() > 0 ? 'success' : 'error',
                    $statement->rowCount() > 0 ? 'El producto se eliminó correctamente.' : 'El producto ya no existe.',
                    'products'
                );
            }

            flashAndRedirect('error', 'La acción solicitada no es válida.', 'products');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $message = $action === 'delete_product'
                    ? 'No se puede eliminar este producto porque está relacionado con recetas o pedidos existentes.'
                    : 'No se pudo completar la operación porque el registro está relacionado con otros datos.';
                flashAndRedirect('error', $message, $actionTab);
            }
            error_log('[Mesa360] Menu operation failed (' . $action . '): ' . $exception->getMessage());
            flashAndRedirect('error', 'No se pudo completar la operación. Verifica los datos e inténtalo de nuevo.', $actionTab);
        } catch (Throwable $exception) {
            error_log('[Mesa360] Menu operation error (' . $action . '): ' . $exception->getMessage());
            flashAndRedirect('error', 'Ocurrió un error al procesar la solicitud. Inténtalo nuevamente.', $actionTab);
        }
    }

    $flash = $_SESSION['mesa360_flash'] ?? null;
    unset($_SESSION['mesa360_flash']);
    $oldInputRecord = $_SESSION['mesa360_old_input'] ?? null;
    unset($_SESSION['mesa360_old_input']);
}


if ($page === 'movimientos' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo instanceof PDO) moduleFlashRedirect('movimientos', 'error', 'No se puede conectar con la base de datos en este momento.');
    if (inputString($_POST, 'action') !== 'record_movement') moduleFlashRedirect('movimientos', 'error', 'La acción solicitada no es válida.');
    if (!hash_equals($csrfToken, inputString($_POST, 'csrf_token'))) moduleFlashRedirect('movimientos', 'error', 'La sesión del formulario venció. Vuelve a intentarlo.');

    $idInsumo = filter_var(inputString($_POST, 'id_insumo'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $tipo = inputString($_POST, 'tipo_movimiento');
    $cantidadText = inputString($_POST, 'cantidad');
    $motivo = inputString($_POST, 'motivo');
    if ($idInsumo === false || !in_array($tipo, ['Entrada', 'Salida'], true) || !validMoney($cantidadText) || (float) $cantidadText <= 0 || $motivo === '' || textLength($motivo) > 255) {
        moduleFlashRedirect('movimientos', 'error', 'Completa el insumo, el tipo, una cantidad positiva y un motivo de hasta 255 caracteres.');
    }
    $cantidad = normalizedMoney($cantidadText);
    try {
        $pdo->beginTransaction();
        $lock = $pdo->prepare('SELECT id_insumo, nombre_insumo, COALESCE(stock_actual, 0) AS stock_actual FROM INSUMO WHERE id_insumo = :id AND COALESCE(activo, 1) = 1 FOR UPDATE');
        $lock->execute(['id' => (int) $idInsumo]);
        $insumo = $lock->fetch();
        if (!$insumo) {
            $pdo->rollBack();
            moduleFlashRedirect('movimientos', 'error', 'El insumo no existe o está inactivo.');
        }
        $stockActual = (float) $insumo['stock_actual'];
        $cantidadFloat = (float) $cantidad;
        if ($tipo === 'Salida' && $cantidadFloat > $stockActual + 0.00001) {
            $pdo->rollBack();
            moduleFlashRedirect('movimientos', 'error', 'No hay existencias suficientes. Stock actual: ' . number_format($stockActual, 2, '.', ','));
        }
        $stockNuevo = round($tipo === 'Entrada' ? $stockActual + $cantidadFloat : $stockActual - $cantidadFloat, 2);
        if ($stockNuevo < 0) {
            $pdo->rollBack();
            moduleFlashRedirect('movimientos', 'error', 'La operación dejaría una existencia negativa.');
        }
        $update = $pdo->prepare('UPDATE INSUMO SET stock_actual = :stock WHERE id_insumo = :id');
        $update->execute(['stock' => number_format($stockNuevo, 2, '.', ''), 'id' => (int) $idInsumo]);
        $insert = $pdo->prepare('INSERT INTO MOVIMIENTOINVENTARIO (id_insumo, tipo_movimiento, cantidad, motivo, fecha_movimiento) VALUES (:insumo, :tipo, :cantidad, :motivo, NOW())');
        $insert->execute(['insumo' => (int) $idInsumo, 'tipo' => $tipo, 'cantidad' => $cantidad, 'motivo' => $motivo]);
        $pdo->commit();
        moduleFlashRedirect('movimientos', 'success', 'Movimiento registrado. Nueva existencia: ' . number_format($stockNuevo, 2, '.', ','));
    } catch (Throwable $exception) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
        error_log('[Mesa360] Inventory movement failed: ' . $exception->getMessage());
        moduleFlashRedirect('movimientos', 'error', 'No se pudo registrar el movimiento. Verifica los datos e inténtalo nuevamente.');
    }
}

if ($page === 'pagos' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo instanceof PDO) paymentFlashRedirect('error', 'No se puede conectar con la base de datos en este momento.');
    $action = inputString($_POST, 'action');
    $tab = in_array($action, ['save_method', 'delete_method'], true) ? 'methods' : 'payments';
    if (!hash_equals($csrfToken, inputString($_POST, 'csrf_token'))) paymentFlashRedirect('error', 'La sesión del formulario venció. Vuelve a intentarlo.', $tab);
    try {
        if ($action === 'save_method') {
            $id = filter_var(inputString($_POST, 'id_metodo_pago', '0'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            $id = $id === false ? 0 : (int) $id;
            $nombre = inputString($_POST, 'nombre_metodo');
            $descripcion = inputString($_POST, 'descripcion');
            $requiereReferencia = isset($_POST['requiere_referencia']) ? 1 : 0;
            $activo = isset($_POST['activo']) ? 1 : 0;
            if ($nombre === '' || textLength($nombre) > 255 || textLength($descripcion) > 5000) paymentFlashRedirect('error', 'El nombre es obligatorio y la descripción no puede superar 5,000 caracteres.', 'methods');
            $dup = $pdo->prepare('SELECT COUNT(*) FROM METODOPAGO WHERE nombre_metodo = :nombre AND id_metodo_pago <> :id');
            $dup->execute(['nombre' => $nombre, 'id' => $id]);
            if ((int) $dup->fetchColumn() > 0) paymentFlashRedirect('error', 'Ya existe un método de pago con ese nombre.', 'methods');
            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE METODOPAGO SET nombre_metodo = :nombre, descripcion = :descripcion, requiere_referencia = :requiere, activo = :activo WHERE id_metodo_pago = :id');
                $stmt->execute(['nombre' => $nombre, 'descripcion' => $descripcion !== '' ? $descripcion : null, 'requiere' => $requiereReferencia, 'activo' => $activo, 'id' => $id]);
                paymentFlashRedirect('success', 'Método de pago actualizado correctamente.', 'methods');
            }
            $stmt = $pdo->prepare('INSERT INTO METODOPAGO (nombre_metodo, descripcion, requiere_referencia, activo) VALUES (:nombre, :descripcion, :requiere, :activo)');
            $stmt->execute(['nombre' => $nombre, 'descripcion' => $descripcion !== '' ? $descripcion : null, 'requiere' => $requiereReferencia, 'activo' => $activo]);
            paymentFlashRedirect('success', 'Método de pago creado correctamente.', 'methods');
        }
        if ($action === 'delete_method') {
            $id = filter_var(inputString($_POST, 'id_metodo_pago'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) paymentFlashRedirect('error', 'El método indicado no es válido.', 'methods');
            $used = $pdo->prepare('SELECT (SELECT COUNT(*) FROM PAGO WHERE id_metodo_pago = :id1) + (SELECT COUNT(*) FROM PEDIDO WHERE id_metodo_pago = :id2)');
            $used->execute(['id1' => (int) $id, 'id2' => (int) $id]);
            if ((int) $used->fetchColumn() > 0) paymentFlashRedirect('error', 'Este método ya está asociado a operaciones; desactívalo en lugar de eliminarlo.', 'methods');
            $stmt = $pdo->prepare('DELETE FROM METODOPAGO WHERE id_metodo_pago = :id');
            $stmt->execute(['id' => (int) $id]);
            paymentFlashRedirect($stmt->rowCount() > 0 ? 'success' : 'error', $stmt->rowCount() > 0 ? 'Método de pago eliminado.' : 'El método ya no existe.', 'methods');
        }
        if ($action === 'record_payment') {
            $orderId = filter_var(inputString($_POST, 'id_pedido'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $methodId = filter_var(inputString($_POST, 'id_metodo_pago'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $amountText = inputString($_POST, 'monto');
            $receivedText = inputString($_POST, 'monto_recibido');
            $reference = inputString($_POST, 'referencia_transaccion');
            if ($orderId === false || $methodId === false || !validMoney($amountText) || (float) $amountText <= 0 || !validMoney($receivedText) || (float) $receivedText < (float) $amountText || textLength($reference) > 255) paymentFlashRedirect('error', 'Verifica el pedido y los importes. El importe recibido debe cubrir el pago.');
            $amount = normalizedMoney($amountText);
            $received = normalizedMoney($receivedText);
            $methodQuery = $pdo->prepare('SELECT id_metodo_pago, requiere_referencia FROM METODOPAGO WHERE id_metodo_pago = :id AND COALESCE(activo, 1) = 1');
            $methodQuery->execute(['id' => (int) $methodId]);
            $method = $methodQuery->fetch();
            if (!$method) paymentFlashRedirect('error', 'El método de pago seleccionado no existe o está inactivo.');
            if ((int) $method['requiere_referencia'] === 1 && $reference === '') paymentFlashRedirect('error', 'Este método de pago requiere una referencia de transacción.');
            $pdo->beginTransaction();
            $orderQuery = $pdo->prepare('SELECT id_pedido, total, estado_actual FROM PEDIDO WHERE id_pedido = :id FOR UPDATE');
            $orderQuery->execute(['id' => (int) $orderId]);
            $order = $orderQuery->fetch();
            if (!$order || $order['estado_actual'] === 'Cancelado') {
                $pdo->rollBack();
                paymentFlashRedirect('error', 'El pedido no existe o está cancelado.');
            }
            $paidQuery = $pdo->prepare('SELECT COALESCE(SUM(monto), 0) FROM PAGO WHERE id_pedido = :id');
            $paidQuery->execute(['id' => (int) $orderId]);
            $paid = (float) $paidQuery->fetchColumn();
            $outstanding = max(0, round((float) $order['total'] - $paid, 2));
            if ((float) $amount > $outstanding + 0.00001) {
                $pdo->rollBack();
                paymentFlashRedirect('error', 'El pago supera el saldo pendiente del pedido: ' . moneyLabel($outstanding) . '.');
            }
            $change = round(max(0, (float) $received - (float) $amount), 2);
            $insert = $pdo->prepare('INSERT INTO PAGO (id_pedido, id_metodo_pago, monto, monto_recibido, cambio, referencia_transaccion, fecha_pago, id_empleado) VALUES (:pedido, :metodo, :monto, :recibido, :cambio, :referencia, NOW(), NULL)');
            $insert->execute(['pedido' => (int) $orderId, 'metodo' => (int) $methodId, 'monto' => $amount, 'recibido' => $received, 'cambio' => number_format($change, 2, '.', ''), 'referencia' => $reference !== '' ? $reference : null]);
            $pdo->commit();
            paymentFlashRedirect('success', 'Pago registrado correctamente por ' . moneyLabel($amount) . '.');
        }
        paymentFlashRedirect('error', 'La acción solicitada no es válida.', $tab);
    } catch (Throwable $exception) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) $pdo->rollBack();
        error_log('[Mesa360] Payment operation failed (' . $action . '): ' . $exception->getMessage());
        if ($exception instanceof PDOException && $exception->getCode() === '23000') paymentFlashRedirect('error', 'No se pudo completar la operación porque el registro está relacionado con otros datos.', $tab);
        paymentFlashRedirect('error', 'No se pudo completar la operación. Verifica los datos e inténtalo nuevamente.', $tab);
    }
}


if (isset($entityModules[$page]) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo instanceof PDO) {
        moduleFlashRedirect($page, 'error', 'No se puede conectar con la base de datos en este momento. Intenta nuevamente.');
    }
    $module = $entityModules[$page];
    $action = inputString($_POST, 'action');
    $recordIdValue = filter_var(inputString($_POST, 'id', '0'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $recordId = $recordIdValue === false ? 0 : (int) $recordIdValue;
    $postedToken = inputString($_POST, 'csrf_token');
    if (!hash_equals($csrfToken, $postedToken)) {
        moduleFlashRedirect($page, 'error', 'La sesión del formulario venció. Vuelve a intentarlo.');
    }

    try {
        if ($action === 'delete_record') {
            if ($recordId < 1) {
                moduleFlashRedirect($page, 'error', 'El registro indicado no es válido.');
            }
            $delete = $pdo->prepare('DELETE FROM ' . $module['table'] . ' WHERE ' . $module['pk'] . ' = :id');
            $delete->execute(['id' => $recordId]);
            moduleFlashRedirect(
                $page,
                $delete->rowCount() > 0 ? 'success' : 'error',
                $delete->rowCount() > 0 ? 'El registro se eliminó correctamente.' : 'El registro ya no existe.'
            );
        }

        if ($action !== 'save_record') {
            moduleFlashRedirect($page, 'error', 'La acción solicitada no es válida.');
        }

        $values = [];
        $oldInput = [];
        $validationError = null;
        foreach ($module['fields'] as $fieldName => $field) {
            if ($field['type'] === 'checkbox') {
                $value = isset($_POST[$fieldName]) ? 1 : 0;
                $oldInput[$fieldName] = $value;
                $values[$fieldName] = $value;
                continue;
            }
            $raw = $_POST[$fieldName] ?? '';
            $value = is_scalar($raw) ? trim((string) $raw) : '';
            $oldInput[$fieldName] = $value;

            if (!empty($field['required']) && $value === '') {
                $validationError = 'Completa el campo «' . $field['label'] . '».';
                break;
            }
            if ($value === '') {
                $values[$fieldName] = null;
                continue;
            }
            if (isset($field['maxlength']) && textLength($value) > (int) $field['maxlength']) {
                $validationError = 'El campo «' . $field['label'] . '» supera la longitud permitida.';
                break;
            }

            if ($field['type'] === 'email' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                $validationError = 'Introduce una dirección de correo válida.';
                break;
            }
            if ($field['type'] === 'number') {
                if (!empty($field['integer'])) {
                    $number = filter_var($value, FILTER_VALIDATE_INT);
                    if ($number === false || $number < (int) ($field['min'] ?? 0)) {
                        $validationError = 'El campo «' . $field['label'] . '» debe ser un número entero válido.';
                        break;
                    }
                    $values[$fieldName] = (int) $number;
                } else {
                    if (!is_numeric($value) || (float) $value < (float) ($field['min'] ?? 0)) {
                        $validationError = 'Introduce un valor numérico válido para «' . $field['label'] . '».';
                        break;
                    }
                    if ($page === 'inventario' && in_array($fieldName, ['stock_minimo', 'costo_unitario'], true)) {
                        if (!validMoney($value)) {
                            $validationError = 'Introduce un importe con hasta dos decimales para «' . $field['label'] . '».';
                            break;
                        }
                        $value = normalizedMoney($value);
                    }
                    if ($page === 'recetas' && $fieldName === 'cantidad_requerida') {
                        if (!validMoney($value) || (float) $value <= 0) {
                            $validationError = 'La cantidad requerida debe ser mayor que cero y tener hasta dos decimales.';
                            break;
                        }
                        $value = normalizedMoney($value);
                    }
                    $values[$fieldName] = $value;
                }
                continue;
            }
            if ($field['type'] === 'date') {
                $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                if (!$date || $date->format('Y-m-d') !== $value) {
                    $validationError = 'Selecciona una fecha válida para «' . $field['label'] . '».';
                    break;
                }
                $values[$fieldName] = $value;
                continue;
            }
            if ($field['type'] === 'time') {
                if (preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value) !== 1) {
                    $validationError = 'Selecciona una hora válida.';
                    break;
                }
                $values[$fieldName] = $value . ':00';
                continue;
            }
            if ($field['type'] === 'select') {
                if (isset($field['options']) && !array_key_exists($value, $field['options'])) {
                    $validationError = 'Selecciona una opción válida para «' . $field['label'] . '».';
                    break;
                }
                if (isset($field['sourceCheckSql'])) {
                    $exists = $pdo->prepare($field['sourceCheckSql']);
                    $exists->execute(['id' => (int) $value]);
                    if (!$exists->fetchColumn()) {
                        $validationError = 'La selección de «' . $field['label'] . '» ya no está disponible. Actualiza la página.';
                        break;
                    }
                }
                $values[$fieldName] = isset($field['options']) ? $value : (int) $value;
                continue;
            }
            $values[$fieldName] = $value;
        }

        if ($validationError !== null) {
            moduleFlashRedirect($page, 'error', $validationError, $recordId > 0 ? 'edit' : 'new', $recordId, $oldInput);
        }

        /* MESA360_MESERA_TABLE_STATUS_ONLY */
        if ($page === 'mesas' && strcasecmp((string)($_SESSION['mesa360_user_role'] ?? ''), 'Mesera') === 0) {
            if ($recordId < 1) moduleFlashRedirect('mesas', 'error', 'La mesera solo puede actualizar el estado de una mesa existente.');
            $currentTable = $pdo->prepare('SELECT numero_mesa, capacidad, ubicacion, activo FROM MESA WHERE id_mesa = :id');
            $currentTable->execute(['id' => $recordId]);
            $currentTableRow = $currentTable->fetch();
            if (!$currentTableRow) moduleFlashRedirect('mesas', 'error', 'La mesa indicada ya no existe.');
            $values = [
                'numero_mesa' => (int)$currentTableRow['numero_mesa'],
                'capacidad' => (int)$currentTableRow['capacidad'],
                'ubicacion' => $currentTableRow['ubicacion'],
                'estado' => $values['estado'],
                'activo' => (int)$currentTableRow['activo'],
            ];
        }

        if ($page === 'inventario') {
            if ($recordId > 0) {
                unset($values['stock_actual']);
            } else {
                $values['stock_actual'] = '0.00';
            }
        }

        if ($page === 'recetas') {
            $duplicateRecipe = $pdo->prepare('SELECT COUNT(*) FROM RECETA WHERE id_producto = :producto AND id_insumo = :insumo AND id_receta <> :id');
            $duplicateRecipe->execute(['producto' => $values['id_producto'], 'insumo' => $values['id_insumo'], 'id' => $recordId]);
            if ((int) $duplicateRecipe->fetchColumn() > 0) {
                moduleFlashRedirect($page, 'error', 'Ese insumo ya está registrado en la receta del producto. Edita el registro existente.', $recordId > 0 ? 'edit' : 'new', $recordId, $oldInput);
            }
        }

        if ($page === 'mesas') {
            $duplicate = $pdo->prepare('SELECT COUNT(*) FROM MESA WHERE numero_mesa = :numero AND id_mesa <> :id');
            $duplicate->execute(['numero' => $values['numero_mesa'], 'id' => $recordId]);
            if ((int) $duplicate->fetchColumn() > 0) {
                moduleFlashRedirect($page, 'error', 'Ya existe una mesa con ese número. Utiliza otro número.', $recordId > 0 ? 'edit' : 'new', $recordId, $oldInput);
            }
        }

        if ($recordId > 0) {
            $exists = $pdo->prepare('SELECT 1 FROM ' . $module['table'] . ' WHERE ' . $module['pk'] . ' = :id');
            $exists->execute(['id' => $recordId]);
            if (!$exists->fetchColumn()) {
                moduleFlashRedirect($page, 'error', 'El registro que intentas editar ya no existe.');
            }
            $setList = [];
            foreach (array_keys($values) as $column) {
                $setList[] = $column . ' = :' . $column;
            }
            $update = $pdo->prepare('UPDATE ' . $module['table'] . ' SET ' . implode(', ', $setList) . ' WHERE ' . $module['pk'] . ' = :record_id');
            $values['record_id'] = $recordId;
            $update->execute($values);
            moduleFlashRedirect($page, 'success', ucfirst($module['singular']) . ' actualizado correctamente.');
        }

        $columns = array_keys($values);
        $placeholders = array_map(static fn(string $column): string => ':' . $column, $columns);
        if (!empty($module['insertCreatedAt'])) {
            $columns[] = 'fecha_creacion';
            $placeholders[] = 'NOW()';
        }
        $insert = $pdo->prepare('INSERT INTO ' . $module['table'] . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')');
        $insert->execute($values);
        moduleFlashRedirect($page, 'success', ucfirst($module['singular']) . ' creado correctamente.');
    } catch (PDOException $exception) {
        error_log('[Mesa360] Entity operation failed (' . $page . '): ' . $exception->getMessage());
        if ($exception->getCode() === '23000') {
            if ($action === 'delete_record' && $page === 'clientes') {
                $message = 'No se puede eliminar este cliente porque tiene reservas, pedidos u otros registros relacionados.';
            } elseif ($action === 'delete_record' && $page === 'mesas') {
                $message = 'No se puede eliminar esta mesa porque tiene reservas u órdenes relacionadas.';
            } elseif ($action === 'delete_record' && $page === 'empleados') {
                $message = 'No se puede eliminar este empleado porque tiene operaciones históricas relacionadas.';
            } elseif ($page === 'empleados') {
                $message = 'No se pudo guardar el empleado. Comprueba que la cédula y el correo no estén registrados en otro empleado.';
            } else {
                $message = 'La operación no puede completarse porque el registro está relacionado con otros datos.';
            }
            moduleFlashRedirect($page, 'error', $message);
        }
        moduleFlashRedirect($page, 'error', 'No se pudo completar la operación. Verifica los datos e inténtalo de nuevo.', $recordId > 0 ? 'edit' : ($action === 'save_record' ? 'new' : ''), $recordId);
    } catch (Throwable $exception) {
        error_log('[Mesa360] Entity operation error (' . $page . '): ' . $exception->getMessage());
        moduleFlashRedirect($page, 'error', 'Ocurrió un error al procesar la solicitud. Inténtalo nuevamente.');
    }
}

if ($page === 'pedidos' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$pdo instanceof PDO) {
        moduleFlashRedirect('pedidos', 'error', 'No se puede conectar con la base de datos en este momento. Intenta nuevamente.');
    }
    $action = inputString($_POST, 'action');
    $postedToken = inputString($_POST, 'csrf_token');
    $orderIdValue = filter_var(inputString($_POST, 'id', '0'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $orderId = $orderIdValue === false ? 0 : (int) $orderIdValue;
    if (!hash_equals($csrfToken, $postedToken)) {
        moduleFlashRedirect('pedidos', 'error', 'La sesión del formulario venció. Vuelve a intentarlo.');
    }

    try {
        if ($action === 'delete_order') {
            if ($orderId < 1) {
                moduleFlashRedirect('pedidos', 'error', 'El pedido indicado no es válido.');
            }
            $paidCheck = $pdo->prepare('SELECT COUNT(*) FROM PAGO WHERE id_pedido = :id');
            $paidCheck->execute(['id' => $orderId]);
            if ((int) $paidCheck->fetchColumn() > 0) {
                moduleFlashRedirect('pedidos', 'error', 'No se puede eliminar este pedido porque tiene pagos registrados.');
            }
            $pdo->beginTransaction();
            $deleteDetails = $pdo->prepare('DELETE FROM DETALLEPEDIDO WHERE id_pedido = :id');
            $deleteDetails->execute(['id' => $orderId]);
            $deleteHistory = $pdo->prepare('DELETE FROM ESTADOPEDIDO WHERE id_pedido = :id');
            $deleteHistory->execute(['id' => $orderId]);
            $deleteOrder = $pdo->prepare('DELETE FROM PEDIDO WHERE id_pedido = :id');
            $deleteOrder->execute(['id' => $orderId]);
            if ($deleteOrder->rowCount() < 1) {
                $pdo->rollBack();
                moduleFlashRedirect('pedidos', 'error', 'El pedido ya no existe.');
            }
            $pdo->commit();
            moduleFlashRedirect('pedidos', 'success', 'El pedido y sus detalles se eliminaron correctamente.');
        }

        if ($action !== 'save_order') {
            moduleFlashRedirect('pedidos', 'error', 'La acción solicitada no es válida.');
        }

        $type = inputString($_POST, 'tipo_pedido');
        $status = inputString($_POST, 'estado_actual', 'Pendiente');
        $customerText = inputString($_POST, 'id_cliente');
        $tableText = inputString($_POST, 'id_mesa');
        $waiterText = inputString($_POST, 'id_empleado_mesero');
        $notes = inputString($_POST, 'observaciones');
        $allowedTypes = ['Local', 'Para llevar'];
        $allowedStatuses = ['Pendiente', 'Preparando', 'Listo', 'Servido', 'Cancelado'];
        $old = [
            'tipo_pedido' => $type, 'estado_actual' => $status,
            'id_cliente' => $customerText, 'id_mesa' => $tableText,
            'id_empleado_mesero' => $waiterText, 'observaciones' => $notes,
        ];
        if (!in_array($type, $allowedTypes, true) || !in_array($status, $allowedStatuses, true)) {
            moduleFlashRedirect('pedidos', 'error', 'Selecciona un tipo y estado de pedido válidos.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
        }
        if (textLength($notes) > 5000) {
            moduleFlashRedirect('pedidos', 'error', 'Las observaciones no pueden superar los 5,000 caracteres.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
        }

        $customerId = null;
        if ($customerText !== '') {
            $valid = filter_var($customerText, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($valid === false) {
                moduleFlashRedirect('pedidos', 'error', 'Selecciona un cliente válido.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
            }
            $check = $pdo->prepare('SELECT 1 FROM CLIENTE WHERE id_cliente = :id');
            $check->execute(['id' => (int) $valid]);
            if (!$check->fetchColumn()) {
                moduleFlashRedirect('pedidos', 'error', 'El cliente seleccionado ya no existe.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
            }
            $customerId = (int) $valid;
        }

        $tableId = null;
        if ($type === 'Local') {
            $valid = filter_var($tableText, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($valid === false) {
                moduleFlashRedirect('pedidos', 'error', 'Selecciona una mesa para los pedidos en el restaurante.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
            }
            $check = $pdo->prepare('SELECT 1 FROM MESA WHERE id_mesa = :id AND COALESCE(activo, 1) = 1');
            $check->execute(['id' => (int) $valid]);
            if (!$check->fetchColumn()) {
                moduleFlashRedirect('pedidos', 'error', 'La mesa seleccionada no está disponible para el sistema.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
            }
            $tableId = (int) $valid;
        }

        $waiterId = null;
        if ($waiterText !== '') {
            $valid = filter_var($waiterText, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($valid === false) {
                moduleFlashRedirect('pedidos', 'error', 'Selecciona un empleado válido.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
            }
            $check = $pdo->prepare('SELECT 1 FROM EMPLEADO WHERE id_empleado = :id AND COALESCE(activo, 1) = 1');
            $check->execute(['id' => (int) $valid]);
            if (!$check->fetchColumn()) {
                moduleFlashRedirect('pedidos', 'error', 'El empleado seleccionado no está activo o ya no existe.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
            }
            $waiterId = (int) $valid;
        }

        $productIds = $_POST['producto_id'] ?? [];
        $quantities = $_POST['cantidad'] ?? [];
        $lineNotes = $_POST['notas_linea'] ?? [];
        if (!is_array($productIds) || !is_array($quantities) || !is_array($lineNotes)) {
            moduleFlashRedirect('pedidos', 'error', 'La lista de productos tiene un formato no válido.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
        }
        $items = [];
        $subtotal = 0.0;
        $productLookup = $pdo->prepare('SELECT id_producto, nombre, precio FROM PRODUCTO WHERE id_producto = :id AND COALESCE(activo, 1) = 1');
        $lineCount = max(count($productIds), count($quantities), count($lineNotes));
        for ($i = 0; $i < $lineCount; $i++) {
            $productText = is_scalar($productIds[$i] ?? null) ? trim((string) $productIds[$i]) : '';
            $quantityText = is_scalar($quantities[$i] ?? null) ? trim((string) $quantities[$i]) : '';
            $lineNote = is_scalar($lineNotes[$i] ?? null) ? trim((string) $lineNotes[$i]) : '';
            if ($productText === '' && $quantityText === '' && $lineNote === '') {
                continue;
            }
            $productId = filter_var($productText, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $quantity = filter_var($quantityText, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000]]);
            if ($productId === false || $quantity === false || textLength($lineNote) > 1000) {
                moduleFlashRedirect('pedidos', 'error', 'Cada línea debe incluir un producto válido y una cantidad entera mayor que cero.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
            }
            $productLookup->execute(['id' => (int) $productId]);
            $product = $productLookup->fetch();
            if (!$product) {
                moduleFlashRedirect('pedidos', 'error', 'Uno de los productos seleccionados está inactivo o ya no existe.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
            }
            $price = (float) $product['precio'];
            if ($price < 0) {
                moduleFlashRedirect('pedidos', 'error', 'El producto tiene un precio no válido.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
            }
            $lineSubtotal = round($price * (int) $quantity, 2);
            $subtotal += $lineSubtotal;
            $items[] = [
                'id_producto' => (int) $productId,
                'cantidad' => (int) $quantity,
                'precio_unitario' => number_format($price, 2, '.', ''),
                'subtotal' => number_format($lineSubtotal, 2, '.', ''),
                'notas_preparacion' => $lineNote !== '' ? $lineNote : null,
            ];
        }
        if ($items === []) {
            moduleFlashRedirect('pedidos', 'error', 'Agrega al menos un producto activo al pedido.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
        }
        $subtotal = round($subtotal, 2);
        if ($subtotal > 99999999.99) {
            moduleFlashRedirect('pedidos', 'error', 'El total del pedido supera el monto permitido.', $orderId > 0 ? 'edit' : 'new', $orderId, $old);
        }

        if ($orderId > 0) {
            $paidCheck = $pdo->prepare('SELECT COUNT(*) FROM PAGO WHERE id_pedido = :id');
            $paidCheck->execute(['id' => $orderId]);
            if ((int) $paidCheck->fetchColumn() > 0) {
                moduleFlashRedirect('pedidos', 'error', 'Este pedido tiene pagos registrados y no puede modificarse desde esta pantalla.', 'edit', $orderId);
            }
            $currentStatusQuery = $pdo->prepare('SELECT estado_actual FROM PEDIDO WHERE id_pedido = :id');
            $currentStatusQuery->execute(['id' => $orderId]);
            $currentStatus = $currentStatusQuery->fetchColumn();
            if ($currentStatus === false) {
                moduleFlashRedirect('pedidos', 'error', 'El pedido que intentas editar ya no existe.');
            }
        }

        $pdo->beginTransaction();
        if ($orderId > 0) {
            $update = $pdo->prepare('UPDATE PEDIDO SET id_mesa = :id_mesa, id_cliente = :id_cliente, id_empleado_mesero = :id_empleado_mesero, fecha_pedido = fecha_pedido, tipo_pedido = :tipo_pedido, estado_actual = :estado_actual, subtotal = :subtotal, descuento = 0.00, impuesto = 0.00, total = :total, observaciones = :observaciones WHERE id_pedido = :id');
            $update->execute([
                'id_mesa' => $tableId, 'id_cliente' => $customerId,
                'id_empleado_mesero' => $waiterId, 'tipo_pedido' => $type,
                'estado_actual' => $status, 'subtotal' => number_format($subtotal, 2, '.', ''),
                'total' => number_format($subtotal, 2, '.', ''),
                'observaciones' => $notes !== '' ? $notes : null, 'id' => $orderId,
            ]);
            $deleteItems = $pdo->prepare('DELETE FROM DETALLEPEDIDO WHERE id_pedido = :id');
            $deleteItems->execute(['id' => $orderId]);
            if ($status !== $currentStatus) {
                $history = $pdo->prepare('INSERT INTO ESTADOPEDIDO (id_pedido, estado, fecha_cambio, id_empleado) VALUES (:id, :estado, NOW(), :empleado)');
                $history->execute(['id' => $orderId, 'estado' => $status, 'empleado' => $waiterId]);
            }
            $savedOrderId = $orderId;
        } else {
            $insert = $pdo->prepare('INSERT INTO PEDIDO (id_mesa, id_cliente, id_empleado_mesero, id_empleado_cajero, id_metodo_pago, fecha_pedido, tipo_pedido, estado_actual, subtotal, descuento, impuesto, total, observaciones) VALUES (:id_mesa, :id_cliente, :id_empleado_mesero, NULL, NULL, NOW(), :tipo_pedido, :estado_actual, :subtotal, 0.00, 0.00, :total, :observaciones)');
            $insert->execute([
                'id_mesa' => $tableId, 'id_cliente' => $customerId,
                'id_empleado_mesero' => $waiterId, 'tipo_pedido' => $type,
                'estado_actual' => $status, 'subtotal' => number_format($subtotal, 2, '.', ''),
                'total' => number_format($subtotal, 2, '.', ''),
                'observaciones' => $notes !== '' ? $notes : null,
            ]);
            $savedOrderId = (int) $pdo->lastInsertId();
            $history = $pdo->prepare('INSERT INTO ESTADOPEDIDO (id_pedido, estado, fecha_cambio, id_empleado) VALUES (:id, :estado, NOW(), :empleado)');
            $history->execute(['id' => $savedOrderId, 'estado' => $status, 'empleado' => $waiterId]);
        }

        $insertDetail = $pdo->prepare('INSERT INTO DETALLEPEDIDO (id_pedido, id_producto, cantidad, precio_unitario, subtotal, notas_preparacion) VALUES (:id_pedido, :id_producto, :cantidad, :precio_unitario, :subtotal, :notas_preparacion)');
        foreach ($items as $item) {
            $insertDetail->execute([
                'id_pedido' => $savedOrderId, 'id_producto' => $item['id_producto'],
                'cantidad' => $item['cantidad'], 'precio_unitario' => $item['precio_unitario'],
                'subtotal' => $item['subtotal'], 'notas_preparacion' => $item['notas_preparacion'],
            ]);
        }
        $pdo->commit();
        moduleFlashRedirect('pedidos', 'success', $orderId > 0 ? 'El pedido se actualizó correctamente.' : 'El pedido y sus productos se registraron correctamente.');
    } catch (Throwable $exception) {
        if ($pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('[Mesa360] Order operation failed (' . $action . '): ' . $exception->getMessage());
        if ($exception instanceof PDOException && $exception->getCode() === '23000') {
            moduleFlashRedirect('pedidos', 'error', 'No se puede completar la operación porque el pedido está relacionado con otros registros.');
        }
        moduleFlashRedirect('pedidos', 'error', 'No se pudo completar el pedido. Revisa los datos e inténtalo nuevamente.', $orderId > 0 ? 'edit' : ($action === 'save_order' ? 'new' : ''), $orderId);
    }
}

$menuTab = isset($_GET['tab']) && is_string($_GET['tab']) && in_array($_GET['tab'], ['products', 'categories'], true)
    ? $_GET['tab']
    : 'products';
$menuSearch = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$categories = [];
$products = [];
$productCount = 0;
$categoryCount = 0;
$categoryFormOpen = $page === 'menu' && (isset($_GET['new_category']) || (isset($_GET['edit_category']) && (int) $_GET['edit_category'] > 0));
$productFormOpen = $page === 'menu' && (isset($_GET['new_product']) || (isset($_GET['edit_product']) && (int) $_GET['edit_product'] > 0));
$editingCategory = null;
$editingProduct = null;
$menuNotice = null;
$categoryValues = ['nombre_categoria' => '', 'descripcion' => '', 'orden' => '0', 'activo' => 1];
$productValues = ['nombre' => '', 'descripcion' => '', 'id_categoria' => '', 'precio' => '', 'costo' => '', 'activo' => 1];

if ($page === 'menu' && $pdo instanceof PDO) {
    try {
        $categoryStatement = $pdo->query(
            'SELECT c.id_categoria, c.nombre_categoria, c.descripcion, c.orden, c.activo,
                    (SELECT COUNT(*) FROM PRODUCTO p WHERE p.id_categoria = c.id_categoria) AS productos_count
             FROM CATEGORIA c
             ORDER BY COALESCE(c.orden, 0), c.nombre_categoria'
        );
        $categories = $categoryStatement->fetchAll();
        $categoryCount = count($categories);

        if ($menuSearch !== '' && $menuTab === 'products') {
            $productsStatement = $pdo->prepare(
                'SELECT p.id_producto, p.id_categoria, p.nombre, p.descripcion, p.precio, p.costo, p.activo, c.nombre_categoria
                 FROM PRODUCTO p
                 LEFT JOIN CATEGORIA c ON c.id_categoria = p.id_categoria
                 WHERE p.nombre LIKE :query_name OR c.nombre_categoria LIKE :query_category OR p.descripcion LIKE :query_description
                 ORDER BY p.id_producto DESC'
            );
            $like = '%' . $menuSearch . '%';
            $productsStatement->execute(['query_name' => $like, 'query_category' => $like, 'query_description' => $like]);
            $products = $productsStatement->fetchAll();
        } else {
            $products = $pdo->query(
                'SELECT p.id_producto, p.id_categoria, p.nombre, p.descripcion, p.precio, p.costo, p.activo, c.nombre_categoria
                 FROM PRODUCTO p
                 LEFT JOIN CATEGORIA c ON c.id_categoria = p.id_categoria
                 ORDER BY p.id_producto DESC'
            )->fetchAll();
        }
        $productCount = count($products);

        $editCategoryId = isset($_GET['edit_category']) ? (int) $_GET['edit_category'] : 0;
        if ($editCategoryId > 0 && $menuTab === 'categories') {
            $statement = $pdo->prepare('SELECT id_categoria, nombre_categoria, descripcion, orden, activo FROM CATEGORIA WHERE id_categoria = :id');
            $statement->execute(['id' => $editCategoryId]);
            $editingCategory = $statement->fetch() ?: null;
            if ($editingCategory === null) {
                $categoryFormOpen = false;
                $menuNotice = 'La categoría solicitada no existe.';
            } else {
                $categoryValues = $editingCategory;
            }
        }

        $editProductId = isset($_GET['edit_product']) ? (int) $_GET['edit_product'] : 0;
        if ($editProductId > 0 && $menuTab === 'products') {
            $statement = $pdo->prepare('SELECT id_producto, id_categoria, nombre, descripcion, precio, costo, activo FROM PRODUCTO WHERE id_producto = :id');
            $statement->execute(['id' => $editProductId]);
            $editingProduct = $statement->fetch() ?: null;
            if ($editingProduct === null) {
                $productFormOpen = false;
                $menuNotice = 'El producto solicitado no existe.';
            } else {
                $productValues = $editingProduct;
            }
        }
    } catch (Throwable $exception) {
        error_log('[Mesa360] Could not load menu records: ' . $exception->getMessage());
        $dbError = 'No se pudo cargar la información del menú en este momento. Intenta nuevamente más tarde.';
        $categories = [];
        $products = [];
    }
}

if ($page === 'menu' && is_array($oldInputRecord) && isset($oldInputRecord['type'], $oldInputRecord['values']) && is_array($oldInputRecord['values'])) {
    if ($oldInputRecord['type'] === 'category' && $menuTab === 'categories' && $categoryFormOpen) {
        $categoryValues = array_merge($categoryValues, $oldInputRecord['values']);
    } elseif ($oldInputRecord['type'] === 'product' && $menuTab === 'products' && $productFormOpen) {
        $productValues = array_merge($productValues, $oldInputRecord['values']);
    }
}


$entityRows = [];
$entityOptions = [];
$entitySearch = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$entityFormOpen = isset($_GET['new']) || (isset($_GET['edit']) && (int) $_GET['edit'] > 0);
$entityEditing = null;
$entityNotice = null;
$entityValues = isset($entityModules[$page]) ? $entityModules[$page]['defaults'] : [];

if (isset($entityModules[$page]) && $pdo instanceof PDO) {
    $module = $entityModules[$page];
    try {
        $sql = $module['listSql'];
        $params = [];
        if ($entitySearch !== '' && !empty($module['searchColumns'])) {
            $conditions = [];
            foreach ($module['searchColumns'] as $index => $columnSql) {
                $placeholder = 'search_' . $index;
                $conditions[] = $columnSql . ' LIKE :' . $placeholder;
                $params[$placeholder] = '%' . $entitySearch . '%';
            }
            $sql .= ' AND (' . implode(' OR ', $conditions) . ')';
        }
        $sql .= ' ORDER BY ' . $module['orderBy'];
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        $entityRows = $statement->fetchAll();

        $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        if ($editId > 0) {
            $statement = $pdo->prepare('SELECT * FROM ' . $module['table'] . ' WHERE ' . $module['pk'] . ' = :id');
            $statement->execute(['id' => $editId]);
            $entityEditing = $statement->fetch() ?: null;
            if ($entityEditing === null) {
                $entityFormOpen = false;
                $entityNotice = 'El registro solicitado ya no existe.';
            } else {
                $entityValues = array_merge($entityValues, $entityEditing);
            }
        }

        if ($entityFormOpen) {
            foreach ($module['fields'] as $fieldName => $field) {
                if (isset($field['source'])) {
                    if ($field['source'] === 'clientes') {
                        $optionSql = 'SELECT id_cliente AS option_value, nombre AS option_label FROM CLIENTE ORDER BY nombre';
                    } elseif ($field['source'] === 'mesas') {
                        $optionSql = 'SELECT id_mesa AS option_value, CONCAT("Mesa ", numero_mesa, " · ", capacidad, " personas") AS option_label FROM MESA WHERE COALESCE(activo, 1) = 1 ORDER BY numero_mesa';
                    } elseif ($field['source'] === 'productos') {
                        $optionSql = 'SELECT id_producto AS option_value, nombre AS option_label FROM PRODUCTO WHERE COALESCE(activo, 1) = 1 ORDER BY nombre';
                    } elseif ($field['source'] === 'insumos') {
                        $optionSql = 'SELECT id_insumo AS option_value, nombre_insumo AS option_label FROM INSUMO WHERE COALESCE(activo, 1) = 1 ORDER BY nombre_insumo';
                    } else {
                        throw new RuntimeException('Unknown option source.');
                    }
                    $entityOptions[$fieldName] = $pdo->query($optionSql)->fetchAll();
                }
            }
        }
    } catch (Throwable $exception) {
        error_log('[Mesa360] Could not load entity records (' . $page . '): ' . $exception->getMessage());
        $dbError = 'No se pudo cargar esta información en este momento. Intenta nuevamente más tarde.';
        $entityRows = [];
        $entityNotice = null;
    }
}

if (isset($entityModules[$page]) && is_array($oldInputRecord) && ($oldInputRecord['type'] ?? '') === 'module' && is_array($oldInputRecord['values'] ?? null) && $entityFormOpen) {
    $entityValues = array_merge($entityValues, $oldInputRecord['values']);
}

$orderRows = [];
$orderSearch = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';
$orderFormOpen = $page === 'pedidos' && (isset($_GET['new']) || (isset($_GET['edit']) && (int) $_GET['edit'] > 0));
$orderEditing = null;
$orderItemsForm = [['id_producto' => '', 'cantidad' => '1', 'notas_preparacion' => '']];
$orderCustomers = [];
$orderTables = [];
$orderEmployees = [];
$orderProducts = [];
$orderDetailRows = [];
$orderNotice = null;
$orderValues = ['tipo_pedido' => 'Local', 'estado_actual' => 'Pendiente', 'id_cliente' => '', 'id_mesa' => '', 'id_empleado_mesero' => '', 'observaciones' => ''];

if ($page === 'pedidos' && $pdo instanceof PDO) {
    try {
        $orderSql = 'SELECT p.id_pedido, p.fecha_pedido, p.tipo_pedido, p.estado_actual, p.subtotal, p.total, p.id_cliente, p.id_mesa, c.nombre AS cliente_nombre, m.numero_mesa, (SELECT COUNT(*) FROM DETALLEPEDIDO d WHERE d.id_pedido = p.id_pedido) AS lineas FROM PEDIDO p LEFT JOIN CLIENTE c ON c.id_cliente = p.id_cliente LEFT JOIN MESA m ON m.id_mesa = p.id_mesa WHERE 1=1';
        $orderParams = [];
        if ($orderSearch !== '') {
            $orderSql .= ' AND (CAST(p.id_pedido AS CHAR) LIKE :q_id OR COALESCE(c.nombre, "") LIKE :q_cliente OR p.estado_actual LIKE :q_estado OR p.tipo_pedido LIKE :q_tipo)';
            $like = '%' . $orderSearch . '%';
            $orderParams = ['q_id' => $like, 'q_cliente' => $like, 'q_estado' => $like, 'q_tipo' => $like];
        }
        $orderSql .= ' ORDER BY p.fecha_pedido DESC, p.id_pedido DESC LIMIT 200';
        $orderStatement = $pdo->prepare($orderSql);
        $orderStatement->execute($orderParams);
        $orderRows = $orderStatement->fetchAll();

        $orderProducts = $pdo->query('SELECT id_producto, nombre, precio FROM PRODUCTO WHERE COALESCE(activo, 1) = 1 ORDER BY nombre')->fetchAll();
        $orderCustomers = $pdo->query('SELECT id_cliente, nombre FROM CLIENTE ORDER BY nombre')->fetchAll();
        $orderTables = $pdo->query('SELECT id_mesa, numero_mesa, capacidad FROM MESA WHERE COALESCE(activo, 1) = 1 ORDER BY numero_mesa')->fetchAll();
        $orderEmployees = $pdo->query('SELECT id_empleado, nombre, cargo FROM EMPLEADO WHERE COALESCE(activo, 1) = 1 ORDER BY nombre')->fetchAll();

        $orderEditId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
        if ($orderEditId > 0 && $orderFormOpen) {
            $orderStatement = $pdo->prepare('SELECT * FROM PEDIDO WHERE id_pedido = :id');
            $orderStatement->execute(['id' => $orderEditId]);
            $orderEditing = $orderStatement->fetch() ?: null;
            if ($orderEditing === null) {
                $orderFormOpen = false;
                $orderNotice = 'El pedido solicitado ya no existe.';
            } else {
                $orderValues = array_merge($orderValues, $orderEditing);
                $detailStatement = $pdo->prepare('SELECT id_producto, cantidad, notas_preparacion FROM DETALLEPEDIDO WHERE id_pedido = :id ORDER BY id_detalle');
                $detailStatement->execute(['id' => $orderEditId]);
                $orderItemsForm = $detailStatement->fetchAll() ?: $orderItemsForm;
            }
        }
    } catch (Throwable $exception) {
        error_log('[Mesa360] Could not load orders: ' . $exception->getMessage());
        $dbError = 'No se pudo cargar la información de pedidos. Intenta nuevamente más tarde.';
        $orderRows = [];
        $orderProducts = $orderCustomers = $orderTables = $orderEmployees = [];
    }
}

if ($page === 'pedidos' && is_array($oldInputRecord) && ($oldInputRecord['type'] ?? '') === 'module' && is_array($oldInputRecord['values'] ?? null) && $orderFormOpen) {
    $orderValues = array_merge($orderValues, $oldInputRecord['values']);
}

$reportFromDefault = date('Y-m-d', strtotime('-29 days'));
$reportToDefault = date('Y-m-d');
$reportFrom = isset($_GET['from']) && is_string($_GET['from']) ? $_GET['from'] : $reportFromDefault;
$reportTo = isset($_GET['to']) && is_string($_GET['to']) ? $_GET['to'] : $reportToDefault;
$validDate = static function (string $value): bool {
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
};
if (!$validDate($reportFrom) || !$validDate($reportTo) || $reportFrom > $reportTo) {
    $reportFrom = $reportFromDefault;
    $reportTo = $reportToDefault;
}
$reportSummary = ['orders' => 0, 'cancelled' => 0, 'order_value' => 0, 'average' => 0, 'items' => 0];
$reportStatuses = [];
$reportProducts = [];
$reportDaily = [];
if ($page === 'reportes' && $pdo instanceof PDO) {
    try {
        $summary = $pdo->prepare('SELECT COUNT(*) AS orders_count, SUM(CASE WHEN estado_actual = "Cancelado" THEN 1 ELSE 0 END) AS cancelled_count, SUM(CASE WHEN estado_actual <> "Cancelado" THEN COALESCE(total, 0) ELSE 0 END) AS order_value, AVG(CASE WHEN estado_actual <> "Cancelado" THEN COALESCE(total, 0) ELSE NULL END) AS average_value FROM PEDIDO WHERE DATE(fecha_pedido) BETWEEN :date_from AND :date_to');
        $summary->execute(['date_from' => $reportFrom, 'date_to' => $reportTo]);
        $data = $summary->fetch() ?: [];
        $reportSummary = [
            'orders' => (int) ($data['orders_count'] ?? 0),
            'cancelled' => (int) ($data['cancelled_count'] ?? 0),
            'order_value' => (float) ($data['order_value'] ?? 0),
            'average' => (float) ($data['average_value'] ?? 0),
            'items' => 0,
        ];
        $statuses = $pdo->prepare('SELECT estado_actual, COUNT(*) AS cantidad, SUM(COALESCE(total, 0)) AS importe FROM PEDIDO WHERE DATE(fecha_pedido) BETWEEN :date_from AND :date_to GROUP BY estado_actual ORDER BY cantidad DESC');
        $statuses->execute(['date_from' => $reportFrom, 'date_to' => $reportTo]);
        $reportStatuses = $statuses->fetchAll();
        $top = $pdo->prepare('SELECT pr.nombre, SUM(d.cantidad) AS unidades, SUM(d.subtotal) AS importe FROM DETALLEPEDIDO d JOIN PEDIDO p ON p.id_pedido = d.id_pedido JOIN PRODUCTO pr ON pr.id_producto = d.id_producto WHERE DATE(p.fecha_pedido) BETWEEN :date_from AND :date_to AND p.estado_actual <> "Cancelado" GROUP BY pr.id_producto, pr.nombre ORDER BY unidades DESC, importe DESC LIMIT 10');
        $top->execute(['date_from' => $reportFrom, 'date_to' => $reportTo]);
        $reportProducts = $top->fetchAll();
        $daily = $pdo->prepare('SELECT DATE(fecha_pedido) AS dia, COUNT(*) AS pedidos, SUM(CASE WHEN estado_actual <> "Cancelado" THEN COALESCE(total, 0) ELSE 0 END) AS importe FROM PEDIDO WHERE DATE(fecha_pedido) BETWEEN :date_from AND :date_to GROUP BY DATE(fecha_pedido) ORDER BY dia DESC LIMIT 14');
        $daily->execute(['date_from' => $reportFrom, 'date_to' => $reportTo]);
        $reportDaily = $daily->fetchAll();
    } catch (Throwable $exception) {
        error_log('[Mesa360] Could not load reports: ' . $exception->getMessage());
        $dbError = 'No se pudieron generar los reportes en este momento. Intenta nuevamente más tarde.';
    }
}


$movementRows = [];
$movementInsums = [];
$paymentTab = isset($_GET['tab']) && is_string($_GET['tab']) && in_array($_GET['tab'], ['payments', 'methods'], true) ? $_GET['tab'] : 'payments';
$paymentRows = [];
$paymentMethods = [];
$paymentOrders = [];
$paymentMethodOpen = $page === 'pagos' && $paymentTab === 'methods' && (isset($_GET['new_method']) || (isset($_GET['edit_method']) && (int) $_GET['edit_method'] > 0));
$paymentMethodEditing = null;
$paymentMethodValues = ['nombre_metodo' => '', 'descripcion' => '', 'requiere_referencia' => 0, 'activo' => 1];

if ($page === 'movimientos' && $pdo instanceof PDO) {
    try {
        $movementInsums = $pdo->query('SELECT id_insumo, nombre_insumo, COALESCE(stock_actual, 0) AS stock_actual, unidad_medida FROM INSUMO WHERE COALESCE(activo, 1) = 1 ORDER BY nombre_insumo')->fetchAll();
        $movementRows = $pdo->query('SELECT mv.id_movimiento, mv.tipo_movimiento, mv.cantidad, mv.motivo, mv.fecha_movimiento, i.nombre_insumo, i.unidad_medida FROM MOVIMIENTOINVENTARIO mv JOIN INSUMO i ON i.id_insumo = mv.id_insumo ORDER BY mv.fecha_movimiento DESC, mv.id_movimiento DESC LIMIT 100')->fetchAll();
    } catch (Throwable $exception) {
        error_log('[Mesa360] Could not load inventory movements: ' . $exception->getMessage());
        $dbError = 'No se pudo cargar el historial de inventario en este momento. Intenta nuevamente más tarde.';
    }
}

if ($page === 'pagos' && $pdo instanceof PDO) {
    try {
        $paymentMethods = $pdo->query('SELECT id_metodo_pago, nombre_metodo, descripcion, requiere_referencia, activo FROM METODOPAGO ORDER BY nombre_metodo')->fetchAll();
        $paymentOrders = $pdo->query('SELECT p.id_pedido, p.total, p.estado_actual, COALESCE(SUM(pg.monto), 0) AS pagado FROM PEDIDO p LEFT JOIN PAGO pg ON pg.id_pedido = p.id_pedido WHERE p.estado_actual <> "Cancelado" GROUP BY p.id_pedido, p.total, p.estado_actual HAVING (p.total - COALESCE(SUM(pg.monto), 0)) > 0.009 ORDER BY p.id_pedido DESC')->fetchAll();
        $paymentRows = $pdo->query('SELECT pg.id_pago, pg.id_pedido, pg.monto, pg.monto_recibido, pg.cambio, pg.referencia_transaccion, pg.fecha_pago, mp.nombre_metodo FROM PAGO pg JOIN METODOPAGO mp ON mp.id_metodo_pago = pg.id_metodo_pago ORDER BY pg.fecha_pago DESC, pg.id_pago DESC LIMIT 200')->fetchAll();
        $editMethodId = isset($_GET['edit_method']) ? (int) $_GET['edit_method'] : 0;
        if ($paymentTab === 'methods' && $editMethodId > 0) {
            $stmt = $pdo->prepare('SELECT id_metodo_pago, nombre_metodo, descripcion, requiere_referencia, activo FROM METODOPAGO WHERE id_metodo_pago = :id');
            $stmt->execute(['id' => $editMethodId]);
            $paymentMethodEditing = $stmt->fetch() ?: null;
            if ($paymentMethodEditing) {
                $paymentMethodValues = array_merge($paymentMethodValues, $paymentMethodEditing);
            } else {
                $paymentMethodOpen = false;
                $flash = ['type' => 'error', 'message' => 'El método de pago solicitado no existe.'];
            }
        }
    } catch (Throwable $exception) {
        error_log('[Mesa360] Could not load payments: ' . $exception->getMessage());
        $dbError = 'No se pudo cargar la información de pagos en este momento. Intenta nuevamente más tarde.';
        $paymentRows = $paymentOrders = $paymentMethods = [];
    }
}

function moneyLabel(mixed $amount): string
{
    return 'RD$ ' . number_format((float) ($amount ?? 0), 2, '.', ',');
}

$pageDescriptions = [
    'pedidos' => 'Consulta y administra los pedidos del restaurante.',
    'mesas' => 'Organiza las mesas y consulta su disponibilidad.',
    'menu' => 'Gestiona los productos, precios y categorías del menú.',
    'inventario' => 'Controla los insumos, las existencias y sus movimientos.',
    'clientes' => 'Mantén organizada la información de los clientes.',
    'reservas' => 'Organiza las reservas y la asignación de mesas.',
    'empleados' => 'Administra los empleados, usuarios y permisos de acceso.',
    'reportes' => 'Consulta los indicadores de ventas y la actividad del restaurante.',
    'recetas' => 'Define los insumos y cantidades que componen los productos del menú.',
    'movimientos' => 'Registra entradas y salidas de inventario y consulta su historial.',
    'pagos' => 'Registra pagos vinculados a pedidos y administra los métodos aceptados.',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#151923">
    <title><?= h($currentTitle) ?> | Mesa360</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <!-- MESA360_AUTH_STYLESHEET -->
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="?page=dashboard" aria-label="Mesa360, ir al panel de control">
            <span class="brand-mark">M</span>
            <span class="brand-copy"><strong>Mesa360</strong><small>Gestión de restaurante</small></span>
        </a>
        <div class="sidebar-label">MENÚ PRINCIPAL</div>
        <nav class="nav-menu" aria-label="Navegación principal">
            <?php foreach ($navigation as $item): ?>
                <a class="nav-link <?= $page === $item['page'] ? 'active' : '' ?>"
                   href="?page=<?= h($item['page']) ?>"
                   <?= $page === $item['page'] ? 'aria-current="page"' : '' ?>>
                    <span class="nav-icon" aria-hidden="true"><?= h($item['icon']) ?></span>
                    <span><?= h($item['label']) ?></span>
                    <?php if ($item['page'] === 'dashboard'): ?><span class="active-marker"></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <!-- MESA360_AUTH_SIDEBAR -->
        <section class="auth-sidebar-user" aria-label="Sesión actual">
            <div class="auth-sidebar-user-copy">
                <strong><?= h($_SESSION['mesa360_username'] ?? 'Usuario') ?></strong>
                <span><?= h($_SESSION['mesa360_user_role'] ?? 'Personal') ?></span>
            </div>
            <form method="post" action="/logout.php" class="auth-sidebar-logout">
                <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                <button type="submit" class="auth-logout-button">Cerrar sesión</button>
            </form>
        </section>
    </aside>

    <div class="workspace">
        <header class="topbar">
            <div class="breadcrumb"><span>Mesa360</span><span class="crumb-separator">/</span><strong><?= h($currentTitle) ?></strong></div>
        </header>

        <main class="main-content">
            <?php if ($page === 'dashboard'): ?>
                <div class="page-heading">
                    <div>
                        <p class="eyebrow">MESA360</p>
                        <h1>Panel de control</h1>
                        <p class="page-subtitle">Gestiona las operaciones diarias del restaurante desde un solo lugar.</p>
                    </div>
                    <div class="date-display"><span class="date-icon">◷</span><span><?= h($currentDate) ?></span></div>
                </div>
                <section class="welcome-panel">
                    <div class="welcome-copy">
                        <span class="welcome-tag">OPERACIONES DEL RESTAURANTE</span>
                        <h2>Todo tu restaurante, en un solo lugar.</h2>
                        <p>Accede a las principales áreas de gestión de Mesa360 y organiza el trabajo diario del restaurante desde un mismo espacio.</p>
                        <a class="primary-button" href="?page=menu">Gestionar menú <span aria-hidden="true">→</span></a>
                    </div>
                    <div class="welcome-art" aria-hidden="true"><div class="art-circle circle-one"></div><div class="art-circle circle-two"></div><div class="art-tile">M<span>360</span></div></div>
                </section>
                <div class="section-heading"><div><h2>Accesos directos</h2><p>Selecciona un área para continuar con la gestión diaria.</p></div></div>
                <section class="module-grid" aria-label="Módulos de Mesa360">
                    <?php
                    $moduleCards = [
                        ['page' => 'pedidos', 'icon' => '≡', 'tone' => 'orange', 'title' => 'Pedidos', 'description' => 'Registrar pedidos y actualizar su estado.'],
                        ['page' => 'mesas', 'icon' => '▦', 'tone' => 'blue', 'title' => 'Mesas', 'description' => 'Consultar y gestionar la disponibilidad de mesas.'],
                        ['page' => 'menu', 'icon' => '◈', 'tone' => 'purple', 'title' => 'Menú y categorías', 'description' => 'Administrar productos, precios y categorías.'],
                        ['page' => 'inventario', 'icon' => '▤', 'tone' => 'green', 'title' => 'Inventario', 'description' => 'Controlar insumos, costos y niveles de existencias.'],
                        ['page' => 'movimientos', 'icon' => '↕', 'tone' => 'green', 'title' => 'Movimientos', 'description' => 'Registrar entradas y salidas de insumos.'],
                        ['page' => 'recetas', 'icon' => '♨', 'tone' => 'purple', 'title' => 'Recetas', 'description' => 'Definir insumos y cantidades por producto.'],
                        ['page' => 'pagos', 'icon' => '$', 'tone' => 'orange', 'title' => 'Pagos', 'description' => 'Registrar cobros y configurar métodos de pago.'],
                        ['page' => 'clientes', 'icon' => '♙', 'tone' => 'blue', 'title' => 'Clientes', 'description' => 'Mantener los datos de los clientes.'],
                        ['page' => 'reservas', 'icon' => '◷', 'tone' => 'purple', 'title' => 'Reservas', 'description' => 'Organizar reservas y asignación de mesas.'],
                        ['page' => 'empleados', 'icon' => '♧', 'tone' => 'green', 'title' => 'Empleados', 'description' => 'Mantener organizada la información del personal.'],
                        ['page' => 'reportes', 'icon' => '▥', 'tone' => 'orange', 'title' => 'Reportes', 'description' => 'Consultar resúmenes de ventas y actividad.'],
                    ];
                    $moduleCards = array_values(array_filter($moduleCards, static fn(array $card): bool => mesa360HasPermission($card['page'], 'ver')));
                    foreach ($moduleCards as $card): ?>
                        <a class="module-card" href="?page=<?= h($card['page']) ?>">
                            <div class="module-card-top"><span class="module-icon <?= h($card['tone']) ?>"><?= h($card['icon']) ?></span><span class="card-arrow" aria-hidden="true">↗</span></div>
                            <h3><?= h($card['title']) ?></h3>
                            <p><?= h($card['description']) ?></p>
                            <div class="card-footer"><span class="card-link">Abrir módulo →</span></div>
                        </a>
                    <?php endforeach; ?>
                </section>

            <?php elseif ($page === 'menu'): ?>
                <div class="page-heading">
                    <div>
                        <p class="eyebrow">GESTIÓN DEL RESTAURANTE</p>
                        <h1>Menú y categorías</h1>
                        <p class="page-subtitle">Administra los productos del menú, sus precios y la organización por categorías.</p>
                    </div>
                    <a class="secondary-button" href="?page=dashboard">← Volver al panel</a>
                </div>

                <?php if (is_array($flash)): ?>
                    <div class="notice notice-<?= h($flash['type'] ?? 'info') ?>" role="status"><?= h($flash['message'] ?? '') ?></div>
                <?php endif; ?>
                <?php if ($dbError !== null): ?>
                    <div class="notice notice-error" role="alert"><?= h($dbError) ?></div>
                <?php elseif ($menuNotice !== null): ?>
                    <div class="notice notice-error" role="status"><?= h($menuNotice) ?></div>
                <?php endif; ?>

                <div class="menu-toolbar">
                    <div><h2>Catálogo del restaurante</h2><p>Los datos que ves aquí se consultan desde la base de datos del sistema.</p></div>
                    <?php if ($menuTab === 'products'): ?>
                        <?php if (mesa360HasPermission('menu', 'crear')): ?><a class="primary-button" href="?page=menu&amp;tab=products&amp;new_product=1">＋ Nuevo producto</a><?php endif; ?>
                    <?php else: ?>
                        <?php if (mesa360HasPermission('menu', 'crear')): ?><a class="primary-button" href="?page=menu&amp;tab=categories&amp;new_category=1">＋ Nueva categoría</a><?php endif; ?>
                    <?php endif; ?>
                </div>

                <nav class="menu-tabs" aria-label="Secciones del catálogo">
                    <a class="menu-tab <?= $menuTab === 'products' ? 'active' : '' ?>" href="?page=menu&amp;tab=products" <?= $menuTab === 'products' ? 'aria-current="page"' : '' ?>>Productos <span><?= h($productCount) ?></span></a>
                    <a class="menu-tab <?= $menuTab === 'categories' ? 'active' : '' ?>" href="?page=menu&amp;tab=categories" <?= $menuTab === 'categories' ? 'aria-current="page"' : '' ?>>Categorías <span><?= h($categoryCount) ?></span></a>
                </nav>

                <?php if ($menuTab === 'products'): ?>
                    <?php if ($productFormOpen && count($categories) === 0 && $editingProduct === null): ?>
                        <section class="empty-panel compact-empty">
                            <div class="empty-icon">◈</div><h2>Primero crea una categoría</h2>
                            <p>Los productos se organizan por categoría. Registra una categoría y después podrás agregar productos al menú.</p>
                            <a class="primary-button" href="?page=menu&amp;tab=categories&amp;new_category=1">Crear categoría <span aria-hidden="true">→</span></a>
                        </section>
                    <?php elseif ($productFormOpen): ?>
                        <section class="crud-card form-card">
                            <div class="crud-card-heading"><div><p class="eyebrow">CATÁLOGO</p><h2><?= $editingProduct !== null ? 'Editar producto' : 'Nuevo producto' ?></h2><p>Completa la información que aparecerá en el catálogo.</p></div><a class="icon-close" href="?page=menu&amp;tab=products" aria-label="Cerrar formulario">×</a></div>
                            <form method="post" action="?page=menu&amp;tab=products" class="crud-form">
                                <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                                <input type="hidden" name="action" value="save_product">
                                <input type="hidden" name="id" value="<?= h($editingProduct['id_producto'] ?? 0) ?>">
                                <div class="form-field"><label for="product-name">Nombre del producto <span class="required-mark">*</span></label><input id="product-name" name="nombre" type="text" maxlength="255" required value="<?= h($productValues['nombre'] ?? '') ?>" autocomplete="off"></div>
                                <div class="form-field"><label for="product-category">Categoría <span class="required-mark">*</span></label><select id="product-category" name="id_categoria" required><option value="">Selecciona una categoría</option><?php foreach ($categories as $category): ?><option value="<?= h($category['id_categoria']) ?>" <?= (string) ($productValues['id_categoria'] ?? '') === (string) $category['id_categoria'] ? 'selected' : '' ?>><?= h($category['nombre_categoria'] ?? '') ?><?= (int) ($category['activo'] ?? 0) === 1 ? '' : ' (inactiva)' ?></option><?php endforeach; ?></select></div>
                                <div class="form-field"><label for="product-price">Precio de venta (RD$) <span class="required-mark">*</span></label><input id="product-price" name="precio" type="number" min="0" max="99999999.99" step="0.01" required value="<?= h($productValues['precio'] ?? '') ?>"></div>
                                <div class="form-field"><label for="product-cost">Costo (RD$)</label><input id="product-cost" name="costo" type="number" min="0" max="99999999.99" step="0.01" value="<?= h($productValues['costo'] ?? '') ?>"><small>Opcional. Puedes completarlo cuando registres el costo del producto.</small></div>
                                <div class="form-field form-field-full"><label for="product-description">Descripción</label><textarea id="product-description" name="descripcion" rows="3" placeholder="Descripción breve del producto"><?= h($productValues['descripcion'] ?? '') ?></textarea></div>
                                <div class="form-field form-field-full"><label class="checkbox-field"><input type="checkbox" name="activo" value="1" <?= (int) ($productValues['activo'] ?? 1) === 1 ? 'checked' : '' ?>><span>Producto disponible para la venta</span></label></div>
                                <div class="form-actions form-field-full"><a class="secondary-button" href="?page=menu&amp;tab=products">Cancelar</a><button class="primary-button" type="submit">Guardar producto</button></div>
                            </form>
                        </section>
                    <?php endif; ?>

                    <section class="crud-card table-card">
                        <div class="crud-card-heading table-card-heading"><div><h2>Productos registrados</h2><p><?= $productCount === 1 ? '1 producto encontrado' : h($productCount) . ' productos encontrados' ?><?= $menuSearch !== '' ? ' para “' . h($menuSearch) . '”' : '' ?></p></div>
                            <form class="search-form" method="get" action=""><input type="hidden" name="page" value="menu"><input type="hidden" name="tab" value="products"><label class="visually-hidden" for="product-search">Buscar productos</label><input id="product-search" type="search" name="q" value="<?= h($menuSearch) ?>" placeholder="Buscar producto..." maxlength="100"><button class="secondary-button" type="submit">Buscar</button><?php if ($menuSearch !== ''): ?><a class="clear-search" href="?page=menu&amp;tab=products">Limpiar</a><?php endif; ?></form>
                        </div>
                        <div class="table-wrap">
                            <table class="data-table"><thead><tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Costo</th><th>Estado</th><th class="actions-heading">Acciones</th></tr></thead><tbody>
                            <?php if ($products === []): ?>
                                <tr><td colspan="6"><div class="table-empty"><span class="empty-icon">◈</span><strong><?= $menuSearch !== '' ? 'No encontramos coincidencias' : 'Todavía no hay productos' ?></strong><p><?= $menuSearch !== '' ? 'Prueba con otro término de búsqueda.' : 'Agrega el primer producto para comenzar a construir el menú.' ?></p><?php if ($menuSearch === '' && count($categories) > 0): ?><?php if (mesa360HasPermission('menu', 'crear')): ?><a class="primary-button small-button" href="?page=menu&amp;tab=products&amp;new_product=1">＋ Agregar producto</a><?php endif; ?><?php elseif (count($categories) === 0): ?><?php if (mesa360HasPermission('menu', 'crear')): ?><a class="secondary-button" href="?page=menu&amp;tab=categories&amp;new_category=1">Crear categoría primero</a><?php endif; ?><?php endif; ?></div></td></tr>
                            <?php else: foreach ($products as $product): ?>
                                <tr>
                                    <td><div class="record-primary"><?= h($product['nombre'] ?? '') ?></div><div class="record-secondary">ID #<?= h($product['id_producto']) ?></div></td>
                                    <td><?= h($product['nombre_categoria'] ?? 'Sin categoría') ?></td>
                                    <td class="money-cell">RD$ <?= h(number_format((float) ($product['precio'] ?? 0), 2, '.', ',')) ?></td>
                                    <td><?= $product['costo'] === null ? '—' : 'RD$ ' . h(number_format((float) $product['costo'], 2, '.', ',')) ?></td>
                                    <td><span class="status-pill <?= (int) ($product['activo'] ?? 0) === 1 ? 'status-active' : 'status-inactive' ?>"><span></span><?= (int) ($product['activo'] ?? 0) === 1 ? 'Disponible' : 'Inactivo' ?></span></td>
                                    <td><div class="row-actions"><?php if (mesa360HasPermission('menu', 'editar')): ?><a class="text-action" href="?page=menu&amp;tab=products&amp;edit_product=<?= h($product['id_producto']) ?>">Editar</a><?php endif; ?><?php if (mesa360HasPermission('menu', 'eliminar')): ?><form method="post" action="?page=menu&amp;tab=products" onsubmit="return confirm('¿Eliminar este producto? Si está relacionado con recetas o pedidos, MySQL impedirá la eliminación.');"><input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="id" value="<?= h($product['id_producto']) ?>"><button class="text-action danger-action" type="submit">Eliminar</button></form><?php endif; ?></div></td>
                                </tr>
                            <?php endforeach; endif; ?>
                            </tbody></table>
                        </div>
                    </section>

                <?php else: ?>
                    <?php if ($categoryFormOpen): ?>
                        <section class="crud-card form-card">
                            <div class="crud-card-heading"><div><p class="eyebrow">CATÁLOGO</p><h2><?= $editingCategory !== null ? 'Editar categoría' : 'Nueva categoría' ?></h2><p>Usa categorías para mantener organizado el menú.</p></div><a class="icon-close" href="?page=menu&amp;tab=categories" aria-label="Cerrar formulario">×</a></div>
                            <form method="post" action="?page=menu&amp;tab=categories" class="crud-form">
                                <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                                <input type="hidden" name="action" value="save_category">
                                <input type="hidden" name="id" value="<?= h($editingCategory['id_categoria'] ?? 0) ?>">
                                <div class="form-field"><label for="category-name">Nombre de la categoría <span class="required-mark">*</span></label><input id="category-name" name="nombre_categoria" type="text" maxlength="255" required value="<?= h($categoryValues['nombre_categoria'] ?? '') ?>"></div>
                                <div class="form-field"><label for="category-order">Orden de visualización <span class="required-mark">*</span></label><input id="category-order" name="orden" type="number" min="0" step="1" required value="<?= h($categoryValues['orden'] ?? 0) ?>"><small>Las categorías se muestran primero por este número y luego por nombre.</small></div>
                                <div class="form-field form-field-full"><label for="category-description">Descripción</label><textarea id="category-description" name="descripcion" rows="3" placeholder="Descripción opcional"><?= h($categoryValues['descripcion'] ?? '') ?></textarea></div>
                                <div class="form-field form-field-full"><label class="checkbox-field"><input type="checkbox" name="activo" value="1" <?= (int) ($categoryValues['activo'] ?? 1) === 1 ? 'checked' : '' ?>><span>Categoría activa</span></label></div>
                                <div class="form-actions form-field-full"><a class="secondary-button" href="?page=menu&amp;tab=categories">Cancelar</a><button class="primary-button" type="submit">Guardar categoría</button></div>
                            </form>
                        </section>
                    <?php endif; ?>

                    <section class="crud-card table-card">
                        <div class="crud-card-heading table-card-heading"><div><h2>Categorías registradas</h2><p><?= $categoryCount === 1 ? '1 categoría registrada' : h($categoryCount) . ' categorías registradas' ?></p></div></div>
                        <div class="table-wrap"><table class="data-table"><thead><tr><th>Categoría</th><th>Descripción</th><th>Orden</th><th>Productos</th><th>Estado</th><th class="actions-heading">Acciones</th></tr></thead><tbody>
                        <?php if ($categories === []): ?>
                            <tr><td colspan="6"><div class="table-empty"><span class="empty-icon">◈</span><strong>Todavía no hay categorías</strong><p>Crea una categoría —por ejemplo, Entradas, Platos principales o Bebidas— para comenzar a organizar los productos.</p><?php if (mesa360HasPermission('menu', 'crear')): ?><a class="primary-button small-button" href="?page=menu&amp;tab=categories&amp;new_category=1">＋ Crear primera categoría</a><?php endif; ?></div></td></tr>
                        <?php else: foreach ($categories as $category): ?>
                            <tr>
                                <td><div class="record-primary"><?= h($category['nombre_categoria'] ?? '') ?></div><div class="record-secondary">ID #<?= h($category['id_categoria']) ?></div></td>
                                <td class="description-cell"><?= h($category['descripcion'] ?? '—') ?></td>
                                <td><?= h($category['orden'] ?? '—') ?></td>
                                <td><?= h($category['productos_count'] ?? 0) ?></td>
                                <td><span class="status-pill <?= (int) ($category['activo'] ?? 0) === 1 ? 'status-active' : 'status-inactive' ?>"><span></span><?= (int) ($category['activo'] ?? 0) === 1 ? 'Activa' : 'Inactiva' ?></span></td>
                                <td><div class="row-actions"><?php if (mesa360HasPermission('menu', 'editar')): ?><a class="text-action" href="?page=menu&amp;tab=categories&amp;edit_category=<?= h($category['id_categoria']) ?>">Editar</a><?php endif; ?><?php if (mesa360HasPermission('menu', 'eliminar')): ?><form method="post" action="?page=menu&amp;tab=categories" onsubmit="return confirm('¿Eliminar esta categoría? Solo se podrá eliminar si no tiene productos asociados.');"><input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="id" value="<?= h($category['id_categoria']) ?>"><button class="text-action danger-action" type="submit" <?= (int) $category['productos_count'] > 0 ? 'disabled title="Esta categoría tiene productos asociados"' : '' ?>>Eliminar</button></form><?php endif; ?></div></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody></table></div>
                    </section>
                <?php endif; ?>

            <?php elseif (isset($entityModules[$page])): ?>
                <?php $module = $entityModules[$page]; ?>
                <div class="page-heading">
                    <div>
                        <p class="eyebrow">GESTIÓN DEL RESTAURANTE</p>
                        <h1><?= h($module['title']) ?></h1>
                        <p class="page-subtitle"><?= h($module['description']) ?></p>
                    </div>
                    <a class="secondary-button" href="?page=dashboard">← Volver al panel</a>
                </div>

                <?php if (is_array($flash)): ?>
                    <div class="notice notice-<?= h($flash['type'] ?? 'info') ?>" role="status"><?= h($flash['message'] ?? '') ?></div>
                <?php endif; ?>
                <?php if ($dbError !== null): ?>
                    <div class="notice notice-error" role="alert"><?= h($dbError) ?></div>
                <?php elseif ($entityNotice !== null): ?>
                    <div class="notice notice-error" role="status"><?= h($entityNotice) ?></div>
                <?php endif; ?>

                <div class="menu-toolbar entity-toolbar">
                    <div><h2><?= h($module['title']) ?> registrados</h2><p><?= count($entityRows) === 1 ? '1 registro encontrado' : h(count($entityRows)) . ' registros encontrados' ?><?= $entitySearch !== '' ? ' para “' . h($entitySearch) . '”' : '' ?></p></div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap"><?php if ($page === 'inventario'): ?><a class="secondary-button" href="?page=movimientos">Registrar movimiento</a><a class="secondary-button" href="?page=recetas">Gestionar recetas</a><?php endif; ?><?php if (mesa360HasPermission($page, 'crear')): ?><a class="primary-button" href="?page=<?= h($page) ?>&amp;new=1">＋ <?= h($module['newLabel']) ?></a><?php endif; ?></div>
                </div>

                <?php if ($page === 'reservas' && $entityFormOpen && (($entityOptions['id_cliente'] ?? []) === [] || ($entityOptions['id_mesa'] ?? []) === [])): ?>
                    <div class="notice notice-info" role="status">Para registrar una reserva, primero registra un cliente y al menos una mesa habilitada para el servicio.</div>
                <?php endif; ?>

                <?php if ($page === 'inventario'): ?><div class="notice notice-info">Las existencias se actualizan mediante entradas y salidas para conservar el historial de movimientos. El stock actual no se modifica directamente desde la ficha del insumo.</div><?php endif; ?>
                <?php if ($entityFormOpen): ?>
                    <section class="crud-card form-card">
                        <div class="crud-card-heading">
                            <div><p class="eyebrow">GESTIÓN</p><h2><?= $entityEditing !== null ? 'Editar ' . h($module['singular']) : h($module['newLabel']) ?></h2><p>Completa los datos y guarda los cambios.</p></div>
                            <a class="icon-close" href="?page=<?= h($page) ?>" aria-label="Cerrar formulario">×</a>
                        </div>
                        <form method="post" action="?page=<?= h($page) ?>" class="crud-form">
                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                            <input type="hidden" name="action" value="save_record">
                            <input type="hidden" name="id" value="<?= h($entityEditing[$module['pk']] ?? 0) ?>">
                            <?php foreach ($module['fields'] as $fieldName => $field): ?>
                                <?php $fieldValue = $entityValues[$fieldName] ?? ''; if (($field['type'] ?? '') === 'time' && is_string($fieldValue)) { $fieldValue = substr($fieldValue, 0, 5); } ?>
                                <div class="form-field <?= !empty($field['full']) ? 'form-field-full' : '' ?>">
                                    <?php if ($field['type'] === 'checkbox'): ?>
                                        <label class="checkbox-field" for="field-<?= h($fieldName) ?>"><input id="field-<?= h($fieldName) ?>" type="checkbox" name="<?= h($fieldName) ?>" value="1" <?= (int) ($entityValues[$fieldName] ?? $field['default'] ?? 0) === 1 ? 'checked' : '' ?> <?= ($page === 'mesas' && (string)($_SESSION['mesa360_user_role'] ?? '') === 'Mesera') ? 'disabled' : '' ?>><span><?= h($field['label']) ?></span></label>
                                    <?php else: ?>
                                        <label for="field-<?= h($fieldName) ?>"><?= h($field['label']) ?><?= !empty($field['required']) ? ' <span class="required-mark">*</span>' : '' ?></label>
                                        <?php if ($field['type'] === 'textarea'): ?>
                                            <textarea id="field-<?= h($fieldName) ?>" name="<?= h($fieldName) ?>" rows="3" <?= !empty($field['required']) ? 'required' : '' ?>><?= h($entityValues[$fieldName] ?? '') ?></textarea>
                                        <?php elseif ($field['type'] === 'select'): ?>
                                            <select id="field-<?= h($fieldName) ?>" name="<?= h($fieldName) ?>" <?= !empty($field['required']) ? 'required' : '' ?>>
                                                <option value="">Selecciona una opción</option>
                                                <?php if (isset($field['options'])): ?>
                                                    <?php foreach ($field['options'] as $optionValue => $optionLabel): ?>
                                                        <option value="<?= h($optionValue) ?>" <?= (string) ($entityValues[$fieldName] ?? '') === (string) $optionValue ? 'selected' : '' ?>><?= h($optionLabel) ?></option>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <?php foreach (($entityOptions[$fieldName] ?? []) as $option): ?>
                                                        <option value="<?= h($option['option_value']) ?>" <?= (string) ($entityValues[$fieldName] ?? '') === (string) $option['option_value'] ? 'selected' : '' ?>><?= h($option['option_label']) ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        <?php else: ?>
                                            <input id="field-<?= h($fieldName) ?>" type="<?= h($field['type']) ?>" name="<?= h($fieldName) ?>" value="<?= h($fieldValue) ?>" <?= isset($field['maxlength']) ? 'maxlength="' . h($field['maxlength']) . '"' : '' ?> <?= isset($field['min']) ? 'min="' . h($field['min']) . '"' : '' ?> <?= isset($field['step']) ? 'step="' . h($field['step']) . '"' : '' ?> <?= ($page === 'inventario' && $fieldName === 'stock_actual') ? 'readonly aria-readonly="true"' : '' ?> <?= ($page === 'mesas' && strcasecmp((string)($_SESSION['mesa360_user_role'] ?? ''), 'Mesera') === 0 && $fieldName !== 'estado') ? 'readonly aria-readonly="true"' : '' ?> <?= !empty($field['required']) ? 'required' : '' ?>>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            <div class="form-actions form-field-full"><a class="secondary-button" href="?page=<?= h($page) ?>">Cancelar</a><button class="primary-button" type="submit">Guardar <?= h($module['singular']) ?></button></div>
                        </form>
                    </section>
                <?php endif; ?>

                <section class="crud-card table-card">
                    <div class="crud-card-heading table-card-heading">
                        <div><h2>Listado de <?= h(strtolower($module['title'])) ?></h2><p>Los registros se consultan desde la base de datos de Mesa360.</p></div>
                        <form class="search-form" method="get" action="">
                            <input type="hidden" name="page" value="<?= h($page) ?>">
                            <label class="visually-hidden" for="entity-search">Buscar <?= h(strtolower($module['title'])) ?></label>
                            <input id="entity-search" type="search" name="q" value="<?= h($entitySearch) ?>" placeholder="<?= h($module['searchPlaceholder']) ?>" maxlength="100">
                            <button class="secondary-button" type="submit">Buscar</button>
                            <?php if ($entitySearch !== ''): ?><a class="clear-search" href="?page=<?= h($page) ?>">Limpiar</a><?php endif; ?>
                        </form>
                    </div>
                    <div class="table-wrap">
                        <table class="data-table"><thead><tr>
                            <?php foreach ($module['columns'] as $column): ?><th><?= h($column['label']) ?></th><?php endforeach; ?>
                            <th class="actions-heading">Acciones</th>
                        </tr></thead><tbody>
                            <?php if ($entityRows === []): ?>
                                <tr><td colspan="<?= count($module['columns']) + 1 ?>"><div class="table-empty"><span class="empty-icon"><?= h($navigation[array_search($page, array_column($navigation, 'page'))]['icon'] ?? '•') ?></span><strong><?= $entitySearch !== '' ? 'No encontramos coincidencias' : 'Todavía no hay registros' ?></strong><p><?= $entitySearch !== '' ? 'Prueba con otro término de búsqueda.' : 'Agrega el primer registro para comenzar a utilizar este módulo.' ?></p><?php if ($entitySearch === ''): ?><?php if (mesa360HasPermission($page, 'crear')): ?><a class="primary-button small-button" href="?page=<?= h($page) ?>&amp;new=1">＋ <?= h($module['newLabel']) ?></a><?php endif; ?><?php endif; ?></div></td></tr>
                            <?php else: foreach ($entityRows as $entityRow): ?>
                                <tr>
                                    <?php foreach ($module['columns'] as $column): ?>
                                        <?php $value = $entityRow[$column['key']] ?? null; ?>
                                        <td>
                                            <?php if (($column['format'] ?? '') === 'primary'): ?>
                                                <div class="record-primary"><?= h($value ?? '—') ?></div><div class="record-secondary">ID #<?= h($entityRow[$module['pk']] ?? '') ?></div>
                                            <?php elseif (($column['format'] ?? '') === 'active'): ?>
                                                <span class="status-pill <?= (int) ($value ?? 0) === 1 ? 'status-active' : 'status-inactive' ?>"><span></span><?= (int) ($value ?? 0) === 1 ? 'Activo' : 'Inactivo' ?></span>
                                            <?php elseif (in_array(($column['format'] ?? ''), ['status', 'reservation-status'], true)): ?>
                                                <span class="status-pill <?= h(moduleStatusClass((string) ($value ?? ''))) ?>"><span></span><?= h($value ?? '—') ?></span>
                                            <?php elseif (($column['format'] ?? '') === 'money'): ?>
                                                <?= h(moneyLabel($value)) ?>
                                            <?php elseif (($column['format'] ?? '') === 'date'): ?>
                                                <?= h(moduleDate((string) ($value ?? ''))) ?>
                                            <?php elseif (($column['format'] ?? '') === 'time'): ?>
                                                <?= h($value ? substr((string) $value, 0, 5) : '—') ?>
                                            <?php else: ?>
                                                <?= h($value === null || $value === '' ? '—' : $value) ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td><div class="row-actions"><?php if (mesa360HasPermission($page, 'editar')): ?><a class="text-action" href="?page=<?= h($page) ?>&amp;edit=<?= h($entityRow[$module['pk']]) ?>">Editar</a><?php endif; ?><?php if (mesa360HasPermission($page, 'eliminar')): ?><form method="post" action="?page=<?= h($page) ?>" onsubmit="return confirm('¿Eliminar este registro? Si está relacionado con otros datos, el sistema impedirá la eliminación.');"><input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>"><input type="hidden" name="action" value="delete_record"><input type="hidden" name="id" value="<?= h($entityRow[$module['pk']]) ?>"><button class="text-action danger-action" type="submit">Eliminar</button></form><?php endif; ?></div></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody></table>
                    </div>
                </section>

            <?php elseif ($page === 'pedidos'): ?>
                <div class="page-heading">
                    <div><p class="eyebrow">GESTIÓN DEL RESTAURANTE</p><h1>Pedidos</h1><p class="page-subtitle">Registra pedidos, administra sus productos y actualiza su estado.</p></div>
                    <a class="secondary-button" href="?page=dashboard">← Volver al panel</a>
                </div>
                <?php if (is_array($flash)): ?><div class="notice notice-<?= h($flash['type'] ?? 'info') ?>" role="status"><?= h($flash['message'] ?? '') ?></div><?php endif; ?>
                <?php if ($dbError !== null): ?><div class="notice notice-error" role="alert"><?= h($dbError) ?></div><?php elseif ($orderNotice !== null): ?><div class="notice notice-error" role="status"><?= h($orderNotice) ?></div><?php endif; ?>
                <div class="menu-toolbar entity-toolbar"><div><h2>Gestión de pedidos</h2><p>Los pedidos y sus líneas se guardan en la base de datos de Mesa360.</p></div><?php if (mesa360HasPermission('pedidos', 'crear')): ?><a class="primary-button" href="?page=pedidos&amp;new=1">＋ Nuevo pedido</a><?php endif; ?></div>
                <?php if ($orderFormOpen && $dbError === null): ?>
                    <section class="crud-card form-card">
                        <div class="crud-card-heading"><div><p class="eyebrow"><?= $orderEditing ? 'EDICIÓN' : 'NUEVO REGISTRO' ?></p><h2><?= $orderEditing ? 'Editar pedido #' . h($orderEditing['id_pedido']) : 'Registrar pedido' ?></h2><p>Selecciona productos activos; los precios y subtotales se calculan con los datos guardados en el sistema.</p></div><a class="icon-close" href="?page=pedidos" aria-label="Cerrar formulario">×</a></div>
                        <?php if ($orderProducts === []): ?><div class="notice notice-info" style="margin:0 23px 18px">Primero registra al menos un producto activo en <a class="text-action" href="?page=menu&amp;tab=products">Menú y categorías</a> para poder crear pedidos.</div><?php endif; ?>
                        <form class="crud-form order-form" method="post" action="?page=pedidos" id="order-form">
                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>"><input type="hidden" name="action" value="save_order"><input type="hidden" name="id" value="<?= h($orderEditing['id_pedido'] ?? 0) ?>">
                            <div class="form-field"><label for="order-type">Tipo de pedido <span class="required-mark">*</span></label><select id="order-type" name="tipo_pedido" required><option value="Local" <?= ($orderValues['tipo_pedido'] ?? 'Local') === 'Local' ? 'selected' : '' ?>>Consumo en el restaurante</option><option value="Para llevar" <?= ($orderValues['tipo_pedido'] ?? '') === 'Para llevar' ? 'selected' : '' ?>>Para llevar</option></select></div>
                            <div class="form-field"><label for="order-status">Estado <span class="required-mark">*</span></label><select id="order-status" name="estado_actual" required><?php foreach (['Pendiente','Preparando','Listo','Servido','Cancelado'] as $statusOption): ?><option value="<?= h($statusOption) ?>" <?= ($orderValues['estado_actual'] ?? 'Pendiente') === $statusOption ? 'selected' : '' ?>><?= h($statusOption) ?></option><?php endforeach; ?></select></div>
                            <div class="form-field"><label for="order-customer">Cliente (opcional)</label><select id="order-customer" name="id_cliente"><option value="">Sin cliente asociado</option><?php foreach ($orderCustomers as $customer): ?><option value="<?= h($customer['id_cliente']) ?>" <?= (string) ($orderValues['id_cliente'] ?? '') === (string) $customer['id_cliente'] ? 'selected' : '' ?>><?= h($customer['nombre']) ?></option><?php endforeach; ?></select></div>
                            <div class="form-field" id="order-table-field"><label for="order-table">Mesa <span class="required-mark">*</span></label><select id="order-table" name="id_mesa"><option value="">Selecciona una mesa</option><?php foreach ($orderTables as $table): ?><option value="<?= h($table['id_mesa']) ?>" <?= (string) ($orderValues['id_mesa'] ?? '') === (string) $table['id_mesa'] ? 'selected' : '' ?>>Mesa <?= h($table['numero_mesa']) ?> · <?= h($table['capacidad']) ?> personas</option><?php endforeach; ?></select><small>Requerida para pedidos de consumo local.</small></div>
                            <div class="form-field"><label for="order-waiter">Mesero (opcional)</label><select id="order-waiter" name="id_empleado_mesero"><option value="">Sin asignar</option><?php foreach ($orderEmployees as $employee): ?><option value="<?= h($employee['id_empleado']) ?>" <?= (string) ($orderValues['id_empleado_mesero'] ?? '') === (string) $employee['id_empleado'] ? 'selected' : '' ?>><?= h($employee['nombre']) ?><?= !empty($employee['cargo']) ? ' · ' . h($employee['cargo']) : '' ?></option><?php endforeach; ?></select></div>
                            <div class="form-field"><label for="order-notes">Observaciones</label><textarea id="order-notes" name="observaciones" rows="2" maxlength="5000"><?= h($orderValues['observaciones'] ?? '') ?></textarea></div>
                            <div class="form-field form-field-full"><div class="order-section-heading"><div><h3>Productos del pedido</h3><p>El total se calcula en el servidor a partir de los precios actuales del menú.</p></div><button class="secondary-button" type="button" id="add-order-item" <?= $orderProducts === [] ? 'disabled' : '' ?>>＋ Agregar producto</button></div>
                                <div class="order-items" id="order-items">
                                    <?php foreach ($orderItemsForm as $itemIndex => $item): ?><div class="order-item-row">
                                        <div class="form-field"><label>Producto</label><select name="producto_id[]" class="order-product-select" required><option value="">Selecciona un producto</option><?php foreach ($orderProducts as $product): ?><option value="<?= h($product['id_producto']) ?>" data-price="<?= h($product['precio']) ?>" <?= (string) ($item['id_producto'] ?? '') === (string) $product['id_producto'] ? 'selected' : '' ?>><?= h($product['nombre']) ?> — <?= h(moneyLabel($product['precio'])) ?></option><?php endforeach; ?></select></div>
                                        <div class="form-field"><label>Cantidad</label><input class="order-quantity" type="number" name="cantidad[]" min="1" max="10000" step="1" value="<?= h($item['cantidad'] ?? '1') ?>" required></div>
                                        <div class="form-field"><label>Notas de preparación</label><input type="text" name="notas_linea[]" maxlength="1000" value="<?= h($item['notas_preparacion'] ?? '') ?>" placeholder="Opcional"></div>
                                        <button class="text-action danger-action remove-order-item" type="button" aria-label="Quitar producto" <?= count($orderItemsForm) < 2 ? 'disabled' : '' ?>>Quitar</button>
                                    </div><?php endforeach; ?>
                                </div>
                                <div class="order-total-row"><span>Total estimado</span><strong id="order-total-preview">RD$ 0.00</strong></div>
                                <small>El cálculo mostrado es una estimación visual; el servidor vuelve a calcular el importe usando los precios guardados. Impuestos y descuentos no se aplican automáticamente en esta etapa.</small>
                            </div>
                            <div class="form-actions form-field-full"><a class="secondary-button" href="?page=pedidos">Cancelar</a><button class="primary-button" type="submit" <?= $orderProducts === [] ? 'disabled' : '' ?>><?= $orderEditing ? 'Guardar cambios' : 'Guardar pedido' ?></button></div>
                        </form>
                    </section>
                <?php endif; ?>
                <section class="crud-card table-card">
                    <div class="crud-card-heading table-card-heading"><div><h2>Pedidos registrados</h2><p><?php if ($orderRows === []): ?><?= $orderSearch !== '' ? 'No hay pedidos que coincidan con tu búsqueda.' : 'Todavía no hay pedidos registrados.' ?><?php elseif (count($orderRows) === 1): ?>1 pedido registrado.<?php else: ?><?= count($orderRows) ?> pedidos registrados.<?php endif; ?></p></div><form class="search-form" method="get" action=""><input type="hidden" name="page" value="pedidos"><label class="visually-hidden" for="order-search">Buscar pedidos</label><input id="order-search" type="search" name="q" value="<?= h($orderSearch) ?>" placeholder="Buscar por número, cliente o estado..." maxlength="100"><button class="secondary-button" type="submit">Buscar</button><?php if ($orderSearch !== ''): ?><a class="clear-search" href="?page=pedidos">Limpiar</a><?php endif; ?></form></div>
                    <div class="table-wrap"><table class="data-table"><thead><tr><th>Pedido</th><th>Fecha</th><th>Tipo</th><th>Cliente</th><th>Mesa</th><th>Productos</th><th>Total</th><th>Estado</th><th class="actions-heading">Acciones</th></tr></thead><tbody>
                    <?php if ($orderRows === []): ?><tr><td colspan="9"><div class="table-empty"><span class="empty-icon">≡</span><strong><?= $orderSearch !== '' ? 'No encontramos pedidos' : 'Todavía no hay pedidos' ?></strong><p><?= $orderSearch !== '' ? 'Prueba otro término de búsqueda.' : 'Registra un pedido con productos activos para iniciar el historial del restaurante.' ?></p><?php if ($orderSearch === ''): ?><?php if (mesa360HasPermission('pedidos', 'crear')): ?><a class="primary-button small-button" href="?page=pedidos&amp;new=1">＋ Registrar primer pedido</a><?php endif; ?><?php endif; ?></div></td></tr><?php else: foreach ($orderRows as $order): ?><tr><td><div class="record-primary">Pedido #<?= h($order['id_pedido']) ?></div></td><td><?= h($order['fecha_pedido'] ? date('d/m/Y H:i', strtotime($order['fecha_pedido'])) : '—') ?></td><td><?= h($order['tipo_pedido']) ?></td><td><?= h($order['cliente_nombre'] ?: 'Cliente ocasional') ?></td><td><?= $order['numero_mesa'] !== null ? 'Mesa ' . h($order['numero_mesa']) : '—' ?></td><td><?= h($order['lineas']) ?></td><td class="money-cell"><?= h(moneyLabel($order['total'])) ?></td><td><span class="status-pill <?= h(moduleStatusClass((string) $order['estado_actual'])) ?>"><span></span><?= h($order['estado_actual']) ?></span></td><td><div class="row-actions"><?php if (mesa360HasPermission('pedidos', 'editar')): ?><a class="text-action" href="?page=pedidos&amp;edit=<?= h($order['id_pedido']) ?>">Ver / editar</a><?php endif; ?><?php if (mesa360HasPermission('pedidos', 'eliminar')): ?><form method="post" action="?page=pedidos" onsubmit="return confirm('¿Eliminar este pedido y sus detalles? Los pedidos con pagos registrados no se pueden eliminar.');"><input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>"><input type="hidden" name="action" value="delete_order"><input type="hidden" name="id" value="<?= h($order['id_pedido']) ?>"><button class="text-action danger-action" type="submit">Eliminar</button></form><?php endif; ?></div></td></tr><?php endforeach; endif; ?>
                    </tbody></table></div>
                </section>

            <?php elseif ($page === 'movimientos'): ?>
                <div class="page-heading"><div><p class="eyebrow">GESTIÓN DEL RESTAURANTE</p><h1>Movimientos de inventario</h1><p class="page-subtitle">Registra entradas y salidas de insumos y conserva el historial de existencias.</p></div><a class="secondary-button" href="?page=inventario">← Volver al inventario</a></div>
                <?php if (is_array($flash)): ?><div class="notice notice-<?= h($flash['type'] ?? 'info') ?>" role="status"><?= h($flash['message'] ?? '') ?></div><?php endif; ?>
                <?php if ($dbError !== null): ?><div class="notice notice-error" role="alert"><?= h($dbError) ?></div><?php endif; ?>
                <section class="crud-card form-card"><div class="crud-card-heading"><div><p class="eyebrow">EXISTENCIAS</p><h2>Registrar movimiento</h2><p>Las salidas no pueden superar el stock disponible. Cada movimiento actualiza la existencia y queda registrado en el historial.</p></div></div>
                <?php if ($movementInsums === []): ?><div class="notice notice-info" style="margin:0 23px 20px">Primero registra un insumo activo en <a class="text-action" href="?page=inventario&amp;new=1">Inventario</a>. La existencia inicial comienza en cero.</div><?php else: ?>
                <form method="post" action="?page=movimientos" class="crud-form"><input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>"><input type="hidden" name="action" value="record_movement">
                    <div class="form-field"><label for="movement-insumo">Insumo <span class="required-mark">*</span></label><select id="movement-insumo" name="id_insumo" required><option value="">Selecciona un insumo</option><?php foreach ($movementInsums as $insumo): ?><option value="<?= h($insumo['id_insumo']) ?>"><?= h($insumo['nombre_insumo']) ?> · Stock: <?= h(number_format((float) $insumo['stock_actual'], 2, '.', ',')) ?> <?= h($insumo['unidad_medida']) ?></option><?php endforeach; ?></select></div>
                    <div class="form-field"><label for="movement-type">Tipo de movimiento <span class="required-mark">*</span></label><select id="movement-type" name="tipo_movimiento" required><option value="Entrada">Entrada (aumentar stock)</option><option value="Salida">Salida (reducir stock)</option></select></div>
                    <div class="form-field"><label for="movement-quantity">Cantidad <span class="required-mark">*</span></label><input id="movement-quantity" name="cantidad" type="number" min="0.01" step="0.01" required></div>
                    <div class="form-field form-field-full"><label for="movement-reason">Motivo <span class="required-mark">*</span></label><input id="movement-reason" name="motivo" type="text" maxlength="255" placeholder="Ej. Compra de proveedor, ajuste de inventario..." required></div>
                    <div class="form-actions form-field-full"><button class="primary-button" type="submit">Registrar movimiento</button></div>
                </form><?php endif; ?></section>
                <section class="crud-card table-card" style="margin-top:18px"><div class="crud-card-heading"><div><h2>Historial de movimientos</h2><p>Los movimientos más recientes del inventario.</p></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Fecha</th><th>Insumo</th><th>Tipo</th><th>Cantidad</th><th>Motivo</th></tr></thead><tbody><?php if ($movementRows === []): ?><tr><td colspan="5"><div class="table-empty"><strong>Todavía no hay movimientos registrados</strong><p>Cuando registres una entrada o salida, aparecerá aquí.</p></div></td></tr><?php else: foreach ($movementRows as $mv): ?><tr><td><?= h($mv['fecha_movimiento'] ? date('d/m/Y H:i', strtotime($mv['fecha_movimiento'])) : '—') ?></td><td><div class="record-primary"><?= h($mv['nombre_insumo']) ?></div><div class="record-secondary"><?= h($mv['unidad_medida']) ?></div></td><td><span class="status-pill <?= $mv['tipo_movimiento'] === 'Entrada' ? 'status-active' : 'status-pending' ?>"><span></span><?= h($mv['tipo_movimiento']) ?></span></td><td><?= h(number_format((float) $mv['cantidad'], 2, '.', ',')) ?></td><td><?= h($mv['motivo']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>

            <?php elseif ($page === 'pagos'): ?>
                <div class="page-heading"><div><p class="eyebrow">GESTIÓN DEL RESTAURANTE</p><h1>Pagos</h1><p class="page-subtitle">Registra pagos de pedidos y administra los métodos aceptados por el restaurante.</p></div><a class="secondary-button" href="?page=dashboard">← Volver al panel</a></div>
                <?php if (is_array($flash)): ?><div class="notice notice-<?= h($flash['type'] ?? 'info') ?>" role="status"><?= h($flash['message'] ?? '') ?></div><?php endif; ?>
                <?php if ($dbError !== null): ?><div class="notice notice-error" role="alert"><?= h($dbError) ?></div><?php endif; ?>
                <nav class="menu-tabs" aria-label="Secciones de pagos"><a class="menu-tab <?= $paymentTab === 'payments' ? 'active' : '' ?>" href="?page=pagos&amp;tab=payments">Pagos registrados <span><?= h(count($paymentRows)) ?></span></a><?php if (mesa360HasPermission('pagos', 'gestionar_metodos')): ?><a class="menu-tab <?= $paymentTab === 'methods' ? 'active' : '' ?>" href="?page=pagos&amp;tab=methods">Métodos de pago <span><?= h(count($paymentMethods)) ?></span></a><?php endif; ?></nav>
                <?php if ($paymentTab === 'methods'): ?>
                    <?php if ($paymentMethodOpen): ?><section class="crud-card form-card"><div class="crud-card-heading"><div><p class="eyebrow">CONFIGURACIÓN</p><h2><?= $paymentMethodEditing ? 'Editar método de pago' : 'Nuevo método de pago' ?></h2><p>Los métodos usados en operaciones históricas no se pueden eliminar; pueden desactivarse.</p></div><a class="icon-close" href="?page=pagos&amp;tab=methods" aria-label="Cerrar formulario">×</a></div><form method="post" action="?page=pagos&amp;tab=methods" class="crud-form"><input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>"><input type="hidden" name="action" value="save_method"><input type="hidden" name="id_metodo_pago" value="<?= h($paymentMethodEditing['id_metodo_pago'] ?? 0) ?>"><div class="form-field"><label for="method-name">Nombre del método <span class="required-mark">*</span></label><input id="method-name" name="nombre_metodo" maxlength="255" required value="<?= h($paymentMethodValues['nombre_metodo']) ?>" placeholder="Ej. Efectivo, Tarjeta, Transferencia"></div><div class="form-field"><label for="method-description">Descripción</label><input id="method-description" name="descripcion" maxlength="5000" value="<?= h($paymentMethodValues['descripcion']) ?>"></div><div class="form-field form-field-full"><label class="checkbox-field" for="method-reference"><input id="method-reference" type="checkbox" name="requiere_referencia" value="1" <?= (int) $paymentMethodValues['requiere_referencia'] === 1 ? 'checked' : '' ?>><span>Requiere referencia de transacción</span></label><label class="checkbox-field" for="method-active"><input id="method-active" type="checkbox" name="activo" value="1" <?= (int) $paymentMethodValues['activo'] === 1 ? 'checked' : '' ?>><span>Método activo</span></label></div><div class="form-actions form-field-full"><a class="secondary-button" href="?page=pagos&amp;tab=methods">Cancelar</a><button class="primary-button" type="submit">Guardar método</button></div></form></section><?php endif; ?>
                    <div class="menu-toolbar entity-toolbar"><div><h2>Métodos de pago</h2><p>Configura las formas de pago disponibles.</p></div><?php if (mesa360HasPermission('pagos', 'gestionar_metodos')): ?><a class="primary-button" href="?page=pagos&amp;tab=methods&amp;new_method=1">＋ Nuevo método</a><?php endif; ?></div>
                    <section class="crud-card table-card"><div class="table-wrap"><table class="data-table"><thead><tr><th>Método</th><th>Descripción</th><th>Referencia</th><th>Estado</th><th class="actions-heading">Acciones</th></tr></thead><tbody><?php if ($paymentMethods === []): ?><tr><td colspan="5"><div class="table-empty"><strong>No hay métodos de pago configurados</strong><p>Agrega al menos uno antes de registrar un pago.</p><a class="primary-button small-button" href="?page=pagos&amp;tab=methods&amp;new_method=1">＋ Crear método de pago</a></div></td></tr><?php else: foreach ($paymentMethods as $method): ?><tr><td><div class="record-primary"><?= h($method['nombre_metodo']) ?></div><div class="record-secondary">ID #<?= h($method['id_metodo_pago']) ?></div></td><td><?= h($method['descripcion'] ?: '—') ?></td><td><?= (int) $method['requiere_referencia'] === 1 ? 'Obligatoria' : 'No requerida' ?></td><td><span class="status-pill <?= (int) $method['activo'] === 1 ? 'status-active' : 'status-inactive' ?>"><span></span><?= (int) $method['activo'] === 1 ? 'Activo' : 'Inactivo' ?></span></td><td><div class="row-actions"><a class="text-action" href="?page=pagos&amp;tab=methods&amp;edit_method=<?= h($method['id_metodo_pago']) ?>">Editar</a><form method="post" action="?page=pagos&amp;tab=methods" onsubmit="return confirm('¿Eliminar este método? Solo se podrá si no está asociado a operaciones.');"><input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>"><input type="hidden" name="action" value="delete_method"><input type="hidden" name="id_metodo_pago" value="<?= h($method['id_metodo_pago']) ?>"><button class="text-action danger-action" type="submit">Eliminar</button></form></div></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
                <?php else: ?>
                    <section class="crud-card form-card"><div class="crud-card-heading"><div><p class="eyebrow">COBRO</p><h2>Registrar pago</h2><p>Selecciona un pedido con saldo pendiente. Los pagos registrados se conservan como historial financiero.</p></div></div>
                    <?php $activeMethods = array_values(array_filter($paymentMethods, static fn(array $m): bool => (int) ($m['activo'] ?? 0) === 1)); ?>
                    <?php if ($paymentOrders === []): ?><div class="notice notice-info" style="margin:0 23px 20px">No hay pedidos con saldo pendiente. Registra un pedido antes de cobrarlo.</div><?php elseif ($activeMethods === []): ?><div class="notice notice-info" style="margin:0 23px 20px">Primero configura un método de pago en <a class="text-action" href="?page=pagos&amp;tab=methods&amp;new_method=1">Métodos de pago</a>.</div><?php else: ?>
                    <form method="post" action="?page=pagos&amp;tab=payments" class="crud-form"><input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>"><input type="hidden" name="action" value="record_payment">
                        <div class="form-field"><label for="payment-order">Pedido pendiente <span class="required-mark">*</span></label><select id="payment-order" name="id_pedido" required><option value="">Selecciona un pedido</option><?php foreach ($paymentOrders as $order): ?><?php $balance = max(0, (float) $order['total'] - (float) $order['pagado']); ?><option value="<?= h($order['id_pedido']) ?>">Pedido #<?= h($order['id_pedido']) ?> · Pendiente <?= h(moneyLabel($balance)) ?></option><?php endforeach; ?></select></div>
                        <div class="form-field"><label for="payment-method">Método de pago <span class="required-mark">*</span></label><select id="payment-method" name="id_metodo_pago" required><option value="">Selecciona un método</option><?php foreach ($activeMethods as $method): ?><option value="<?= h($method['id_metodo_pago']) ?>" data-reference="<?= (int) $method['requiere_referencia'] ?>"><?= h($method['nombre_metodo']) ?></option><?php endforeach; ?></select></div>
                        <div class="form-field"><label for="payment-amount">Importe del pago (RD$) <span class="required-mark">*</span></label><input id="payment-amount" name="monto" type="number" min="0.01" step="0.01" required></div>
                        <div class="form-field"><label for="payment-received">Importe recibido (RD$) <span class="required-mark">*</span></label><input id="payment-received" name="monto_recibido" type="number" min="0.01" step="0.01" required></div>
                        <div class="form-field form-field-full"><label for="payment-reference">Referencia de transacción</label><input id="payment-reference" name="referencia_transaccion" maxlength="255"><small>Obligatoria solo para los métodos que la requieren.</small></div>
                        <div class="form-actions form-field-full"><button class="primary-button" type="submit">Registrar pago</button></div>
                    </form><?php endif; ?></section>
                    <section class="crud-card table-card" style="margin-top:18px"><div class="crud-card-heading"><div><h2>Pagos registrados</h2><p>Los movimientos de pago se conservan como historial y no se editan ni eliminan desde esta pantalla.</p></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Fecha</th><th>Pedido</th><th>Método</th><th>Importe</th><th>Recibido</th><th>Cambio</th><th>Referencia</th></tr></thead><tbody><?php if ($paymentRows === []): ?><tr><td colspan="7"><div class="table-empty"><strong>Todavía no hay pagos registrados</strong><p>Los pagos aparecerán aquí al registrar cobros de pedidos.</p></div></td></tr><?php else: foreach ($paymentRows as $pay): ?><tr><td><?= h($pay['fecha_pago'] ? date('d/m/Y H:i', strtotime($pay['fecha_pago'])) : '—') ?></td><td>Pedido #<?= h($pay['id_pedido']) ?></td><td><?= h($pay['nombre_metodo']) ?></td><td class="money-cell"><?= h(moneyLabel($pay['monto'])) ?></td><td class="money-cell"><?= h(moneyLabel($pay['monto_recibido'])) ?></td><td class="money-cell"><?= h(moneyLabel($pay['cambio'])) ?></td><td><?= h($pay['referencia_transaccion'] ?: '—') ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
                <?php endif; ?>

            <?php elseif ($page === 'reportes'): ?>
                <div class="page-heading"><div><p class="eyebrow">GESTIÓN DEL RESTAURANTE</p><h1>Reportes</h1><p class="page-subtitle">Consulta los pedidos, su distribución por estado y los productos con mayor movimiento.</p></div><a class="secondary-button" href="?page=dashboard">← Volver al panel</a></div>
                <?php if (is_array($flash)): ?><div class="notice notice-<?= h($flash['type'] ?? 'info') ?>" role="status"><?= h($flash['message'] ?? '') ?></div><?php endif; ?>
                <?php if ($dbError !== null): ?><div class="notice notice-error" role="alert"><?= h($dbError) ?></div><?php endif; ?>
                <section class="crud-card report-filter-card"><form class="report-filter-form" method="get" action=""><input type="hidden" name="page" value="reportes"><div class="form-field"><label for="report-from">Desde</label><input id="report-from" type="date" name="from" value="<?= h($reportFrom) ?>" required></div><div class="form-field"><label for="report-to">Hasta</label><input id="report-to" type="date" name="to" value="<?= h($reportTo) ?>" required></div><button class="primary-button" type="submit">Actualizar reporte</button></form></section>
                <div class="report-period-note">Periodo consultado: <?= h(moduleDate($reportFrom)) ?> — <?= h(moduleDate($reportTo)) ?>. Los importes corresponden al valor de pedidos no cancelados; no equivalen necesariamente a pagos cobrados.</div>
                <section class="report-kpi-grid" aria-label="Indicadores del periodo">
                    <article class="report-kpi"><span>Pedidos registrados</span><strong><?= h($reportSummary['orders']) ?></strong><small>Incluye todos los estados</small></article>
                    <article class="report-kpi"><span>Valor de pedidos no cancelados</span><strong><?= h(moneyLabel($reportSummary['order_value'])) ?></strong><small>Importe registrado, sin descontar pagos</small></article>
                    <article class="report-kpi"><span>Ticket promedio no cancelado</span><strong><?= h(moneyLabel($reportSummary['average'])) ?></strong><small>Promedio por pedido</small></article>
                    <article class="report-kpi"><span>Pedidos cancelados</span><strong><?= h($reportSummary['cancelled']) ?></strong><small>Dentro del periodo seleccionado</small></article>
                </section>
                <div class="report-grid-two">
                    <section class="crud-card"><div class="crud-card-heading"><div><h2>Pedidos por estado</h2><p>Conteo e importe según el estado actual.</p></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Estado</th><th>Pedidos</th><th>Importe</th></tr></thead><tbody><?php if ($reportStatuses === []): ?><tr><td colspan="3"><div class="table-empty"><strong>Sin datos para este periodo</strong><p>Cuando se registren pedidos, el resumen aparecerá aquí.</p></div></td></tr><?php else: foreach ($reportStatuses as $statusRow): ?><tr><td><span class="status-pill <?= h(moduleStatusClass((string) $statusRow['estado_actual'])) ?>"><span></span><?= h($statusRow['estado_actual']) ?></span></td><td><?= h($statusRow['cantidad']) ?></td><td class="money-cell"><?= h(moneyLabel($statusRow['importe'])) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
                    <section class="crud-card"><div class="crud-card-heading"><div><h2>Productos más pedidos</h2><p>Unidades registradas en pedidos no cancelados.</p></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Producto</th><th>Unidades</th><th>Importe</th></tr></thead><tbody><?php if ($reportProducts === []): ?><tr><td colspan="3"><div class="table-empty"><strong>Sin productos vendidos todavía</strong><p>El resumen se completará a medida que se registren pedidos con productos.</p></div></td></tr><?php else: foreach ($reportProducts as $productRow): ?><tr><td><div class="record-primary"><?= h($productRow['nombre']) ?></div></td><td><?= h($productRow['unidades']) ?></td><td class="money-cell"><?= h(moneyLabel($productRow['importe'])) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
                </div>
                <section class="crud-card table-card"><div class="crud-card-heading"><div><h2>Actividad diaria</h2><p>Últimos 14 días con pedidos dentro del periodo consultado.</p></div></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Fecha</th><th>Pedidos</th><th>Importe no cancelado</th></tr></thead><tbody><?php if ($reportDaily === []): ?><tr><td colspan="3"><div class="table-empty"><strong>No hay actividad registrada para estas fechas</strong><p>Prueba otro periodo o registra el primer pedido.</p></div></td></tr><?php else: foreach ($reportDaily as $dailyRow): ?><tr><td><?= h(moduleDate((string) $dailyRow['dia'])) ?></td><td><?= h($dailyRow['pedidos']) ?></td><td class="money-cell"><?= h(moneyLabel($dailyRow['importe'])) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>

            <?php else: ?>
                <div class="page-heading">
                    <div><p class="eyebrow">GESTIÓN DEL RESTAURANTE</p><h1><?= h($currentTitle) ?></h1><p class="page-subtitle"><?= h($pageDescriptions[$page] ?? 'Administra la información del restaurante.') ?></p></div>
                    <a class="secondary-button" href="?page=dashboard">← Volver al panel</a>
                </div>
                <section class="module-placeholder">
                    <div class="placeholder-icon"><?= h($navigation[array_search($page, array_column($navigation, 'page'))]['icon'] ?? '•') ?></div>
                    <h2><?= h($currentTitle) ?></h2>
                    <p><?= h($pageDescriptions[$page] ?? 'Administra la información del restaurante.') ?> Esta sección se incorporará al flujo funcional de Mesa360 en la siguiente etapa.</p>
                    <a class="secondary-button" href="?page=dashboard">Volver al panel de control</a>
                </section>
            <?php endif; ?>

            <footer class="page-footer"><span>© <?= date('Y') ?> Mesa360</span><span>Sistema de gestión para restaurantes</span></footer>
        </main>
    </div>
</div>
<script>
(() => {
    const form = document.getElementById('order-form');
    if (!form) return;
    const items = document.getElementById('order-items');
    const total = document.getElementById('order-total-preview');
    const type = document.getElementById('order-type');
    const tableField = document.getElementById('order-table-field');
    const tableSelect = document.getElementById('order-table');
    const addButton = document.getElementById('add-order-item');
    const templateRow = () => {
        const first = items.querySelector('.order-item-row');
        const row = first.cloneNode(true);
        row.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
        row.querySelectorAll('input').forEach(input => { input.value = input.classList.contains('order-quantity') ? '1' : ''; });
        return row;
    };
    const recalc = () => {
        let sum = 0;
        items.querySelectorAll('.order-item-row').forEach(row => {
            const select = row.querySelector('.order-product-select');
            const option = select?.selectedOptions[0];
            const price = Number(option?.dataset.price || 0);
            const quantity = Number(row.querySelector('.order-quantity')?.value || 0);
            if (Number.isFinite(price) && Number.isFinite(quantity) && quantity > 0) sum += price * quantity;
        });
        total.textContent = 'RD$ ' + sum.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        items.querySelectorAll('.remove-order-item').forEach(btn => btn.disabled = items.querySelectorAll('.order-item-row').length < 2);
    };
    const toggleTable = () => {
        const local = type.value === 'Local';
        tableField.style.display = local ? '' : 'none';
        tableSelect.required = local;
        if (!local) tableSelect.value = '';
    };
    items.addEventListener('change', recalc);
    items.addEventListener('input', recalc);
    items.addEventListener('click', event => {
        const button = event.target.closest('.remove-order-item');
        if (button && items.querySelectorAll('.order-item-row').length > 1) { button.closest('.order-item-row').remove(); recalc(); }
    });
    addButton?.addEventListener('click', () => { items.appendChild(templateRow()); recalc(); });
    type.addEventListener('change', toggleTable);
    toggleTable();
    recalc();
})();
</script>
</body>
</html>
