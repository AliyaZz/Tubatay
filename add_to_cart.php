<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Необходимо авторизоваться']));
}

$data = json_decode(file_get_contents('php://input'), true);
$item_id = $data['item_id'];
$quantity = $data['quantity'] ?? 1;

// Проверка существования товара
$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE item_id = ?");
$stmt->execute([$item_id]);
if (!$stmt->fetch()) {
    http_response_code(404);
    die(json_encode(['error' => 'Товар не найден']));
}

// Работа с корзиной
$stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND item_id = ?");
$stmt->execute([$_SESSION['user_id'], $item_id]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ?");
    $stmt->execute([$existing['cart_id']]);
} else {
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, item_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $item_id, $quantity]);
}

echo json_encode(['success' => true]);
?>