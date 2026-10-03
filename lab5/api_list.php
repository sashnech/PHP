<?php

require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if ($q === '') {
    $stmt = $pdo->query(
        'SELECT id, title, author, created_at, is_pinned
         FROM topics
         ORDER BY id DESC'
    );
} else {
    $stmt = $pdo->prepare(
        'SELECT id, title, author, created_at, is_pinned
         FROM topics
         WHERE author LIKE :author
         ORDER BY id DESC'
    );

    $stmt->execute([
        ':author' => '%' . $q . '%'
    ]);
}

echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
