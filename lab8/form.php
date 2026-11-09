<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/lib/layout.php';
$errors = [];
$title = '';
$author = '';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $title = post_text('title');
    $author = post_text('author');
    $message = post_text('message');
    if ($title === '' || utf_length($title) > 120) {
        $errors[] = 'Назва має містити від 1 до 120 символів.';
    } elseif (!preg_match('/^[\p{L}\p{N}\s.,!?():;\'"«»—–-]+$/u', $title)) {
        $errors[] = 'Назва містить заборонені спеціальні символи.';
    }
    if ($author === '' || utf_length($author) > 80) $errors[] = 'Введіть ім’я автора (до 80 символів).';
    if (utf_length($message) < 10 || utf_length($message) > 2000) {
        $errors[] = 'Повідомлення має містити від 10 до 2000 символів.';
    }
    $success = !$errors;
}
page_header('Форма №2');
?>
<section class="card narrow">
    <h2>Форма - перевірка введення</h2>
    <p class="hint">Показує перевірку POST і localStorage. Повідомлення не додається до бази тем.</p>
    <?php if ($success): ?>
        <div class="success"><strong>Дані успішно перевірено</strong>
            <p>Назва: <?= h($title) ?></p><p>Автор: <?= h($author) ?></p>
            <p>Повідомлення: <?= nl2br(h($message)) ?></p>
        </div>
        <p><a href="form.php">Заповнити ще раз</a></p>
        <script>localStorage.removeItem('forum_variant9_message_draft');</script>
    <?php else: ?>
        <div id="clientError" class="error" role="alert"></div>
        <?php foreach ($errors as $error): ?><p class="error"><?= h($error) ?></p><?php endforeach; ?>
        <form id="validationForm" method="post" action="form.php" novalidate>
            <?= csrf_field() ?>
            <label for="title">Назва теми</label>
            <input type="text" id="title" name="title" maxlength="120" value="<?= h($title) ?>" required>
            <label for="author">Автор</label>
            <input type="text" id="author" name="author" maxlength="80" value="<?= h($author) ?>" required>
            <label for="messageField">Повідомлення (мінімум 10 символів)</label>
            <textarea id="messageField" name="message" minlength="10" maxlength="2000" required><?= h($message) ?></textarea>
            <button type="submit">Перевірити</button>
        </form>
        <script src="form.js"></script>
    <?php endif; ?>
</section>
<?php page_footer(); ?>
