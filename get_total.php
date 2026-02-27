<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Требуется авторизация']));
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT getCartTotal(?) as total");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'total' => number_format($result['total'], 2)
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка сервера: ' . $e->getMessage()]);
}
?>