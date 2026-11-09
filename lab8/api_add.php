<?php
$GLOBALS['IS_JSON_API'] = true;
require_once __DIR__ . '/bootstrap.php';

try {
    require_post(true);
    require_csrf(true);

    [$title, $author, $createdAt, $errors] = topic_fields_from_input($_POST);
    if ($errors) {
        json_reply(false, null, implode(' ', $errors), 400);
    }

    $pin = $_POST['is_pinned'] ?? '0';
    if (!is_string($pin) || !in_array($pin, ['0', '1', 'on'], true)) {
        json_reply(false, null, 'Поле is_pinned має бути рядком зі значенням 0 або 1.', 400);
    }
    if ($pin !== '0') {
        json_reply(false, null, 'Закріплення доступне лише модератору.', 403);
    }

    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/lib/TopicRepository.php';
    $topic = add_topic($pdo, $title, $author, $createdAt);

    http_response_code(201);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($topic, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $exception) {
    error_log('[lab8/api_add.php] ' . $exception);
    json_reply(false, null, 'Помилка бази даних. Спробуйте пізніше.', 500);
}
