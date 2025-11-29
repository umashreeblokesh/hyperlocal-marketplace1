<?php
// backend/inc/init.php
// Global CORS & DB initializer. Include this file in every endpoint.

// ====== FRONTEND ORIGIN (GitHub Pages) ======
$FRONTEND_ORIGIN = 'https://umashreeblokesh.github.io';

// ====== CORS HANDLING ======
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && $origin === $FRONTEND_ORIGIN) {
    header("Access-Control-Allow-Origin: $origin");
    header("Vary: Origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    // For debugging only, you could use: header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ====== ENV helper ======
function env($k, $d = null) {
    $v = getenv($k);
    return ($v === false) ? $d : $v;
}

// ====== SESSION SETTINGS (optional, supports cross-site cookies) ======
$secure = str_starts_with(env('BASE_URL', ''), 'https://');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'None'
]);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ====== DB CONNECTION ======
$DB = new mysqli(
    env('DB_HOST','127.0.0.1'),
    env('DB_USER','root'),
    env('DB_PASS',''),
    env('DB_NAME','hyperlocal'),
    (int)env('DB_PORT',3306)
);

if ($DB->connect_errno) {
    error_log("DB connect failed: ({$DB->connect_errno}) {$DB->connect_error}");
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Database connection failed']);
    exit;
}
$DB->set_charset('utf8mb4');

// ====== JSON HELPERS ======
function json_ok($data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['ok'=>true], $data));
    exit;
}
function json_err($msg='error', $code=400) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($code);
    echo json_encode(['ok'=>false,'error'=>$msg]);
    exit;
}
