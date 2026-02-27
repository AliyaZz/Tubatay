<?php
session_start();
require_once 'db.php';

// Обработка авторизации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    $login_errors = [];
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_errors[] = 'Некорректный email';
    }
    
    if (empty($password)) {
        $login_errors[] = 'Введите пароль';
    }
    
    if (empty($login_errors)) {
        try {
            // Ищем пользователя
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password'])) {
                $login_errors[] = 'Неверные учетные данные';
            } else {
                // Успешная авторизация
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_email'] = $user['email'];
                $login_success = 'Авторизация успешна! Добро пожаловать, ' . htmlspecialchars($user['name']);
                
                // Редирект или обновление страницы
                header('Location: index.php');
                exit;
            }
        } catch (PDOException $e) {
            $login_errors[] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="stylemenu.css">
    <title>Войти</title>
  </head>
<body>
  <div class="mainb">
    <header class='header'>
      <div class='header-inner'>
        <div class="container p-lg-0">
          <nav class="navbar navbar-expand-lg main-navbar">
            <a class="navbar-brand" href="./index.php">Тюбетей</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon">
                <i class="fas fa-bars" style="margin:5px 0px 0px 0px;"></i>
              </span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav me-auto">
                <li class="nav-item">
                  <a class="nav-link" href="./menu.php">Меню</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="./index.php">Главная</a>
                </li>
              </ul>
              <form class="d-flex">
                <input class="form-control me-2 search-form" type="search" placeholder="Поиск" aria-label="Search">
                <button class="btn btn-search" type="submit">Найти</button>
              </form>
              <a class="btn btn-primary" href="./cart.php" role="button">Корзина</a>
            </div>
          </nav>
        </div>
      </div> 
    </header>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header login-header">
                        <h3 class="text-center login-text">Войти или зарегистрироваться</h3>
                    </div>
                    <div class="card-body login-card">
                        <form id="login-form" method="POST">
                          <?php if (!empty($login_errors)): ?>
                              <div class="alert alert-danger">
                                  <?php foreach ($login_errors as $error): ?>
                                      <div><?= htmlspecialchars($error) ?></div>
                                  <?php endforeach; ?>
                              </div>
                          <?php endif; ?>
                          
                          <?php if (!empty($login_success)): ?>
                              <div class="alert alert-success"><?= htmlspecialchars($login_success) ?></div>
                          <?php endif; ?>

                          <div class="mb-3">
                              <label for="email" class="form-label">Email адрес</label>
                              <input type="email" class="form-control" id="email" name="email" required>
                          </div>
                          <div class="mb-3">
                              <label for="password" class="form-label">Пароль</label>
                              <input type="password" class="form-control" id="password" name="password" required>
                          </div>
                          <button type="submit" class="btn btn-primary w-100">Продолжить</button>
                      </form>
                        <div class="text-center mt-3">
                            <p>Нет аккаунта? <a href="./register.php">Зарегистрироваться</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src='main.js'></script>

  <footer class="footer mt-auto py-3 bg-dark text-white">
    <div class="container">
      <div class="row">
        <div class="col-md-4 text-center">
          <p>Email: <a href="mailto:tubatay.feedback@gmail.com" class="text-white">tubatay.feedback@gmail.com</a></p>
        </div>
        <div class="col-md-4 text-center">
          <p>&copy; <span id="current-year"></span> Тюбетей</p>
        </div>
        <div class="col-md-4 text-center">
          <p>Телефон: <a href="tel:+78432494141" class="text-white">+7(843)249-41-41</a></p>
        </div>
      </div>
    </div>
  </footer>
  
</body>
</html>