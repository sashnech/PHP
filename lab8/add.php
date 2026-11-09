<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lib/TopicRepository.php';
require_once __DIR__ . '/lib/layout.php';

$errors = [];
$title = '';
$author = '';
$createdAt = date('Y-m-d\TH:i');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $title = post_text('title');
    $author = post_text('author');
    $rawCreatedAt = $_POST['created_at'] ?? null;
    $createdAt = is_string($rawCreatedAt) ? $rawCreatedAt : '';
    $errors = validate_topic($title, $author, date_from_form($createdAt));
    if (!is_string($rawCreatedAt)) {
        $errors[] = 'Поле created_at має бути рядком.';
    }
    if (!$errors) {
        add_topic($pdo, $title, $author, date_from_form($createdAt));
        header('Location: index.php');
        exit;
    }
}

page_header('Додати тему');
?>
<section class="card narrow">
    <h2>Додати тему</h2>
    <?php foreach ($errors as $error): ?><p class="error"><?= h($error) ?></p><?php endforeach; ?>
    <form method="post" action="add.php">
        <?= csrf_field() ?>
        <label for="title">Назва</label>
        <input id="title" name="title" type="text" value="<?= h($title) ?>" maxlength="255" required>
        <label for="author">Автор</label>
        <input id="author" name="author" type="text" value="<?= h($author) ?>" maxlength="100" required>
        <label for="created_at">Дата й час створення</label>
        <input id="created_at" name="created_at" type="datetime-local" value="<?= h($createdAt) ?>" required>
        <p class="hint">Тема створюється незакріпленою. Закріплення доступне модератору.</p>
        <button type="submit">Додати</button> <a href="index.php">Назад</a>
    </form>
</section>
<?php page_footer(); ?>
