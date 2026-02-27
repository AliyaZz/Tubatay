<?php
session_start();
require_once 'db.php'; ?>
<?php

// Получаем все товары
$stmt = $pdo->query("SELECT * FROM menu_items");
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="stylemenu.css">
    <title>Меню</title>
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

    <div class="container">
      <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
              Сортировка
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <li><a class="dropdown-item" href="#" data-sort="name-asc">По алфавиту (А-Я)</a></li>
              <li><a class="dropdown-item" href="#" data-sort="name-desc">По алфавиту (Я-А)</a></li>
              <li><a class="dropdown-item" href="#" data-sort="price-asc">По цене (по возрастанию)</a></li>
              <li><a class="dropdown-item" href="#" data-sort="price-desc">По цене (по убыванию)</a></li>
            </ul>
          </div>
      
          <div class="title-container text-center flex-grow-1">
            <h1>Меню</h1>
          </div>
        </div>
      </div>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <?php foreach ($items as $item):
                // Получаем средний рейтинг
                $avgStmt = $pdo->prepare("SELECT COALESCE(AVG(rating), 0) FROM ratings WHERE item_id = ?");
                $avgStmt->execute([$item['item_id']]);
                $avgRating = round($avgStmt->fetchColumn(), 1);
                
                // Получаем оценку пользователя (если авторизован)
                $userRating = 0;
                if (isset($_SESSION['user_id'])) {
                    $userStmt = $pdo->prepare("SELECT rating FROM ratings WHERE user_id = ? AND item_id = ?");
                    $userStmt->execute([$_SESSION['user_id'], $item['item_id']]);
                    $userRating = $userStmt->fetchColumn() ?? 0;
                }
            ?>
            <div class="col">
                <div class="card shadow-sm">
                    <img class="bd-placeholder-img card-img-top" src="<?= $item['image_url'] ?>">
                    <div class="card-title-text">
                        <h4><?= $item['name'] ?></h4>
                    </div>
                    <p><?= $item['price'] ?> р.</p>
                    <button class="btn btn-sm btn-primary card-btn" data-item-id="<?= $item['item_id'] ?>">Добавить в корзину</button>
                    <div class="rating" data-product-id="<?= $item['item_id'] ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?= $i <= $userRating ? 'active' : '' ?>" 
                                  data-value="<?= $i ?>">&#9733;</span>
                        <?php endfor; ?>
                    </div>
                    <p>Текущий рейтинг: <span class="current-rating"><?= $avgRating ?></span></p>
                </div>
            </div>
            <?php endforeach; ?>
    </div>
</div>
        </div>
      </div>
    </div>

    <div id="home" class="tab-pane fade">
      <h3>Главная</h3>
        <p>В этой секции можно добавить информацию о кафе, такие как история, атмосфера и т.д.</p>
    </div>
    <div id="about" class="tab-pane fade">
      <h3>О нас</h3>
      <p>Кафе Тюбетей - это уютное место для отдыха и приятного времяпрепровождения. Мы предлагаем широкий выбор блюд и напитков, созданные с любовью нашими кулинарами.</p>
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

  <div class="modal fade" id="cartModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        Товар добавлен в корзину!
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>