<?php

$errors = [];
$success = false;

$title = '';
$author = '';
$message = '';

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function textLength(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    preg_match_all('/./us', $value, $matches);
    return count($matches[0]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $message = trim($_POST['message'] ?? '');


    if ($title === '') {
        $errors['title'] = 'Введіть назву теми.';
    } elseif (!preg_match('/^[\p{L}\p{N}\s.,!?():;\'"«»—–-]+$/u', $title)) {
        $errors['title'] = 'Назва містить заборонені спеціальні символи.';
    }

    if ($author === '') {
        $errors['author'] = 'Введіть ім’я автора.';
    }

    if ($message === '') {
        $errors['message'] = 'Введіть повідомлення.';
    } elseif (textLength($message) < 10) {
        $errors['message'] = 'Повідомлення має містити щонайменше 10 символів.';
    }

    if (empty($errors)) {
        $success = true;
    }
}
?>
<!doctype html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Нова тема форуму</h1>
    <?php if ($success): ?>
        <section class="success">
            <h2>Тему успішно створено</h2>
            <div class="result-row"><strong>Назва:</strong> <?= h($title) ?></div>
            <div class="result-row"><strong>Автор:</strong> <?= h($author) ?></div>
            <div class="result-row"><strong>Повідомлення:</strong><br><?= nl2br(h($message)) ?></div>
        </section>

        <div class="actions">
            <a href="form.php">Створити ще одну тему</a>
        </div>
    <?php else: ?>
        <div id="clientError" class="client-error" role="alert"></div>

        <form id="topicForm" method="post" action="form.php" novalidate>
            <div class="field">
                <label for="title">Назва теми</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?= h($title) ?>"
                    required
                    maxlength="120"
                    autocomplete="off"
                    placeholder="Наприклад: Питання щодо практичної роботи"
                >
                <div class="hint">Дозволені літери, цифри, пробіли та звичайні розділові знаки.</div>
                <?php if (isset($errors['title'])): ?>
                    <div class="error"><?= h($errors['title']) ?></div>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="author">Автор</label>
                <input
                    type="text"
                    id="author"
                    name="author"
                    value="<?= h($author) ?>"
                    required
                    maxlength="80"
                    autocomplete="name"
                    placeholder="Ваше ім’я"
                >
                <?php if (isset($errors['author'])): ?>
                    <div class="error"><?= h($errors['author']) ?></div>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="message">Повідомлення</label>
                <textarea
                    id="message"
                    name="message"
                    required
                    minlength="10"
                    maxlength="2000"
                    placeholder="Введіть повідомлення (мінімум 10 символів)"
                ><?= h($message) ?></textarea>
                <?php if (isset($errors['message'])): ?>
                    <div class="error"><?= h($errors['message']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit">Створити тему</button>
        </form>
    <?php endif; ?>
</div>

<script>
(() => {
    const draftKey = 'forum_variant9_message_draft';
    const success = <?= $success ? 'true' : 'false' ?>;

    if (success) {
        localStorage.removeItem(draftKey);
        return;
    }

    const form = document.getElementById('topicForm');
    const title = document.getElementById('title');
    const author = document.getElementById('author');
    const message = document.getElementById('message');
    const clientError = document.getElementById('clientError');

    const savedDraft = localStorage.getItem(draftKey);
    if (savedDraft !== null && message.value.trim() === '') {
        message.value = savedDraft;
    }

    message.addEventListener('input', () => {
        localStorage.setItem(draftKey, message.value);
    });

    form.addEventListener('submit', (event) => {
        clientError.style.display = 'none';
        clientError.textContent = '';

        const titleValue = title.value.trim();
        const authorValue = author.value.trim();
        const messageValue = message.value.trim();


        const allowedTitle = /^[\p{L}\p{N}\s.,!?():;'"«»—–-]+$/u;

        let errorText = '';

        if (titleValue === '') {
            errorText = 'Введіть назву теми.';
        } else if (!allowedTitle.test(titleValue)) {
            errorText = 'Назва містить заборонені спеціальні символи.';
        } else if (authorValue === '') {
            errorText = 'Введіть ім’я автора.';
        } else if (Array.from(messageValue).length < 10) {
            errorText = 'Повідомлення має містити щонайменше 10 символів.';
        }

        if (errorText !== '') {
            event.preventDefault();
            clientError.textContent = errorText;
            clientError.style.display = 'block';
            clientError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
})();
</script>
</body>
</html>
