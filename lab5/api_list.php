<?php

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

function jsonError(string $message, int $status): void
{
    http_response_code($status);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    jsonError('Метод не підтримується.', 405);
}

$rawQuery = $_GET['q'] ?? '';
if (!is_string($rawQuery)) {
    jsonError('Параметр q має бути рядком.', 400);
}

$q = trim($rawQuery);
if (preg_match('//u', $q) !== 1) {
    jsonError('Параметр q має містити коректний текст UTF-8.', 400);
}

$queryLength = function_exists('mb_strlen')
    ? mb_strlen($q, 'UTF-8')
    : preg_match_all('/./us', $q);

if ($queryLength > 100) {
    jsonError('Параметр пошуку занадто довгий (максимум 100 символів).', 400);
}

try {
    require_once __DIR__ . '/db.php';

    if ($q === '') {
        $stmt = $pdo->query(
            'SELECT id, title, author, created_at, is_pinned
             FROM topics
             ORDER BY id DESC'
        );
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, title, author, created_at, is_pinned
             FROM topics
             WHERE author LIKE :author
             ORDER BY id DESC'
        );
        $stmt->execute([':author' => '%' . $q . '%']);
    }

    echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    error_log('[lab5/api_list.php] ' . $e);
    jsonError('Помилка бази даних. Спробуйте пізніше.', 500);
}
