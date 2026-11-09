<?php
declare(strict_types=1);

function list_topics(PDO $pdo, string $author = ''): array
{
    if ($author === '') {
        return $pdo->query('SELECT * FROM topics ORDER BY id DESC')->fetchAll();
    }
    $stmt = $pdo->prepare('SELECT * FROM topics WHERE author LIKE :author ORDER BY id DESC');
    $stmt->execute(['author' => '%' . $author . '%']);
    return $stmt->fetchAll();
}

function get_topic(PDO $pdo, int $id): array|false
{
    $stmt = $pdo->prepare('SELECT * FROM topics WHERE id = :id');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch();
}

function list_pinned(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM topics WHERE is_pinned = 1 ORDER BY id DESC')->fetchAll();
}

function add_topic(PDO $pdo, string $title, string $author, string $createdAt): array
{
    $stmt = $pdo->prepare('INSERT INTO topics (title, author, created_at, is_pinned) VALUES (:title, :author, :created_at, 0)');
    $stmt->execute(['title' => $title, 'author' => $author, 'created_at' => $createdAt]);
    return get_topic($pdo, (int)$pdo->lastInsertId());
}

function update_topic(PDO $pdo, int $id, string $title, string $author, int $pinned): void
{
    $stmt = $pdo->prepare('UPDATE topics SET title = :title, author = :author, is_pinned = :pinned WHERE id = :id');
    $stmt->execute(['title' => $title, 'author' => $author, 'pinned' => $pinned, 'id' => $id]);
    invalidate_pinned_cache();
}

function delete_topic(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM topics WHERE id = :id');
    $stmt->execute(['id' => $id]);
    invalidate_pinned_cache();
}

function pin_topic(PDO $pdo, int $id): array|false
{
    $topic = get_topic($pdo, $id);
    if (!$topic) return false;
    $stmt = $pdo->prepare('UPDATE topics SET is_pinned = 1 WHERE id = :id');
    $stmt->execute(['id' => $id]);
    invalidate_pinned_cache();
    return get_topic($pdo, $id);
}
