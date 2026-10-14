<?php

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

function jsonError(string $message, int $status): void
{
    http_response_code($status);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

function textLength(string $value): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : preg_match_all('/./us', $value);
}

function validDateTime(string $value): ?string
{

    if (!preg_match(
        '/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}))?$/D',
        $value,
        $parts
    )) {
        return null;
    }

    $year = (int) $parts[1];
    $month = (int) $parts[2];
    $day = (int) $parts[3];
    $hour = (int) $parts[4];
    $minute = (int) $parts[5];
    $second = isset($parts[6]) ? (int) $parts[6] : 0;

    if ($year < 1000 || !checkdate($month, $day, $year)
        || $hour > 23 || $minute > 59 || $second > 59) {
        return null;
    }

    return sprintf('%04d-%02d-%02d %02d:%02d:%02d',
        $year, $month, $day, $hour, $minute, $second);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    jsonError('Метод не підтримується.', 405);
}

foreach (['title', 'author', 'created_at'] as $field) {
    if (isset($_POST[$field]) && !is_string($_POST[$field])) {
        jsonError('Поле ' . $field . ' має бути рядком.', 400);
    }
}

$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$createdAtInput = trim($_POST['created_at'] ?? '');

if ($title === '' || $author === '' || $createdAtInput === '') {
    jsonError('Заповніть усі обов’язкові поля.', 400);
}

if (preg_match('//u', $title) !== 1 || preg_match('//u', $author) !== 1) {
    jsonError('Текст має бути у коректному кодуванні UTF-8.', 400);
}

if (textLength($title) > 255) {
    jsonError('Назва теми не може перевищувати 255 символів.', 400);
}

if (textLength($author) > 100) {
    jsonError('Ім’я автора не може перевищувати 100 символів.', 400);
}

$createdAt = validDateTime($createdAtInput);
if ($createdAt === null) {
    jsonError('Некоректна дата або час створення.', 400);
}

$isPinnedInput = $_POST['is_pinned'] ?? '0';
if (!is_string($isPinnedInput) || !in_array($isPinnedInput, ['0', '1', 'on'], true)) {
    jsonError('Поле is_pinned повинно бути 0 або 1.', 400);
}
$isPinned = ($isPinnedInput === '1' || $isPinnedInput === 'on') ? 1 : 0;

try {
    require_once __DIR__ . '/db.php';

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
    $stmt->execute([':id' => $id]);

    http_response_code(201);
    echo json_encode($stmt->fetch(), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $e) {
    error_log('[lab5/api_add.php] ' . $e);
    jsonError('Помилка бази даних. Спробуйте пізніше.', 500);
}
