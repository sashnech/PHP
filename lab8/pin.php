<?php
require_once __DIR__ . '/bootstrap.php';
require_post();
require_csrf();
require_moderator();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lib/TopicRepository.php';
$id = positive_id($_POST['id'] ?? null);
if (!$id) { http_response_code(400); exit('Некоректний id.'); }
if (!pin_topic($pdo, $id)) { http_response_code(404); exit('Тему не знайдено.'); }
header('Location: index.php');
exit;
