function handleSearch(event) {
  event.preventDefault(); // Предотвращаем отправку формы

  // Получаем значение поискового запроса
  const searchText = document.querySelector('.search-form').value.trim();

  // Проверяем, что запрос не пустой
  if (searchText) {
    // Сохраняем поисковый запрос в localStorage
    localStorage.setItem('searchQuery', searchText);
    console.log('Поисковый запрос сохранен:', searchText); // Отладка

    // Перенаправляем пользователя на страницу меню
    window.location.href = './menu.html';
  } else {
    alert('Введите поисковый запрос!'); // Уведомление, если поле пустое
  }
}

// Добавляем обработчик события для формы поиска
const searchForm = document.querySelector('.d-flex');
if (searchForm) {
  searchForm.addEventListener('submit', handleSearch);
} else {
  console.error('Форма поиска не найдена!'); // Отладка
}


document.addEventListener('DOMContentLoaded', function () {
  const cartItemsContainer = document.getElementById('cart-items');
  const totalPriceElement = document.getElementById('total-price');
  const clearCartButton = document.getElementById('clear-cart');

  // Функция для отображения товаров в корзине
  function renderCart() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    cartItemsContainer.innerHTML = '';

    if (cart.length === 0) {
      cartItemsContainer.innerHTML = '<p class="carttext">Ваша корзина пуста.</p>';
      totalPriceElement.textContent = '0';
      return;
    }

    let totalPrice = 0;

    cart.forEach((item, index) => {
      const cartItem = document.createElement('div');
      cartItem.className = 'cart-item';

      // Извлекаем цену товара (убираем " р." и преобразуем в число)
      const price = parseFloat(item.price.replace(' р.', ''));

      // Считаем общую стоимость для этого товара
      const itemTotal = price * item.quantity;

      cartItem.innerHTML = `
        <img src="${item.image}" alt="${item.name}">
        <h4>${item.name}</h4>
        <div class="quantity-1">${item.price}</div>
        <div class="quantity">Количество: ${item.quantity}</div>
        <button class="remove-item" data-index="${index}">Удалить</button>
      `;
      cartItemsContainer.appendChild(cartItem);

      // Добавляем стоимость товара к общей сумме
      totalPrice += itemTotal;
    });

    // Обновляем общую стоимость
    totalPriceElement.textContent = totalPrice.toFixed(2);
  }

  // Функция для удаления товара из корзины
  function removeItemFromCart(index) {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    cart.splice(index, 1); // Удаляем товар по индексу
    localStorage.setItem('cart', JSON.stringify(cart));
    renderCart(); // Перерисовываем корзину
  }

  // Обработчик для кнопки удаления товара
  cartItemsContainer.addEventListener('click', function (event) {
    if (event.target.classList.contains('remove-item')) {
      const index = event.target.getAttribute('data-index');
      removeItemFromCart(index);
    }
  });

  // Обработчик для кнопки очистки корзины
  clearCartButton.addEventListener('click', function () {
    localStorage.removeItem('cart');
    renderCart();
  });

  // Инициализация корзины при загрузке страницы
  renderCart();
});


// Обработка формы входа
document.getElementById('login-form').addEventListener('submit', function (e) {
  e.preventDefault();
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  console.log('Вход:', { email, password });
  // Здесь можно добавить логику для отправки данных на сервер
});

// Обработка формы регистрации
document.getElementById('register-form').addEventListener('submit', function (e) {
  e.preventDefault();
  const name = document.getElementById('name').value;
  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;
  console.log('Регистрация:', { name, email, password });
  // Здесь можно добавить логику для отправки данных на сервер
});