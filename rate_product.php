<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Требуется авторизация']);
    exit;
}

$item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 5]
]);

if (!$item_id || !$rating) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные']);
    exit;
}

try {
    // Вставляем оценку
    $stmt = $pdo->prepare("INSERT INTO ratings (user_id, item_id, rating, created_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $item_id, $rating, date('Y-m-d H:i:s')]);

    // Получаем новый средний рейтинг
    $avg = $pdo->prepare("SELECT AVG(rating) FROM ratings WHERE item_id = ?");
    $avg->execute([$item_id]);
    $new_rating = (float)$avg->fetchColumn();

    echo json_encode([
        'success' => true,
        'new_rating' => $new_rating
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => (string)$e]);
}