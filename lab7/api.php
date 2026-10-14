<?php


ini_set('display_errors', '0');
ini_set('html_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/cache.php';

function jsonError(string $message, int $status): void
{
    http_response_code($status);
    echo json_encode(['success' => false, 'error' => $message],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function respondProfile(array $data, float $start, int $sqlQueries, ?array $cacheInfo = null): void
{
    $profile = [
        'execution_ms' => round((microtime(true) - $start) * 1000, 2),
        'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        'sql_queries' => $sqlQueries
    ];
    if ($cacheInfo !== null) {
        $profile['pinned_cache'] = $cacheInfo;
    }

    echo json_encode([
        'success' => true,
        'data' => $data,
        'profile' => $profile
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function listTopicsBefore(PDO $pdo, int &$sqlQueries): array
{
    $sqlQueries++;
    $topics = $pdo->query(
        'SELECT id, title, author, created_at, is_pinned FROM topics ORDER BY id DESC'
    )->fetchAll();

    foreach ($topics as &$topic) {
        $sqlQueries++;
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM topics WHERE author = :author');
        $stmt->execute([':author' => $topic['author']]);
        $topic['author_topic_count'] = (int) $stmt->fetchColumn();
    }
    unset($topic);
    return $topics;
}

function listTopicsAfter(PDO $pdo, int &$sqlQueries): array
{
    $sqlQueries++;
    $topics = $pdo->query(
        'SELECT id, title, author, created_at, is_pinned FROM topics ORDER BY id DESC'
    )->fetchAll();

    $sqlQueries++;
    $counts = $pdo->query(
        'SELECT author, COUNT(*) AS cnt FROM topics GROUP BY author'
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    foreach ($topics as &$topic) {
        $topic['author_topic_count'] = (int) ($counts[$topic['author']] ?? 0);
    }
    unset($topic);
    return $topics;
}

$start = microtime(true);
$sqlQueries = 0;

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        header('Allow: GET');
        jsonError('Метод не підтримується.', 405);
    }

    $resource = $_GET['resource'] ?? null;
    if ($resource !== 'topics') {
        jsonError('Невідомий ресурс.', 404);
    }

    $mode = $_GET['mode'] ?? 'after';
    if (!is_string($mode) || !in_array($mode, ['before', 'after'], true)) {
        jsonError('Параметр mode має бути before або after.', 400);
    }

    require __DIR__ . '/db.php';
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new RuntimeException('Відсутнє підключення PDO.');
    }

    if ($mode === 'before') {
        respondProfile(listTopicsBefore($pdo, $sqlQueries), $start, $sqlQueries);
    }

    $topics = listTopicsAfter($pdo, $sqlQueries);
    $cacheInfo = [];
    $pinned = listPinnedCached($pdo, $sqlQueries, $cacheInfo);
    respondProfile([
        'topics' => $topics,
        'pinned_topics' => $pinned
    ], $start, $sqlQueries, $cacheInfo);
} catch (Throwable $e) {
    error_log('[lab7/api.php] ' . $e);
    jsonError('Помилка сервера або бази даних. Перевірте імпорт schema.sql.', 500);
}
