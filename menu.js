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
    document.querySelectorAll('.rating').forEach(rating => {
        const stars = rating.querySelectorAll('.star');
        const itemId = rating.dataset.productId;
        const currentRating = rating.parentElement.querySelector('.current-rating');

        stars.forEach(star => {
            star.addEventListener('click', async function() {
                const value = parseInt(star.dataset.value);
                
                try {
                    const response = await fetch('save_rating.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `item_id=${itemId}&rating=${value}`,
                        credentials: 'include'
                    });
                    
                    if (!response.ok) throw new Error('Войдите или зарегистрируйтесь');
                    
                    const data = await response.json();
                    
                    // Обновляем средний рейтинг
                    currentRating.textContent = data.average.toFixed(1);
                    
                    // Подсвечиваем звезды пользователя
                    stars.forEach(s => {
                        s.classList.toggle('active', s.dataset.value <= value);
                    });
                    
                } catch (error) {
                    console.error('Ошибка:', error);
                    alert(error);
                }
            });
        });
    });
});

document.querySelectorAll('.card-btn').forEach(button => {
  button.addEventListener('click', function(event) {
    event.preventDefault();
    addToCart(this);
    const modal = new bootstrap.Modal(document.getElementById('cartModal'));
    modal.show();
  });
});

// Обновленная функция addToCart
async function addToCart(button) {
  const productId = button.getAttribute('data-item-id');
  const card = button.closest('.card');
  const productName = card.querySelector('h4').innerText;
  const productPrice = card.querySelector('p').innerText;
  const productImage = card.querySelector('img').src;

  const product = {
    id: productId,
    name: productName,
    price: productPrice,
    image: productImage,
    quantity: 1
  };

  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  const existingProduct = cart.find(item => item.id === productId);
  
  if (existingProduct) {
    existingProduct.quantity += 1;
  } else {
    cart.push(product);
  }

  localStorage.setItem('cart', JSON.stringify(cart));

  // Синхронизация с БД для авторизованных пользователей
  try {
    const response = await fetch('add_to_cart.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ item_id: productId, quantity: 1 }),
      credentials: 'include'
    });

    if (!response.ok) throw new Error('Ошибка при добавлении в корзину');
  } catch (error) {
    console.error('Ошибка:', error);
    alert(error.message);
  }
}

document.addEventListener('DOMContentLoaded', function() {
    // Удаление товара
    document.querySelectorAll('.remove-item').forEach(button => {
        button.addEventListener('click', async function() {
            const itemId = this.dataset.itemId;
            const confirmDelete = confirm('Вы уверены, что хотите удалить этот товар из корзины?');
            
            if (confirmDelete) {
                try {
                    const response = await fetch('remove_from_cart.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ item_id: itemId }),
                        credentials: 'include'
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                        itemElement.remove();
                        await updateTotalPrice();
                        alert('Товар удален');
                    } else {
                        showModal(data.error || 'Ошибка при удалении');
                    }
                } catch (error) {
                    console.error('Ошибка:', error);
                    alert('Произошла ошибка');
                }
            }
        });
    });

    // Очистка корзины
    document.getElementById('clear-cart').addEventListener('click', async function() {
        const confirmClear = confirm('Вы уверены, что хотите очистить корзину?');
        if (confirmClear) {
            try {
                const response = await fetch('clear_cart.php', {
                    method: 'POST',
                    credentials: 'include'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('cart-items').innerHTML = '';
                    await updateTotalPrice();
                    alert('Корзина очищена');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                alert('Ошибка при очистке');
            }
        }
    });
});

// Остальные функции без изменений

// Обновленная функция для обновления общей суммы
async function updateTotalPrice() {
    try {
        const response = await fetch('get_total.php');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('total-price').textContent = 
                parseFloat(data.total).toFixed(2);
        }
    } catch (error) {
        console.error('Ошибка обновления суммы:', error);
    }
}

// Функция для показа модального окна
function showModal(message) {
    const modal = new bootstrap.Modal(document.getElementById('cartDModal'));
    const modalBody = document.querySelector('#cartDModal .modal-body');
    modalBody.textContent = message;
    modal.show();
}