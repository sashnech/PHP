<?php

function listPinnedCached(
    PDO $pdo,
    int &$sqlQueries,
    array &$cacheInfo,
    int $ttlSeconds = 60
): array {
    $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR
        . 'lab7_pinned_' . sha1(__DIR__) . '.json';

    $sqlQueries++;
    $statement = $pdo->query('SELECT revision FROM lab7_pinned_cache_state WHERE id = 1');
    $revisionValue = $statement->fetchColumn();
    if ($revisionValue === false) {
        throw new RuntimeException('Не налаштовано lab7_pinned_cache_state. Виконайте schema.sql.');
    }
    $revision = (int) $revisionValue;
    $now = time();

    if (is_file($cacheFile) && is_readable($cacheFile)) {
        $raw = @file_get_contents($cacheFile);
        $cached = is_string($raw) ? json_decode($raw, true) : null;

        if (is_array($cached)
            && isset($cached['revision'], $cached['saved_at'], $cached['topics'])
            && (int) $cached['revision'] === $revision
            && is_numeric($cached['saved_at'])
            && $now >= (int) $cached['saved_at']
            && $now - (int) $cached['saved_at'] < $ttlSeconds
            && is_array($cached['topics'])
        ) {
            $cacheInfo = [
                'status' => 'hit',
                'revision' => $revision,
                'ttl_seconds' => $ttlSeconds
            ];
            return $cached['topics'];
        }
    }

    $sqlQueries++;
    $topics = $pdo->query(
        'SELECT id, title, author, created_at, is_pinned
         FROM topics WHERE is_pinned = 1 ORDER BY id DESC'
    )->fetchAll();

    $payload = json_encode([
        'revision' => $revision,
        'saved_at' => $now,
        'topics' => $topics
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    if ($payload !== false && @file_put_contents($cacheFile, $payload, LOCK_EX) === false) {
        error_log('[lab7] Не вдалося зберегти кеш закріплених тем.');
    }

    $cacheInfo = [
        'status' => 'miss',
        'revision' => $revision,
        'ttl_seconds' => $ttlSeconds
    ];
    return $topics;
}
