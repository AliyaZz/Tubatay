<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Необходима авторизация');
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");

$stmt->execute([$userId]);
echo json_encode(['success' => true, 'deleted' => $stmt->rowCount()]);
?>