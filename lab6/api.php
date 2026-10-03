<?php

/*

1. GET /api.php?resource=topics
   Повертає список усіх тем.

2. GET /api.php?resource=topics&id=1
   Повертає одну тему за id або 404, якщо запис не знайдено.

3. POST /api.php?resource=topics
   Створює нову тему.
   JSON-тіло:
   {
       "title": "Назва теми",
       "author": "Автор",
       "created_at": "2026-10-03 22:00:00",
       "is_pinned": 0
   }

4. POST /api.php?resource=topics&action=pin&id=1
   Закріплює тему: is_pinned = 1.
*/

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['resource'] ?? null;
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$action = $_GET['action'] ?? null;

function respond(bool $success, $data = null, ?string $error = null, int $status = 200): void
{
    http_response_code($status);

    if ($success) {
        echo json_encode([
            'success' => true,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $error
        ], JSON_UNESCAPED_UNICODE);
    }

    exit;
}

function getJsonBody(): array
{
    $data = json_decode(file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

function listTopics(PDO $pdo): void
{
    $stmt = $pdo->query(
        'SELECT id, title, author, created_at, is_pinned
         FROM topics
         ORDER BY id DESC'
    );

    respond(true, $stmt->fetchAll());
}

function getTopic(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare(
        'SELECT id, title, author, created_at, is_pinned
         FROM topics
         WHERE id = :id'
    );

    $stmt->execute([
        ':id' => $id
    ]);

    $topic = $stmt->fetch();

    if (!$topic) {
        respond(false, null, 'Тему не знайдено.', 404);
    }

    respond(true, $topic);
}

function createTopic(PDO $pdo): void
{
    $data = getJsonBody();

    $title = trim($data['title'] ?? '');
    $author = trim($data['author'] ?? '');
    $createdAt = trim($data['created_at'] ?? '');
    $isPinned = isset($data['is_pinned']) ? (int) $data['is_pinned'] : 0;

    if ($title === '') {
        respond(false, null, 'Поле title є обов’язковим.', 400);
    }

    if ($author === '') {
        respond(false, null, 'Поле author є обов’язковим.', 400);
    }

    if ($createdAt === '') {
        respond(false, null, 'Поле created_at є обов’язковим.', 400);
    }

    if ($isPinned !== 0 && $isPinned !== 1) {
        respond(false, null, 'Поле is_pinned повинно мати значення 0 або 1.', 400);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO topics (title, author, created_at, is_pinned)
         VALUES (:title, :author, :created_at, :is_pinned)'
    );

    $stmt->execute([
        ':title' => $title,
        ':author' => $author,
        ':created_at' => $createdAt,
        ':is_pinned' => $isPinned
    ]);

    $id = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'SELECT id, title, author, created_at, is_pinned
         FROM topics
         WHERE id = :id'
    );

    $stmt->execute([
        ':id' => $id
    ]);

    respond(true, $stmt->fetch(), null, 201);
}

function pinTopic(PDO $pdo, int $id): void
{
    if ($id <= 0) {
        respond(false, null, 'Необхідно передати коректний id.', 400);
    }

    $stmt = $pdo->prepare(
        'SELECT id
         FROM topics
         WHERE id = :id'
    );

    $stmt->execute([
        ':id' => $id
    ]);

    if (!$stmt->fetch()) {
        respond(false, null, 'Тему не знайдено.', 404);
    }

    $stmt = $pdo->prepare(
        'UPDATE topics
         SET is_pinned = 1
         WHERE id = :id'
    );

    $stmt->execute([
        ':id' => $id
    ]);

    $stmt = $pdo->prepare(
        'SELECT id, title, author, created_at, is_pinned
         FROM topics
         WHERE id = :id'
    );

    $stmt->execute([
        ':id' => $id
    ]);

    respond(true, $stmt->fetch());
}

if ($resource !== 'topics') {
    respond(false, null, 'Невідомий ресурс.', 404);
}

if ($method === 'GET') {
    if ($action !== null) {
        respond(false, null, 'Метод не підтримується для цієї дії.', 405);
    }

    if ($id === null) {
        listTopics($pdo);
    }

    if ($id <= 0) {
        respond(false, null, 'Необхідно передати коректний id.', 400);
    }

    getTopic($pdo, $id);
}

if ($method === 'POST') {
    if ($action === null) {
        createTopic($pdo);
    }

    if ($action === 'pin') {
        pinTopic($pdo, $id ?? 0);
    }

    respond(false, null, 'Невідома дія.', 404);
}

respond(false, null, 'Метод не підтримується.', 405);
