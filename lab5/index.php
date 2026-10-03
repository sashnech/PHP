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
        <h2>Додати тему</h2>

        <form id="topicForm">
            <label for="title">Назва теми</label>
            <input type="text" id="title" name="title" required>

            <label for="author">Автор</label>
            <input type="text" id="author" name="author" required>

            <label for="created_at">Дата і час створення</label>
            <input type="datetime-local" id="created_at" name="created_at" required>

            <label class="checkbox">
                <input type="checkbox" id="is_pinned" name="is_pinned">
                Закріплена тема
            </label>

            <button type="submit">Додати тему</button>
        </form>

        <div id="message"></div>
    </section>

    <section class="card">
        <h2>Список тем</h2>

        <label for="search">Пошук за автором</label>
        <input type="text" id="search" placeholder="Введіть ім’я автора">

        <div id="results"></div>
    </section>
</div>

<script src="script.js"></script>
</body>
</html>
