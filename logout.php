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
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /login.php', true, 303);
    exit;
}

$submittedToken = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
$sessionToken = isset($_SESSION['mesa360_csrf_token']) && is_string($_SESSION['mesa360_csrf_token'])
    ? $_SESSION['mesa360_csrf_token'] : '';

if ($sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
    http_response_code(400);
    echo 'Solicitud no válida. Actualiza la página e inténtalo de nuevo.';
    exit;
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}
session_destroy();
header('Location: /login.php?logged_out=1', true, 303);
exit;
