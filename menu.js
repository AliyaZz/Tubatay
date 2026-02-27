function searchDishes(searchText) {
    const cards = document.querySelectorAll('.col');

    cards.forEach(card => {
      const dishName = card.querySelector('h4').textContent.toLowerCase();
      if (dishName.includes(searchText.toLowerCase())) {
        card.style.display = ''; // Показываем карточку, если название совпадает
      } else {
        card.style.display = 'none'; // Скрываем карточку, если название не совпадает
      }
    });
  }

  // Функция для обработки поиска на текущей странице
  function handleSearch(event) {
    event.preventDefault(); // Предотвращаем отправку формы

    // Получаем значение поискового запроса
    const searchText = document.querySelector('.search-form').value.trim();

    // Проверяем, что запрос не пустой
    if (searchText) {
      // Выполняем поиск
      searchDishes(searchText);
    } else {
      // Если запрос пустой, показываем все карточки
      const cards = document.querySelectorAll('.col');
      cards.forEach(card => {
        card.style.display = '';
      });
    }
  }

  // Добавляем обработчик события для формы поиска
  const searchForm = document.querySelector('.d-flex');
  if (searchForm) {
    searchForm.addEventListener('submit', handleSearch);
  } else {
    console.error('Форма поиска не найдена!'); // Отладка
  }

  // Проверяем, есть ли сохраненный поисковый запрос в localStorage
  const searchQuery = localStorage.getItem('searchQuery');

  if (searchQuery) {
    console.log('Найден поисковый запрос:', searchQuery); // Отладка

    // Выполняем поиск
    searchDishes(searchQuery);

    // Очищаем запрос в localStorage после выполнения поиска
    localStorage.removeItem('searchQuery');
    console.log('Поисковый запрос удален из localStorage'); // Отладка
  } else {
    console.log('Поисковый запрос не найден в localStorage'); // Отладка
  }




  function sortCards(sortType) {
    const container = document.querySelector('.row.row-cols-1.row-cols-sm-2.row-cols-md-3.g-3');
    const cards = Array.from(container.querySelectorAll('.col'));

    cards.sort((a, b) => {
      const nameA = a.querySelector('h4').textContent.toLowerCase();
      const nameB = b.querySelector('h4').textContent.toLowerCase();
      const priceA = parseFloat(a.querySelector('p').textContent.match(/\d+/)[0]);
      const priceB = parseFloat(b.querySelector('p').textContent.match(/\d+/)[0]);

      switch (sortType) {
        case 'name-asc':
          return nameA.localeCompare(nameB);
        case 'name-desc':
          return nameB.localeCompare(nameA);
        case 'price-asc':
          return priceA - priceB;
        case 'price-desc':
          return priceB - priceA;
        default:
          return 0;
      }
    });

    // Очищаем контейнер и добавляем отсортированные карточки
    container.innerHTML = '';
    cards.forEach(card => container.appendChild(card));
  }

  // Добавляем обработчики событий для выпадающего списка
  document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', (event) => {
      event.preventDefault();
      const sortType = event.target.getAttribute('data-sort');
      sortCards(sortType);
    });
  });

  

document.addEventListener('DOMContentLoaded', function() {
    const ratings = document.querySelectorAll('.rating');

    ratings.forEach(rating => {
        const stars = rating.querySelectorAll('.star');
        const currentRating = rating.parentElement.querySelector('.current-rating');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = parseInt(star.getAttribute('data-value'));
                setRating(stars, value, currentRating);
            });
        });
    });

    function setRating(stars, value, currentRatingElement) {
        stars.forEach(star => {
            const starValue = parseInt(star.getAttribute('data-value'));
            if (starValue <= value) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
        currentRatingElement.textContent = value;
    }
});


// menu.js

function addToCart(button) {
  // Находим родительский элемент карточки товара
  const card = button.closest('.card');
  
  // Извлекаем данные о товаре
  const productName = card.querySelector('h4').innerText;
  const productPrice = card.querySelector('p').innerText;
  const productImage = card.querySelector('img').src;
  const productId = card.querySelector('.rating').getAttribute('data-product-id');

  // Создаем объект товара
  const product = {
      id: productId,
      name: productName,
      price: productPrice,
      image: productImage,
      quantity: 1
  };

  // Получаем текущую корзину из localStorage
  let cart = JSON.parse(localStorage.getItem('cart')) || [];

  // Проверяем, есть ли уже такой товар в корзине
  const existingProduct = cart.find(item => item.id === productId);
  if (existingProduct) {
      existingProduct.quantity += 1; // Увеличиваем количество, если товар уже в корзине
  } else {
      cart.push(product); // Добавляем новый товар в корзину
  }

  // Сохраняем обновленную корзину в localStorage
  localStorage.setItem('cart', JSON.stringify(cart));

  // // Опционально: можно показать уведомление о добавлении товара
  // postMessage('Товар добавлен в корзину!');
}