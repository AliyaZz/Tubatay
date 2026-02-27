<?php
session_start();
require_once 'db.php';

// Всегда возвращаем JSON
header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Требуется авторизация']);
    exit;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $phone = $data['phone'] ?? '';
    $userId = $_SESSION['user_id'];

    // Начинаем транзакцию
    $pdo->beginTransaction();

    // 1. Рассчитываем общую стоимость
    $stmt = $pdo->prepare("
        SELECT SUM(m.price * c.quantity) as total
        FROM cart c
        JOIN menu_items m ON c.item_id = m.item_id
        WHERE c.user_id = :user_id
    ");
    $stmt->execute(['user_id' => $userId]);
    $total = $stmt->fetchColumn();

    if (!$total) {
        throw new Exception('Корзина пуста');
    }

    // 2. Создаем заказ
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_price, created_at, phone_number)
        VALUES (:user_id, :total, NOW(), :phone)
    ");
    $stmt->execute([
        'user_id' => $userId,
        'total' => $total,
        'phone' => $phone
    ]);
    $orderId = $pdo->lastInsertId();

    // 3. Переносим товары в order_items
    $stmt = $pdo->prepare("
        INSERT INTO order_items (order_id, item_id, quantity, price)
        SELECT 
            :order_id,
            c.item_id,
            c.quantity,
            m.price
        FROM cart c
        JOIN menu_items m ON c.item_id = m.item_id
        WHERE c.user_id = :user_id
    ");
    $stmt->execute([
        'order_id' => $orderId,
        'user_id' => $userId
    ]);

    // 4. Очищаем корзину
    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);

    // Фиксируем транзакцию
    $pdo->commit();

    echo json_encode(['success' => true, 'order_id' => $orderId]);
} catch (Exception $e) {
    // Получаем текст ошибки
    $rawMessage = $e->getMessage();

    // Если ошибка содержит "1644", обрезаем до текста ошибки
    if (preg_match('/1644\s(.+)/', $rawMessage, $matches)) {
        $cleanError = $matches[1];
    } else {
        $cleanError = $rawMessage;
    }

    echo json_encode(['error' => $cleanError]);
}
?>