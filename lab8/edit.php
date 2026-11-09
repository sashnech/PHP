<?php
require_once __DIR__ . '/bootstrap.php';
require_moderator();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lib/TopicRepository.php';
require_once __DIR__ . '/lib/layout.php';

$id = positive_id($_GET['id'] ?? $_POST['id'] ?? null);
if (!$id) { http_response_code(400); exit('Некоректний id.'); }
$topic = get_topic($pdo, $id);
if (!$topic) { http_response_code(404); exit('Тему не знайдено.'); }
$errors = [];
$title = (string)$topic['title'];
$author = (string)$topic['author'];
$pinned = (int)$topic['is_pinned'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $title = post_text('title');
    $author = post_text('author');
    $pinned = isset($_POST['is_pinned']) ? 1 : 0;
    $errors = validate_topic($title, $author, (string)$topic['created_at']);
    if (!$errors) {
        update_topic($pdo, $id, $title, $author, $pinned);
        header('Location: index.php');
        exit;
    }
}
page_header('Редагування теми');
?>
<section class="card narrow">
    <h2>Редагувати тему №<?= h($id) ?></h2>
    <p>Створено: <?= h($topic['created_at']) ?></p>
    <?php foreach ($errors as $error): ?><p class="error"><?= h($error) ?></p><?php endforeach; ?>
    <form method="post" action="edit.php">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= h($id) ?>">
        <label for="title">Назва</label><input id="title" name="title" type="text" maxlength="255" value="<?= h($title) ?>" required>
        <label for="author">Автор</label><input id="author" name="author" type="text" maxlength="100" value="<?= h($author) ?>" required>
        <label class="checkbox"><input type="checkbox" name="is_pinned" value="1" <?= $pinned ? 'checked' : '' ?>> Закріплена тема</label>
        <button type="submit">Зберегти</button> <a href="index.php">Назад</a>
    </form>
</section>
<?php page_footer(); ?>
