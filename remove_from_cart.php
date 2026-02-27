<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Необходима авторизация');
}

$data = json_decode(file_get_contents('php://input'), true);
$itemId = $data['item_id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$itemId) {
    http_response_code(400);
    exit('Неверные данные');
}

// Удаляем товар из корзины
$stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND item_id = ?");
$stmt->execute([$userId, $itemId]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Товар не найден в корзине']);
}
?>