<?php

$GLOBALS['IS_JSON_API'] = true;
require_once __DIR__ . '/bootstrap.php';

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $resource = $_GET['resource'] ?? null;
    if (!is_string($resource)) {
        json_reply(false, null, 'Параметр resource має бути рядком.', 400);
    }
    if ($resource !== 'topics') {
        json_reply(false, null, 'Невідомий ресурс.', 404);
    }

    $action = $_GET['action'] ?? null;
    if ($action !== null && !is_string($action)) {
        json_reply(false, null, 'Параметр action має бути рядком.', 400);
    }
    $rawId = $_GET['id'] ?? null;
    if ($rawId !== null && !positive_id($rawId)) {
        json_reply(false, null, 'Некоректний id.', 400);
    }
    $id = $rawId === null ? null : positive_id($rawId);

    if ($method === 'GET') {
        if ($action !== null) {
            header('Allow: POST');
            json_reply(false, null, 'Метод не підтримується для цієї дії.', 405);
        }
        $q = search_author($_GET['q'] ?? '');
        if ($q === null) {
            json_reply(false, null, 'Параметр q має бути рядком UTF-8 до 100 символів.', 400);
        }
        require_once __DIR__ . '/db.php';
        require_once __DIR__ . '/lib/TopicRepository.php';
        if ($id !== null) {
            $topic = get_topic($pdo, $id);
            if (!$topic) json_reply(false, null, 'Тему не знайдено.', 404);
            json_reply(true, $topic);
        }
        json_reply(true, list_topics($pdo, $q));
    }

    if ($method !== 'POST') {
        header('Allow: GET, POST');
        json_reply(false, null, 'Метод не підтримується.', 405);
    }

    require_csrf(true);

    if ($action === 'pin') {
        require_moderator(true);
        if ($id === null) json_reply(false, null, 'Необхідно передати id.', 400);
        require_once __DIR__ . '/db.php';
        require_once __DIR__ . '/lib/TopicRepository.php';
        $topic = pin_topic($pdo, $id);
        if (!$topic) json_reply(false, null, 'Тему не знайдено.', 404);
        json_reply(true, $topic);
    }

    if ($action !== null || $rawId !== null) {
        json_reply(false, null, 'Невідомий маршрут.', 404);
    }

    $raw = file_get_contents('php://input');
    if (!is_string($raw) || trim($raw) === '' || strlen($raw) > 65536) {
        json_reply(false, null, 'Необхідно передати JSON-об’єкт (до 64 КБ).', 400);
    }
    try {
        $input = json_decode($raw, false, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        json_reply(false, null, 'Некоректний формат JSON.', 400);
    }
    if (!$input instanceof stdClass) {
        json_reply(false, null, 'Очікується JSON-об’єкт із полями теми.', 400);
    }
    [$title, $author, $createdAt, $errors] = topic_fields_from_input((array) $input);
    if ($errors) {
        json_reply(false, null, implode(' ', $errors), 400);
    }

    $pin = $input->is_pinned ?? 0;
    if (!in_array($pin, [0, '0', false, 1, '1', true], true)) {
        json_reply(false, null, 'Поле is_pinned повинно мати значення 0 або 1.', 400);
    }
    if (!in_array($pin, [0, '0', false], true)) {
        json_reply(false, null, 'Закріплення доступне лише через дію pin модератору.', 403);
    }

    require_once __DIR__ . '/db.php';
    require_once __DIR__ . '/lib/TopicRepository.php';
    json_reply(true, add_topic($pdo, $title, $author, $createdAt), null, 201);
} catch (Throwable $exception) {
    error_log('[lab8/api.php] ' . $exception);
    json_reply(false, null, 'Внутрішня помилка сервера або бази даних.', 500);
}
