<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (function_exists('posix_geteuid') && posix_geteuid() !== 0) {
    fwrite(STDERR, "Run this script with sudo so it can use MySQL root through the local socket.\n");
    exit(1);
}

// Ubuntu's MySQL root account uses auth_socket. Running this CLI utility with sudo
// lets it provision the first user without granting user/role creation privileges to
// the web application's restricted mesa360_app account.
$dsn = 'mysql:host=localhost;dbname=mesa360_database;charset=utf8mb4';
$pdo = new PDO($dsn, 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

function promptLine(string $prompt): string
{
    fwrite(STDOUT, $prompt);
    $value = fgets(STDIN);
    return $value === false ? '' : trim($value);
}

function promptHidden(string $prompt): string
{
    fwrite(STDOUT, $prompt);
    $isTty = function_exists('posix_isatty') ? @posix_isatty(STDIN) : true;
    if ($isTty) {
        @shell_exec('stty -echo');
    }
    $value = fgets(STDIN);
    if ($isTty) {
        @shell_exec('stty echo');
    }
    fwrite(STDOUT, PHP_EOL);
    return $value === false ? '' : rtrim($value, "\r\n");
}

$counts = $pdo->query('SELECT (SELECT COUNT(*) FROM USUARIO) AS usuarios, (SELECT COUNT(*) FROM ROL) AS roles, (SELECT COUNT(*) FROM EMPLEADO) AS empleados')->fetch();
echo "Mesa360 administrator account setup\n";
echo "Current records: USUARIO=" . (int)$counts['usuarios'] . ", ROL=" . (int)$counts['roles'] . ", EMPLEADO=" . (int)$counts['empleados'] . "\n";
echo "This creates one employee account; it does not delete or change existing accounts.\n\n";

$username = promptLine('New administrator username (letters/numbers/._-): ');
if (!preg_match('/\A[A-Za-z0-9._-]{3,50}\z/', $username)) {
    fwrite(STDERR, "Username must contain 3-50 characters and use only letters, numbers, dot, underscore, or hyphen.\n");
    exit(1);
}

$check = $pdo->prepare('SELECT id_usuario FROM USUARIO WHERE nombre_usuario = :username LIMIT 1');
$check->execute(['username' => $username]);
if ($check->fetch()) {
    fwrite(STDERR, "That username already exists. Choose another username; no records were changed.\n");
    exit(1);
}

$password = promptHidden('New password (minimum 12 characters): ');
$confirm = promptHidden('Confirm password: ');
if (strlen($password) < 12) {
    fwrite(STDERR, "Password must contain at least 12 characters. No records were changed.\n");
    exit(1);
}
if (!hash_equals($password, $confirm)) {
    fwrite(STDERR, "Passwords do not match. No records were changed.\n");
    exit(1);
}
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
if (!is_string($passwordHash)) {
    fwrite(STDERR, "Could not hash the password. No records were changed.\n");
    exit(1);
}
unset($password, $confirm);

try {
    $pdo->beginTransaction();

    $roleLookup = $pdo->prepare('SELECT id_rol FROM ROL WHERE nombre_rol = :role ORDER BY id_rol LIMIT 1');
    $roleLookup->execute(['role' => 'Administrador']);
    $role = $roleLookup->fetch();
    if ($role) {
        $roleId = (int)$role['id_rol'];
    } else {
        $roleInsert = $pdo->prepare('INSERT INTO ROL (nombre_rol, descripcion) VALUES (:name, :description)');
        $roleInsert->execute([
            'name' => 'Administrador',
            'description' => 'Acceso administrativo al sistema de gestión Mesa360.',
        ]);
        $roleId = (int)$pdo->lastInsertId();
    }

    $employeeInsert = $pdo->prepare(
        'INSERT INTO EMPLEADO (cedula, nombre, telefono, correo, cargo, fecha_contratacion, fecha_nacimiento, direccion, activo)
         VALUES (NULL, :name, NULL, NULL, :position, CURDATE(), NULL, NULL, 1)'
    );
    $employeeInsert->execute([
        'name' => 'Administrador de Mesa360',
        'position' => 'Administrador',
    ]);
    $employeeId = (int)$pdo->lastInsertId();

    $userInsert = $pdo->prepare(
        'INSERT INTO USUARIO (nombre_usuario, contrasena, id_empleado, id_rol, activo, fecha_creacion, ultimo_acceso, intentos_fallidos)
         VALUES (:username, :password_hash, :employee_id, :role_id, 1, NOW(), NULL, 0)'
    );
    $userInsert->execute([
        'username' => $username,
        'password_hash' => $passwordHash,
        'employee_id' => $employeeId,
        'role_id' => $roleId,
    ]);
    $pdo->commit();

    echo "Administrator account created successfully.\n";
    echo "Username: {$username}\n";
    echo "Role: Administrador\n";
    echo "The password was stored as a password hash and was not displayed.\n";
    echo "Keep the password safe. It cannot be retrieved from the database.\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Could not create the administrator account: " . $exception->getMessage() . "\n");
    exit(1);
}
