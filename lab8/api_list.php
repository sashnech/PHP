<?php
$GLOBALS['IS_JSON_API'] = true;
require_once __DIR__ . '/bootstrap.php';

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        header('Allow: GET');
        json_reply(false, null, 'Метод не підтримується.', 405);
    }
    $q = search_author($_GET['q'] ?? '');
    if ($q === null) {
        json_reply(false, null, 'Параметр q має бути рядком UTF-8 до 100 символів.', 400);
    }

    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/lib/TopicRepository.php';
    $topics = list_topics($pdo, $q);

    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($topics, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $exception) {
    error_log('[lab8/api_list.php] ' . $exception);
    json_reply(false, null, 'Помилка бази даних. Спробуйте пізніше.', 500);
}
