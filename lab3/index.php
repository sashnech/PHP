<?php

require_once __DIR__ . '/classes/Topic.php';
require_once __DIR__ . '/classes/PinnedTopic.php';
require_once __DIR__ . '/classes/Forum.php';
require_once __DIR__ . '/lib/functions.php';

$forum = new Forum();

$topics = [
    new Topic('Як почати вивчати PHP?', 'Олександр', '-2 hours'),
    new Topic('Порадьте середовище для веброзробки', 'Марія', '-1 day'),
    new PinnedTopic('Правила форуму', 'Адміністратор', '-5 days', '+7 days'),
    new PinnedTopic('Важливе оголошення для студентів', 'Олександр', '-30 minutes', '+2 days'),
    new Topic('Laravel чи чистий PHP?', 'Іван', '-3 days')
];

foreach ($topics as $topic) {
    $forum->addTopic($topic);
}

$authorTopics = $forum->listByAuthor('Олександр');
$pinnedTopics = $forum->listPinned();
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

    <section class="card">
        <h2>Усі теми</h2>
        <table>
            <thead>
            <tr>
                <th>Інформація</th>
                <th>Створено</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($topics as $topic): ?>
                <tr>
                    <td><?= sanitizeText($topic->getInfo()) ?></td>
                    <td><?= sanitizeText(timeAgo($topic->getCreatedAt())) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="card">
        <h2>Теми автора «Олександр»</h2>
        <ul>
            <?php foreach ($authorTopics as $topic): ?>
                <li><?= sanitizeText($topic->getInfo()) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="card">
        <h2>Закріплені теми</h2>
        <ul>
            <?php foreach ($pinnedTopics as $topic): ?>
                <li><?= sanitizeText($topic->getInfo()) ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
</body>
</html>
