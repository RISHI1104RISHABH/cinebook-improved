<?php
// ─── Database ─────────────────────────────────────────────────────────────────
// Railway MySQL plugin injects MYSQLHOST, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE, MYSQLPORT
// Falls back to manual DB_* env vars, then localhost defaults for local dev.
define('DB_HOST', getenv('MYSQLHOST')     ?: getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', (int)(getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306));
define('DB_USER', getenv('MYSQLUSER')     ?: getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'movie_booking');

// ─── Site ─────────────────────────────────────────────────────────────────────
define('SITE_NAME', getenv('SITE_NAME') ?: 'CineBook');

// Dynamic SITE_URL — works on localhost, Railway, or any custom domain.
if (getenv('SITE_URL')) {
    define('SITE_URL', rtrim(getenv('SITE_URL'), '/'));
} else {
    $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
             (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
             ? 'https' : 'http';
    $host  = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('SITE_URL', $proto . '://' . $host);
}

// ─── Database connection ───────────────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($conn->connect_error) {
    http_response_code(503);
    echo '<!DOCTYPE html><html><head><title>Database Error</title>
    <style>body{font-family:sans-serif;background:#0f0f1a;color:#f5f5f7;display:flex;
    align-items:center;justify-content:center;height:100vh;margin:0;}
    .box{background:#1a1a2e;padding:40px;border-radius:12px;max-width:500px;text-align:center;}
    h2{color:#e50914;margin-bottom:12px;}p{color:#9aa0b4;}</style></head>
    <body><div class="box"><h2>&#9888;&#65039; Database Unavailable</h2>
    <p>Cannot connect to the database. Please check your configuration or try again shortly.</p></div></body></html>';
    exit;
}
$conn->set_charset('utf8mb4');

// ─── Session ──────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path'     => '/',
        'secure'   => str_starts_with(SITE_URL, 'https'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
