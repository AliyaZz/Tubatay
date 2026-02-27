<?php
session_start();
require_once 'db.php';

// Уничтожаем все данные сессии
session_unset();
session_destroy();

// Редирект на предыдущую страницу или главную
$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: " . $redirect_url);
exit;
?>