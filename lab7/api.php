<?php


require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$start = microtime(true);
$sqlQueries = 0;

$resource = $_GET['resource'] ?? null;
$mode = $_GET['mode'] ?? 'after';

function respondProfile(array $data, float $start, int $sqlQueries): void
{
    $elapsed = (microtime(true) - $start) * 1000;
    $peakMemory = memory_get_peak_usage(true) / 1024 / 1024;

    echo json_encode([
        'success' => true,
        'data' => $data,
        'profile' => [
            'execution_ms' => round($elapsed, 2),
            'peak_memory_mb' => round($peakMemory, 2),
            'sql_queries' => $sqlQueries
        ]
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

function listTopicsBefore(PDO $pdo, int &$sqlQueries): array
{
    $sqlQueries++;
    $topics = $pdo->query(
        'SELECT id, title, author, created_at, is_pinned
         FROM topics
         ORDER BY id DESC'
    )->fetchAll();

    foreach ($topics as &$topic) {
        $sqlQueries++;

        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM topics
             WHERE author = :author'
        );

        $stmt->execute([
            ':author' => $topic['author']
        ]);

        $topic['author_topic_count'] = (int) $stmt->fetchColumn();
    }

    return $topics;
}

function listTopicsAfter(PDO $pdo, int &$sqlQueries): array
{
    $sqlQueries++;
    $topics = $pdo->query(
        'SELECT id, title, author, created_at, is_pinned
         FROM topics
         ORDER BY id DESC'
    )->fetchAll();

    $sqlQueries++;
    $counts = $pdo->query(
        'SELECT author, COUNT(*) AS cnt
         FROM topics
         GROUP BY author'
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    foreach ($topics as &$topic) {
        $topic['author_topic_count'] = (int) ($counts[$topic['author']] ?? 0);
    }

    return $topics;
}

function listPinnedCached(PDO $pdo, int &$sqlQueries, int $ttlSeconds = 60): array
{
    $cacheFile = sys_get_temp_dir() . '/lab7_topics_pinned.json';

    if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $ttlSeconds) {
        $cached = json_decode(file_get_contents($cacheFile), true);
        return is_array($cached) ? $cached : [];
    }

    $sqlQueries++;

    $topics = $pdo->query(
        'SELECT id, title, author, created_at, is_pinned
         FROM topics
         WHERE is_pinned = 1
         ORDER BY id DESC'
    )->fetchAll();

    file_put_contents(
        $cacheFile,
        json_encode($topics, JSON_UNESCAPED_UNICODE)
    );

    return $topics;
}

if ($resource !== 'topics') {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Невідомий ресурс.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($mode === 'before') {
    $topics = listTopicsBefore($pdo, $sqlQueries);
    respondProfile($topics, $start, $sqlQueries);
}

if ($mode !== 'after') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Параметр mode має бути before або after.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$topics = listTopicsAfter($pdo, $sqlQueries);
$pinnedTopics = listPinnedCached($pdo, $sqlQueries);

respondProfile([
    'topics' => $topics,
    'pinned_topics' => $pinnedTopics
], $start, $sqlQueries);
