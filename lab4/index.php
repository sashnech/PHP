<?php

require_once __DIR__ . '/db.php';

function listByAuthor($pdo, $author)
{
    $stmt = $pdo->prepare('SELECT * FROM topics WHERE author = :author ORDER BY created_at DESC');
    $stmt->execute([':author' => $author]);
    return $stmt->fetchAll();
}

function listPinned($pdo)
{
    $stmt = $pdo->query('SELECT * FROM topics WHERE is_pinned = 1 ORDER BY created_at DESC');
    return $stmt->fetchAll();
}

$topics = $pdo->query('SELECT * FROM topics ORDER BY created_at DESC')->fetchAll();
$pinnedTopics = listPinned($pdo);

$author = trim($_GET['author'] ?? '');
$authorTopics = $author !== '' ? listByAuthor($pdo, $author) : [];

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
</head>
<body>
<div class="container">
    <h1>Форум</h1>

    <div class="top-actions">
        <a class="button" href="add.php">Додати тему</a>
    </div>

    <section class="card">
        <h2>Усі теми</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Назва</th>
                <th>Автор</th>
                <th>Створено</th>
                <th>Закріплена</th>
                <th>Дії</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($topics as $topic): ?>
                <tr>
                    <td><?= h($topic['id']) ?></td>
                    <td><?= h($topic['title']) ?></td>
                    <td><?= h($topic['author']) ?></td>
                    <td><?= h($topic['created_at']) ?></td>
                    <td><?= $topic['is_pinned'] ? 'Так' : 'Ні' ?></td>
                    <td>
                        <div class="actions">
                            <a class="link" href="edit.php?id=<?= h($topic['id']) ?>">Редагувати</a>
                            <form method="post" action="delete.php" onsubmit="return confirm('Видалити цю тему?');">
                                <input type="hidden" name="id" value="<?= h($topic['id']) ?>">
                                <button class="delete" type="submit">Видалити</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="card">
        <h2>Теми за автором</h2>
        <form class="filter" method="get" action="index.php">
            <input type="text" name="author" value="<?= h($author) ?>" placeholder="Введіть автора" required>
            <button type="submit">Знайти</button>
        </form>

        <?php if ($author !== ''): ?>
            <ul>
                <?php foreach ($authorTopics as $topic): ?>
                    <li><?= h($topic['title']) ?> — <?= h($topic['created_at']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Закріплені теми</h2>
        <ul>
            <?php foreach ($pinnedTopics as $topic): ?>
                <li><?= h($topic['title']) ?> - <?= h($topic['author']) ?> - <?= h($topic['created_at']) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
</body>
</html>
