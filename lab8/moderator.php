<?php
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/lib/layout.php';


$hash = getenv('FORUM_MODERATOR_PASSWORD_HASH') ?: '$2y$12$04rfNYMj7OTzESc5eNLp2eVm2Twc9A5lztkXpX7hTA5naDPGfrt.C';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    if (isset($_POST['logout'])) {
        unset($_SESSION['moderator']);
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        header('Location: moderator.php');
        exit;
    }
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    if (password_verify($password, $hash)) {
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['moderator'] = true;
        header('Location: index.php');
        exit;
    }
    $error = 'Неправильний пароль.';
}
page_header('Вхід модератора');
?>
<section class="card narrow">
    <h2>Модератор</h2>
    <?php if ($error !== ''): ?><p class="error"><?= h($error) ?></p><?php endif; ?>
    <?php if (is_moderator()): ?>
        <p>Ви увійшли як модератор. Доступні редагування, видалення й закріплення тем.</p>
        <form method="post" action="moderator.php"><?= csrf_field() ?><button type="submit" name="logout" value="1">Вийти</button></form>
    <?php else: ?>
        <form method="post" action="moderator.php">
            <?= csrf_field() ?>
            <label for="password">Пароль модератора</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            <button type="submit">Увійти</button>
        </form>
        <p class="hint">Пароль - Moderator9!</p>
    <?php endif; ?>
</section>
<?php page_footer(); ?>
