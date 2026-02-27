<?php
session_start();
require_once 'db.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Данные из формы
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Проверка уникальности email
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception('Email уже зарегистрирован');
            }
            
            $addressId = 1;
            
            // Создание пользователя
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, adress_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $addressId]);
            
            $pdo->commit();
            $success = 'Регистрация успешна!';
        } catch (Exception $e) {
            $errorInfo = $e->errorInfo;
            
            // Берем только текст ошибки (третий элемент массива)
            $cleanError = $errorInfo[2] ?? $e->getMessage();
            
            // Удаляем префикс "1644 " если он есть
            if (strpos($cleanError, '1644 ') === 0) {
                $cleanError = substr($cleanError, 5);
            }
            
            $errors[] = $cleanError;
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
    <title>Регистрация</title>
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
                        <h3 class="text-center login-text">Регистрация</h3>
                    </div>
                    <div class="card-body login-card">
                        <form id="register-form" method="POST">
                          <?php if (!empty($success)): ?>
                              <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                          <?php endif; ?>
                          
                          <div class="mb-3">
                              <label for="name" class="form-label">Имя</label>
                              <input type="text" class="form-control" id="name" name="name" required>
                          </div>
                          <div class="mb-3">
                              <label for="email" class="form-label">Email адрес</label>
                              <input type="email" class="form-control" id="email" name="email" required>
                          </div>
                          <div class="mb-3">
                              <label for="password" class="form-label">Пароль</label>
                              <input type="password" class="form-control" id="password" name="password" required>
                          </div>
                          <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                      </form>
                        <div class="text-center mt-3">
                            <p>Уже есть аккаунт? <a href="./login.php">Войти</a></p>
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

  <!-- Модальное окно ошибок -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="errorModalLabel">Ошибка регистрации</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($errors)): ?>
          <?php foreach ($errors as $error): ?>
            <div class="text-danger mb-2"><?= htmlspecialchars($error) ?></div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($errors)): ?>
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
        errorModal.show();
    <?php endif; ?>
});
</script>

</body>
</html>