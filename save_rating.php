<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Требуется авторизация']);
    exit;
}

$user_id = $_SESSION['user_id'];
$item_id = (int)$_POST['item_id'];
$rating = (int)$_POST['rating'];

// Проверка данных
if ($rating < 1 || $rating > 5 || $item_id < 1) {
    http_response_code(400);
    echo json_encode(['error' => 'Некорректные данные']);
    exit;
}

// Проверяем существующую оценку
$stmt = $pdo->prepare("SELECT rating_id FROM ratings WHERE user_id = ? AND item_id = ?");
$stmt->execute([$user_id, $item_id]);
$existing = $stmt->fetch();

try {
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE ratings SET rating = ? WHERE rating_id = ?");
        $stmt->execute([$rating, $existing['rating_id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO ratings (user_id, item_id, rating, created_at) 
                             VALUES (?, ?, ?, NOW())");
        $stmt->execute([$user_id, $item_id, $rating]);
    }
    
    // Получаем новый средний рейтинг
    $avgStmt = $pdo->prepare("SELECT COALESCE(AVG(rating), 0) FROM ratings WHERE item_id = ?");
    $avgStmt->execute([$item_id]);
    $average = round($avgStmt->fetchColumn(), 1);
    
    echo json_encode(['average' => $average]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка базы данных']);
}