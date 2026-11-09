<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lib/TopicRepository.php';
require_once __DIR__ . '/lib/layout.php';

$author = search_author($_GET['author'] ?? '');
if ($author === null) {
    http_response_code(400);
    exit('Некоректний параметр author.');
}
$topics = list_topics($pdo);
$matches = $author !== '' ? list_topics($pdo, $author) : [];
$pinnedTopics = list_pinned($pdo);
page_header('Теми форуму');
?>
<section class="card">
    <h2>Усі теми</h2>
    <p><a class="button" href="add.php">Додати тему</a></p>
    <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>Назва</th><th>Автор</th><th>Створено</th><th>Закріплена</th><?php if (is_moderator()): ?><th>Дії</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($topics as $topic): ?>
            <tr>
                <td><?= h($topic['id']) ?></td><td><?= h($topic['title']) ?></td>
                <td><?= h($topic['author']) ?></td><td><?= h($topic['created_at']) ?></td>
                <td><?= $topic['is_pinned'] ? 'Так' : 'Ні' ?></td>
                <?php if (is_moderator()): ?>
                <td><div class="actions">
                    <a href="edit.php?id=<?= h($topic['id']) ?>">Редагувати</a>
                    <form method="post" action="delete.php" onsubmit="return confirm('Видалити цю тему?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= h($topic['id']) ?>">
                        <button type="submit" class="danger">Видалити</button>
                    </form>
                    <?php if (!(int)$topic['is_pinned']): ?>
                    <form method="post" action="pin.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= h($topic['id']) ?>">
                        <button type="submit" class="secondary">Закріпити</button>
                    </form>
                    <?php endif; ?>
                </div></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php if (!$topics): ?><p>Тем поки немає.</p><?php endif; ?>
</section>
<section class="card">
    <h2>Пошук за автором (SQL)</h2>
    <form class="filter" method="get" action="index.php">
        <input type="text" name="author" maxlength="100" placeholder="Введіть автора" value="<?= h($author) ?>">
        <button type="submit">Знайти</button>
    </form>
    <?php if ($author !== ''): ?>
        <ul><?php foreach ($matches as $topic): ?><li><?= h($topic['title']) ?> — <?= h($topic['author']) ?></li><?php endforeach; ?></ul>
        <?php if (!$matches): ?><p>Збігів не знайдено.</p><?php endif; ?>
    <?php endif; ?>
</section>
<section class="card">
    <h2>Закріплені теми</h2>
    <ul><?php foreach ($pinnedTopics as $topic): ?><li><?= h($topic['title']) ?> — <?= h($topic['author']) ?> — <?= h($topic['created_at']) ?></li><?php endforeach; ?></ul>
    <?php if (!$pinnedTopics): ?><p>Закріплених тем немає.</p><?php endif; ?>
</section>
<?php page_footer(); ?>
