<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$topics = [
    [
        'title' => 'Як почати вивчати PHP?',
        'author' => 'Олександр',
        'repliesCount' => 28,
        'createdAt' => '2026-09-10'
    ],
    [
        'title' => 'Порадьте середовище для веброзробки',
        'author' => 'Марія',
        'repliesCount' => 12,
        'createdAt' => '2026-09-11'
    ],
    [
        'title' => 'Laravel чи чистий PHP?',
        'author' => 'Іван',
        'repliesCount' => 35,
        'createdAt' => '2026-09-12'
    ],
    [
        'title' => 'Помилка підключення до MySQL',
        'author' => 'Андрій',
        'repliesCount' => 19,
        'createdAt' => '2026-09-12'
    ],
    [
        'title' => 'Корисні ресурси для вивчення HTML і CSS',
        'author' => 'Софія',
        'repliesCount' => 24,
        'createdAt' => '2026-09-13'
    ],
    [
        'title' => 'Як правильно організувати структуру PHP-проєкту?',
        'author' => 'Дмитро',
        'repliesCount' => 0,
        'createdAt' => '2026-09-14'
    ]
];

function formatTopic(array $topic): string
{
    return "{$topic['title']} — автор: {$topic['author']}";
}

function getTopicStatus(int $repliesCount): string
{
    if ($repliesCount > 20) {
        return 'Гаряча тема';
    } elseif ($repliesCount > 0) {
        return 'Звичайна тема';
    } else {
        return 'Без відповідей';
    }
}

$totalReplies = 0;

foreach ($topics as $topic) {
    $totalReplies += $topic['repliesCount'];
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
<main class="container">
    <h1>Дошка обговорень</h1>

    <table>
        <thead>
        <tr>
            <th>№</th>
            <th>Тема</th>
            <th>Автор</th>
            <th>Кількість відповідей</th>
            <th>Дата створення</th>
            <th>Статус</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($topics as $index => $topic): ?>
            <?php $status = getTopicStatus($topic['repliesCount']); ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td>
                    <strong><?= htmlspecialchars($topic['title']) ?></strong>
                    <div class="description">
                        <?= htmlspecialchars(formatTopic($topic)) ?>
                    </div>
                </td>
                <td><?= htmlspecialchars($topic['author']) ?></td>
                <td><?= $topic['repliesCount'] ?></td>
                <td><?= htmlspecialchars($topic['createdAt']) ?></td>
                <td>
                    <span class="status <?= $status === 'Гаряча тема' ? 'hot' : 'normal' ?>">
                        <?= htmlspecialchars($status) ?>
                    </span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <section class="summary">
        <h2>Підсумок</h2>
        <p>
            Сумарна кількість повідомлень (відповідей) по всіх темах:
            <strong><?= $totalReplies ?></strong>
        </p>
    </section>
</main>
</body>
</html>
