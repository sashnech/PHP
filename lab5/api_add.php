<?php

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$createdAt = trim($_POST['created_at'] ?? '');
$isPinned = isset($_POST['is_pinned']) ? 1 : 0;

if ($title === '' || $author === '' || $createdAt === '') {
    http_response_code(400);
    echo json_encode([
        'error' => 'Заповніть усі обов’язкові поля.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
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

http_response_code(201);

echo json_encode($stmt->fetch(), JSON_UNESCAPED_UNICODE);
