<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

require_once __DIR__ . '/lib/security.php';

set_exception_handler(function (Throwable $exception): void {
    error_log((string) $exception);
    http_response_code(500);
    if (!empty($GLOBALS['IS_JSON_API'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => 'Внутрішня помилка сервера.'], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="uk"><meta charset="utf-8"><title>Помилка</title><body><h1>Помилка сервера</h1><p>Спробуйте пізніше.</p></body></html>';
    }
});
