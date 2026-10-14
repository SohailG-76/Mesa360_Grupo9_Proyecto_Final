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

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

function loginH(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (!empty($_SESSION['mesa360_user_id'])) {
    header('Location: /?page=dashboard', true, 303);
    exit;
}

$_SESSION['mesa360_csrf_token'] ??= bin2hex(random_bytes(32));
$csrfToken = (string)$_SESSION['mesa360_csrf_token'];
$errorMessage = '';
$usernameInput = '';

$now = time();
if (!empty($_SESSION['mesa360_login_blocked_until']) && (int)$_SESSION['mesa360_login_blocked_until'] <= $now) {
    unset($_SESSION['mesa360_login_blocked_until'], $_SESSION['mesa360_login_failures']);
}
$blockedUntil = (int)($_SESSION['mesa360_login_blocked_until'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameInput = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $submittedToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if ($blockedUntil > $now) {
        $errorMessage = 'Se han realizado demasiados intentos. Espera unos minutos antes de intentarlo nuevamente.';
    } elseif (!hash_equals($csrfToken, $submittedToken)) {
        $errorMessage = 'La sesión del formulario ha caducado. Actualiza la página e inténtalo de nuevo.';
    } elseif ($usernameInput === '' || $password === '') {
        $errorMessage = 'Introduce tu usuario y contraseña.';
    } else {
        try {
            $configPath = '/etc/mesa360/database.php';
            if (!is_readable($configPath)) {
                throw new RuntimeException('Database configuration unavailable.');
            }
            $dbConfig = require $configPath;
            if (!is_array($dbConfig)) {
                throw new RuntimeException('Invalid database configuration.');
            }

            $dsn = 'mysql:host=' . $dbConfig['host']
                . ';dbname=' . $dbConfig['database']
                . ';charset=' . ($dbConfig['charset'] ?? 'utf8mb4');
            $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $stmt = $pdo->prepare(
                'SELECT u.id_usuario, u.nombre_usuario, u.contrasena, u.id_empleado, u.id_rol, u.activo,
                        u.intentos_fallidos, r.nombre_rol, e.activo AS empleado_activo
                   FROM USUARIO u
                   INNER JOIN ROL r ON r.id_rol = u.id_rol
                   LEFT JOIN EMPLEADO e ON e.id_empleado = u.id_empleado
                  WHERE u.nombre_usuario = :username
                  LIMIT 1'
            );
            $stmt->execute(['username' => $usernameInput]);
            $user = $stmt->fetch();

            $valid = is_array($user)
                && (int)$user['activo'] === 1
                && $user['id_empleado'] !== null
                && (int)($user['empleado_activo'] ?? 0) === 1
                && is_string($user['contrasena'])
                && password_verify($password, $user['contrasena']);

            if ($valid) {
                session_regenerate_id(true);
                $_SESSION['mesa360_user_id'] = (int)$user['id_usuario'];
                $_SESSION['mesa360_username'] = (string)$user['nombre_usuario'];
                $_SESSION['mesa360_user_role_id'] = (int)$user['id_rol'];
                $_SESSION['mesa360_user_role'] = (string)$user['nombre_rol'];
                $_SESSION['mesa360_csrf_token'] = bin2hex(random_bytes(32));
                unset($_SESSION['mesa360_login_failures'], $_SESSION['mesa360_login_blocked_until']);

                $update = $pdo->prepare(
                    'UPDATE USUARIO SET intentos_fallidos = 0, ultimo_acceso = NOW() WHERE id_usuario = :id'
                );
                $update->execute(['id' => (int)$user['id_usuario']]);

                header('Location: /?page=dashboard', true, 303);
                exit;
            }

            if (is_array($user)) {
                $failed = $pdo->prepare(
                    'UPDATE USUARIO SET intentos_fallidos = LEAST(COALESCE(intentos_fallidos, 0) + 1, 2147483647) WHERE id_usuario = :id'
                );
                $failed->execute(['id' => (int)$user['id_usuario']]);
            }

            $failures = (int)($_SESSION['mesa360_login_failures'] ?? 0) + 1;
            $_SESSION['mesa360_login_failures'] = $failures;
            if ($failures >= 5) {
                $_SESSION['mesa360_login_blocked_until'] = time() + 15 * 60;
                $blockedUntil = (int)$_SESSION['mesa360_login_blocked_until'];
                $errorMessage = 'Se han realizado demasiados intentos. Espera 15 minutos antes de intentarlo nuevamente.';
            } else {
                $errorMessage = 'Usuario o contraseña incorrectos, o cuenta inactiva.';
            }
        } catch (Throwable $exception) {
            error_log('[Mesa360] Login failed due to an internal error: ' . $exception->getMessage());
            $errorMessage = 'No se pudo iniciar sesión en este momento. Inténtalo nuevamente más tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#171c29">
    <title>Iniciar sesión | Mesa360</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body class="login-page">
    <main class="login-layout">
        <section class="login-brand-panel" aria-label="Mesa360">
            <div class="login-brand">
                <span class="login-brand-mark">M</span>
                <div><strong>Mesa360</strong><span>Gestión de restaurante</span></div>
            </div>
            <div class="login-brand-copy">
                <span class="login-eyebrow">CONTROL · ORGANIZACIÓN · SERVICIO</span>
                <h1>Todo tu restaurante, en un solo lugar.</h1>
                <p>Accede a las herramientas que tu equipo necesita para gestionar las operaciones diarias.</p>
            </div>
            <div class="login-brand-bottom"><span class="login-status-dot"></span> Gestión para personal autorizado</div>
            <div class="login-decor login-decor-one"></div>
            <div class="login-decor login-decor-two"></div>
        </section>

        <section class="login-form-panel">
            <div class="login-form-wrap">
                <div class="login-mobile-brand">
                    <span class="login-brand-mark">M</span>
                    <div><strong>Mesa360</strong><span>Gestión de restaurante</span></div>
                </div>
                <div class="login-heading">
                    <span class="login-eyebrow">BIENVENIDO DE NUEVO</span>
                    <h2>Iniciar sesión</h2>
                    <p>Introduce tus credenciales para continuar.</p>
                </div>

                <?php if ($errorMessage !== ''): ?>
                    <div class="login-alert" role="alert"><?= loginH($errorMessage) ?></div>
                <?php endif; ?>
                <?php if (isset($_GET['logged_out'])): ?>
                    <div class="login-notice" role="status">Has cerrado sesión correctamente.</div>
                <?php endif; ?>

                <form class="login-form" method="post" action="/login.php" autocomplete="on">
                    <input type="hidden" name="csrf_token" value="<?= loginH($csrfToken) ?>">
                    <label for="username">Usuario</label>
                    <input id="username" name="username" type="text" value="<?= loginH($usernameInput) ?>" autocomplete="username" maxlength="50" required autofocus>

                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>

                    <button type="submit" class="login-submit">Iniciar sesión <span aria-hidden="true">→</span></button>
                </form>
                <p class="login-private-note"><span aria-hidden="true">▣</span> Acceso exclusivo para el personal del restaurante.</p>
                <footer class="login-footer">© <?= date('Y') ?> Mesa360</footer>
            </div>
        </section>
    </main>
</body>
</html>
