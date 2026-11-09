<?php
declare(strict_types=1);

const TOPIC_TITLE_MAX_LENGTH = 255;
const TOPIC_AUTHOR_MAX_LENGTH = 100;
const TOPIC_SEARCH_MAX_LENGTH = 100;

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function require_csrf(bool $json = false): void
{
    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '';
    if (!is_string($provided) || !hash_equals(csrf_token(), $provided)) {
        if ($json) json_reply(false, null, 'Недійсний CSRF-токен.', 403);
        http_response_code(403);
        exit('Недійсний CSRF-токен.');
    }
}

function is_moderator(): bool
{
    return ($_SESSION['moderator'] ?? false) === true;
}

function require_moderator(bool $json = false): void
{
    if (!is_moderator()) {
        if ($json) json_reply(false, null, 'Доступ лише для модератора.', 403);
        http_response_code(403);
        exit('Доступ лише для модератора. <a href="moderator.php">Увійти</a>');
    }
}

function require_post(bool $json = false): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Allow: POST');
        if ($json) json_reply(false, null, 'Метод не підтримується.', 405);
        http_response_code(405);
        exit('Метод не підтримується.');
    }
}

function json_reply(bool $success, mixed $data = null, ?string $error = null, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    $payload = $success ? ['success' => true, 'data' => $data] : ['success' => false, 'error' => $error];
    $result = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    echo $result === false ? '{"success":false,"error":"Помилка формування JSON."}' : $result;
    exit;
}

function positive_id(mixed $value): int
{
    if ((!is_string($value) && !is_int($value)) || !preg_match('/^[1-9][0-9]*$/D', (string) $value)) {
        return 0;
    }
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 0;
}

function post_text(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function utf_length(string $text): int
{
    if (function_exists('mb_strlen')) return mb_strlen($text, 'UTF-8');
    $count = preg_match_all('/./us', $text);
    return $count === false ? 0 : $count;
}

function normalize_created_at(mixed $input): ?string
{
    if (!is_string($input)) return null;
    $value = trim($input);
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})[T ](\d{2}):(\d{2})(?::(\d{2}))?$/D', $value, $m)) {
        return null;
    }
    $year = (int) $m[1];
    $month = (int) $m[2];
    $day = (int) $m[3];
    $hour = (int) $m[4];
    $minute = (int) $m[5];
    $second = isset($m[6]) ? (int) $m[6] : 0;
    if ($year < 1000 || !checkdate($month, $day, $year)
        || $hour > 23 || $minute > 59 || $second > 59) {
        return null;
    }
    return sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $month, $day, $hour, $minute, $second);
}

function date_from_form(mixed $date): string
{
    if (!is_string($date)) return '';
    return normalize_created_at($date) ?? trim($date);
}

function validate_topic(string $title, string $author, string $createdAt): array
{
    $errors = [];
    if ($title === '' || preg_match('//u', $title) !== 1
        || utf_length($title) > TOPIC_TITLE_MAX_LENGTH) {
        $errors[] = 'Назва теми має містити від 1 до 255 символів UTF-8.';
    }
    if ($author === '' || preg_match('//u', $author) !== 1
        || utf_length($author) > TOPIC_AUTHOR_MAX_LENGTH) {
        $errors[] = 'Автор має містити від 1 до 100 символів UTF-8.';
    }
    if (normalize_created_at($createdAt) === null) {
        $errors[] = 'Некоректна дата або час створення.';
    }
    return $errors;
}

function topic_fields_from_input(array $input): array
{
    $errors = [];
    foreach (['title', 'author', 'created_at'] as $field) {
        if (!array_key_exists($field, $input) || !is_string($input[$field])) {
            $errors[] = 'Поле ' . $field . ' має бути рядком.';
        }
    }
    if ($errors) {
        return ['', '', '', $errors];
    }
    $title = trim($input['title']);
    $author = trim($input['author']);
    $createdAt = date_from_form($input['created_at']);
    return [$title, $author, $createdAt, validate_topic($title, $author, $createdAt)];
}

function search_author(mixed $value): ?string
{
    if (!is_string($value)) return null;
    $value = trim($value);
    if (preg_match('//u', $value) !== 1 || utf_length($value) > TOPIC_SEARCH_MAX_LENGTH) {
        return null;
    }
    return $value;
}

function invalidate_pinned_cache(): void
{
    require_once dirname(__DIR__) . '/profiling/cache.php';
    $cacheFile = pinned_cache_file();
    if (is_file($cacheFile)) {
        @unlink($cacheFile);
    }
}
