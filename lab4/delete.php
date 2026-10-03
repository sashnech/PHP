<?php

require_once __DIR__ . '/db.php';

function deleteTopic($pdo, $id)
{
    $stmt = $pdo->prepare('DELETE FROM topics WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    deleteTopic($pdo, $id);
}

header('Location: index.php');
exit;
