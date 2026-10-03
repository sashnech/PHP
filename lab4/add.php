<?php

require_once __DIR__ . '/db.php';

function addTopic($pdo, $title, $author, $createdAt, $isPinned)
{
    $stmt = $pdo->prepare(
        'INSERT INTO topics (title, author, created_at, is_pinned)
         VALUES (:title, :author, :created_at, :is_pinned)'
    );

    $stmt->execute([
        ':title' => $title,
        ':author' => $author,
        ':created_at' => $createdAt,
        ':is_pinned' => $isPinned,
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $createdAt = str_replace('T', ' ', $_POST['created_at'] ?? '');
    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;

    addTopic($pdo, $title, $author, $createdAt, $isPinned);

    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Додати тему</title>

</head>
<body>
<div class="card">
    <h1>Додати тему</h1>
    <form method="post" action="add.php">
        <label for="title">Назва</label>
        <input id="title" name="title" type="text" required>

        <label for="author">Автор</label>
        <input id="author" name="author" type="text" required>

        <label for="created_at">Дата створення</label>
        <input id="created_at" name="created_at" type="datetime-local" required>

        <div class="check">
            <label><input type="checkbox" name="is_pinned" value="1"> Закріплена тема</label>
        </div>

        <button type="submit">Додати</button>
        <a href="index.php">Назад</a>
    </form>
</div>
</body>
</html>
