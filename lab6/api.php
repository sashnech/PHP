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

ini_set('display_errors', '0');
ini_set('html_errors', '0');
header('Content-Type: application/json; charset=utf-8');

function respond(bool $success, $data = null, ?string $error = null, int $status = 200): void
{
    http_response_code($status);
    $payload = $success
        ? ['success' => true, 'data' => $data]
        : ['success' => false, 'error' => $error];

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        error_log('[lab6/api.php] Не вдалося сформувати JSON-відповідь.');
        http_response_code(500);
        echo '{"success":false,"error":"Внутрішня помилка сервера."}';
    } else {
        echo $json;
    }
    exit;
}

function readJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        respond(false, null, 'Необхідно передати JSON-об’єкт.', 400);
    }

    try {
        $json = json_decode($raw, false, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        respond(false, null, 'Некоректний формат JSON.', 400);
    }

    if (!$json instanceof stdClass) {
        respond(false, null, 'Очікується JSON-об’єкт із полями теми.', 400);
    }

    return (array) $json;
}

function textLength(string $value): int
{
    return preg_match_all('/./us', $value);
}

function requiredText(array $data, string $name, int $maxLength): string
{
    if (!array_key_exists($name, $data) || !is_string($data[$name])) {
        respond(false, null, "Поле {$name} має бути непорожнім рядком.", 400);
    }

    $value = trim($data[$name]);
    if ($value === '') {
        respond(false, null, "Поле {$name} є обов’язковим.", 400);
    }
    if (preg_match('//u', $value) !== 1) {
        respond(false, null, "Поле {$name} має містити коректний UTF-8 текст.", 400);
    }
    if (textLength($value) > $maxLength) {
        respond(false, null, "Поле {$name} не може перевищувати {$maxLength} символів.", 400);
    }

    return $value;
}

function validCreatedAt(array $data): string
{
    if (!array_key_exists('created_at', $data) || !is_string($data['created_at'])) {
        respond(false, null, 'Поле created_at має бути рядком із датою.', 400);
    }

    $value = trim($data['created_at']);
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?$/D', $value, $m)) {
        respond(false, null, 'Некоректна дата created_at. Очікується YYYY-MM-DD HH:MM:SS.', 400);
    }

    $year = (int) $m[1];
    $month = (int) $m[2];
    $day = (int) $m[3];
    $hour = (int) $m[4];
    $minute = (int) $m[5];
    $second = isset($m[6]) ? (int) $m[6] : 0;

    if ($year < 1000 || !checkdate($month, $day, $year)
        || $hour > 23 || $minute > 59 || $second > 59) {
        respond(false, null, 'Поле created_at містить неіснуючу дату або час.', 400);
    }

    return sprintf('%04d-%02d-%02d %02d:%02d:%02d',
        $year, $month, $day, $hour, $minute, $second);
}

function validIsPinned(array $data): int
{
    if (!array_key_exists('is_pinned', $data)) {
        return 0;
    }
    $value = $data['is_pinned'];
    if ($value === 0 || $value === '0') {
        return 0;
    }
    if ($value === 1 || $value === '1') {
        return 1;
    }

    respond(false, null, 'Поле is_pinned повинно мати значення 0 або 1.', 400);
}

function validId($raw): int
{
    if (!is_string($raw) || !ctype_digit($raw)) {
        respond(false, null, 'Необхідно передати коректний id.', 400);
    }
    $id = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        respond(false, null, 'Необхідно передати коректний id.', 400);
    }
    return $id;
}

function loadPdo(): PDO
{
    require __DIR__ . '/db.php';
    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException('Змінна $pdo не ініціалізована.');
    }
    return $pdo;
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
         FROM topics WHERE id = :id'
    );
    $stmt->execute([':id' => $id]);
    $topic = $stmt->fetch();

    if (!$topic) {
        respond(false, null, 'Тему не знайдено.', 404);
    }
    respond(true, $topic);
}

function createTopic(PDO $pdo, string $title, string $author, string $createdAt, int $isPinned): void
{
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
         FROM topics WHERE id = :id'
    );
    $stmt->execute([':id' => $id]);
    respond(true, $stmt->fetch(), null, 201);
}

function pinTopic(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('SELECT id FROM topics WHERE id = :id');
    $stmt->execute([':id' => $id]);
    if (!$stmt->fetch()) {
        respond(false, null, 'Тему не знайдено.', 404);
    }

    $stmt = $pdo->prepare('UPDATE topics SET is_pinned = 1 WHERE id = :id');
    $stmt->execute([':id' => $id]);

    $stmt = $pdo->prepare(
        'SELECT id, title, author, created_at, is_pinned
         FROM topics WHERE id = :id'
    );
    $stmt->execute([':id' => $id]);
    respond(true, $stmt->fetch());
}

try {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $resource = $_GET['resource'] ?? null;
    $action = $_GET['action'] ?? null;

    if (!is_string($resource) || $resource !== 'topics') {
        respond(false, null, 'Невідомий ресурс.', 404);
    }
    if ($action !== null && !is_string($action)) {
        respond(false, null, 'Параметр action має бути рядком.', 400);
    }

    if ($method === 'GET') {
        if ($action !== null) {
            header('Allow: POST');
            respond(false, null, 'Метод не підтримується для цієї дії.', 405);
        }
        $id = array_key_exists('id', $_GET) ? validId($_GET['id']) : null;
        $pdo = loadPdo();
        if ($id === null) {
            listTopics($pdo);
        }
        getTopic($pdo, $id);
    }

    if ($method === 'POST') {
        if ($action === null) {
            $data = readJsonBody();
            $title = requiredText($data, 'title', 255);
            $author = requiredText($data, 'author', 100);
            $createdAt = validCreatedAt($data);
            $isPinned = validIsPinned($data);
            createTopic(loadPdo(), $title, $author, $createdAt, $isPinned);
        }

        if ($action === 'pin') {
            if (!array_key_exists('id', $_GET)) {
                respond(false, null, 'Необхідно передати коректний id.', 400);
            }
            $id = validId($_GET['id']);
            pinTopic(loadPdo(), $id);
        }
        respond(false, null, 'Невідома дія.', 404);
    }

    header('Allow: GET, POST');
    respond(false, null, 'Метод не підтримується.', 405);
} catch (Throwable $e) {
    error_log('[lab6/api.php] ' . $e);
    respond(false, null, 'Внутрішня помилка сервера або бази даних.', 500);
}
