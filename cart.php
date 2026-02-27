<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        c.item_id,
        c.quantity,
        m.name,
        m.price,
        m.image_url,
        getCartTotal(:userId) as total
    FROM cart c
    JOIN menu_items m ON c.item_id = m.item_id
    WHERE c.user_id = :userId
");
$stmt->execute(['userId' => $userId]);
$cartData = $stmt->fetchAll();

$totalPrice = $cartData[0]['total'] ?? 0;
$cartItems = $cartData;
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="stylecart.css">
    <title>Корзина</title>
    <style>
        .modal-backdrop {
            z-index: 1040 !important;
        }
        .modal {
            z-index: 1050 !important;
        }
    </style>
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
                <li class="nav-item">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a class="nav-link" href="logout.php">Выйти</a>
                    <?php else: ?>
                        <a class="nav-link" href="./login.php">Войти</a>
                    <?php endif; ?>
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

    <div class="container mt-4">
      <h1>Ваша корзина</h1>
      <div id="cart-items" class="cart-items">
        <?php foreach ($cartItems as $item): ?>
        <div class="cart-item mb-3 p-3 border" data-item-id="<?= $item['item_id'] ?>">
            <div class="row">
                <div class="col-md-2">
                    <img src="<?= $item['image_url'] ?>" class="img-fluid">
                </div>
                <div class="col-md-6">
                    <h4><?= $item['name'] ?></h4>
                    <p>Цена: <?= $item['price'] ?> р.</p>
                    <p>Количество: <?= $item['quantity'] ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-danger remove-item" data-item-id="<?= $item['item_id'] ?>">
                        Удалить
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="total-price mt-4">
        <h3>Общая стоимость: <span id="total-price"><?= number_format($totalPrice, 2) ?></span> р.</h3>
    </div>
      <!-- <button id="clear-cart" class="btn btn-danger mt-3">Очистить корзину</button> -->
      <button id="order" class="btn btn-success mt-3">Оформить заказ</button>
    </div>
  </div>

  <div class="modal fade" id="phoneModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Введите номер телефона</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="phoneForm">
                        <div class="mb-3">
                            <label for="phoneInput" class="form-label">Номер телефона</label>
                            <input type="tel" class="form-control" id="phoneInput" 
                                   placeholder="+7(XXX)XXX-XX-XX" required>
                            <div class="form-text">Пример: +7(912)345-67-89</div>
                        </div>
                    </form>
                    <div id="errorMessage" class="alert alert-danger d-none mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" id="confirmOrder">Продолжить</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Уведомление об успехе -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto">Успешно!</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Заказ успешно оформлен!
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src='menu.js'></script>

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
    
    <script>
        // Обработчик кнопки оформления заказа
        document.getElementById('order').addEventListener('click', function() {
            // Проверка что корзина не пуста
            <?php if(empty($cartItems)): ?>
                alert('Ваша корзина пуста!');
            <?php else: ?>
                const modal = new bootstrap.Modal(document.getElementById('phoneModal'));
                modal.show();
            <?php endif; ?>
        });

        document.getElementById('confirmOrder').addEventListener('click', async function() {
    const phoneInput = document.getElementById('phoneInput');
    const errorMessage = document.getElementById('errorMessage');
    const confirmBtn = this;
    
    // Сброс ошибок
    errorMessage.classList.add('d-none');
    confirmBtn.disabled = true;
    
    // Простая валидация
    const phone = phoneInput.value.trim();

    try {
        const response = await fetch('create_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ phone })
        });
        
        // Проверяем Content-Type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Сервер вернул: ${text.slice(0, 100)}`);
        }

        const result = await response.json();

        if (response.ok && result.success) {
            // Успешное создание заказа
            const toast = new bootstrap.Toast(document.getElementById('successToast'));
            toast.show();
            
            // Закрываем модальное окно
            bootstrap.Modal.getInstance(document.getElementById('phoneModal')).hide();
            
            // Обновляем страницу через 2 секунды
            setTimeout(() => window.location.reload(), 2000);
        } else {
            // Ошибка от сервера
            showError(result.error || 'Неизвестная ошибка');
        }
    } catch (error) {
        showError(`Ошибка: ${error.message}`);
    } finally {
        confirmBtn.disabled = false;
    }
});

function showError(message) {
    const errorMessage = document.getElementById('errorMessage');
    errorMessage.textContent = message;
    errorMessage.classList.remove('d-none');
}
    </script>
</body>
</html>