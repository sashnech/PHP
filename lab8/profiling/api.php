<?php

$GLOBALS['IS_JSON_API'] = true;
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/cache.php';

function listTopicsBefore(PDO $pdo, int &$sqlQueries): array
{
    $sqlQueries++;
    $topics = $pdo->query('SELECT id, title, author, created_at, is_pinned FROM topics ORDER BY id DESC')->fetchAll();
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
    $topics = $pdo->query('SELECT id, title, author, created_at, is_pinned FROM topics ORDER BY id DESC')->fetchAll();
    $sqlQueries++;
    $counts = $pdo->query('SELECT author, COUNT(*) AS cnt FROM topics GROUP BY author')->fetchAll(PDO::FETCH_KEY_PAIR);
    foreach ($topics as &$topic) {
        $topic['author_topic_count'] = (int) ($counts[$topic['author']] ?? 0);
    }
    unset($topic);
    return $topics;
}

$start = microtime(true);
$sqlQueries = 0;
try {
    require_moderator(true);
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        header('Allow: GET');
        json_reply(false, null, 'Метод не підтримується.', 405);
    }
    if (($_GET['resource'] ?? null) !== 'topics') {
        json_reply(false, null, 'Невідомий ресурс.', 404);
    }
    $mode = $_GET['mode'] ?? 'after';
    if (!is_string($mode) || !in_array($mode, ['before', 'after'], true)) {
        json_reply(false, null, 'mode має бути before або after.', 400);
    }

    require_once __DIR__ . '/../db.php';
    if ($mode === 'before') {
        $data = listTopicsBefore($pdo, $sqlQueries);
        $cacheInfo = null;
    } else {
        $topics = listTopicsAfter($pdo, $sqlQueries);
        $cacheInfo = [];
        $pinned = listPinnedCached($pdo, $sqlQueries, $cacheInfo);
        $data = ['topics' => $topics, 'pinned_topics' => $pinned];
    }

    $profile = [
        'execution_ms' => round((microtime(true) - $start) * 1000, 2),
        'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        'sql_queries' => $sqlQueries,
    ];
    if ($cacheInfo !== null) {
        $profile['pinned_cache'] = $cacheInfo;
    }
    json_profile_reply($data, $profile);
} catch (Throwable $exception) {
    error_log('[lab8/profiling/api.php] ' . $exception);
    json_reply(false, null, 'Помилка сервера або БД. Перевірте імпорт schema.sql.', 500);
}

function json_profile_reply(array $data, array $profile): never
{
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['success' => true, 'data' => $data, 'profile' => $profile],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
