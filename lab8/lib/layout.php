<?php
declare(strict_types=1);

function page_header(string $title): void
{
    $token = h(csrf_token());
    $moderator = is_moderator();
    echo '<!doctype html><html lang="uk"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="csrf-token" content="' . $token . '">';
    echo '<title>' . h($title) . '</title><link rel="stylesheet" href="style.css"></head><body><div class="container">';
    echo '<header><h1>Форум </h1><nav>';
    echo '<a href="index.php">Теми</a><a href="ajax.php">AJAX</a><a href="form.php">Форма</a>';
    echo '<a href="lab1.php">PHP</a><a href="oop.php">ООП</a>';
    echo '<a href="profiling/api.php?resource=topics&amp;mode=after">Профілювання</a><a href="moderator.php">' . ($moderator ? 'Модератор ' : 'Вхід модератора') . '</a>';
    echo '</nav></header>';
}

function page_footer(): void
{
    echo '</div></body></html>';
}
