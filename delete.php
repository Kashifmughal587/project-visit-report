<?php
require_once __DIR__ . '/db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("DELETE FROM project_visits WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php');
exit;
