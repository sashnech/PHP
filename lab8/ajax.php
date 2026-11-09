<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/lib/layout.php';
page_header('AJAX та JSON');
?>
<section class="card narrow">
    <h2>Додати тему через AJAX</h2>
    <form id="topicForm">
        <?= csrf_field() ?>
        <label for="title">Назва</label><input id="title" name="title" type="text" maxlength="255" required>
        <label for="author">Автор</label><input id="author" name="author" type="text" maxlength="100" required>
        <label for="created_at">Дата й час створення</label><input id="created_at" name="created_at" type="datetime-local" required value="<?= date('Y-m-d\TH:i') ?>">
        <button type="submit">Додати тему</button>
    </form>
    <p id="message" role="status"></p>
</section>
<section class="card">
    <h2>Живий пошук за автором</h2>
    <input type="text" id="search" placeholder="Введіть автора" maxlength="100">
    <div id="results" aria-live="polite">Завантаження...</div>
</section>
<script src="script.js"></script>
<?php page_footer(); ?>
