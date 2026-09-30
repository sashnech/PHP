<?php

require_once __DIR__ . '/db.php';

function updateTopic($pdo, $id, $title, $author, $isPinned)
{
    $stmt = $pdo->prepare(
        'UPDATE topics
         SET title = :title, author = :author, is_pinned = :is_pinned
         WHERE id = :id'
    );

    $stmt->execute([
        ':title' => $title,
        ':author' => $author,
        ':is_pinned' => $isPinned,
        ':id' => $id,
    ]);
}

$id = $_GET['id'] ?? $_POST['id'] ?? null;

$stmt = $pdo->prepare('SELECT * FROM topics WHERE id = :id');
$stmt->execute([':id' => $id]);
$topic = $stmt->fetch();

if (!$topic) {
    exit('Тему не знайдено.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isPinned = isset($_POST['is_pinned']) ? 1 : 0;

    updateTopic($pdo, $id, $title, $author, $isPinned);

    header('Location: index.php');
    exit;
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Редагувати тему</title>

</head>
<body>
<div class="card">
    <h1>Редагувати тему</h1>

    <div class="row"><span class="label">Створено:</span> <?= h($topic['created_at']) ?></div>

    <form method="post" action="edit.php">
        <input type="hidden" name="id" value="<?= h($topic['id']) ?>">

        <label for="title">Назва</label>
        <input id="title" name="title" type="text" value="<?= h($topic['title']) ?>" required>

        <label for="author">Автор</label>
        <input id="author" name="author" type="text" value="<?= h($topic['author']) ?>" required>

        <div class="check">
            <label>
                <input type="checkbox" name="is_pinned" value="1" <?= $topic['is_pinned'] ? 'checked' : '' ?>>
                Закріплена тема
            </label>
        </div>

        <button type="submit">Зберегти</button>
        <a href="index.php">Назад</a>
    </form>
</div>
</body>
</html>
