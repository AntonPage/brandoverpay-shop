// Додавання товару до кошика
document.addEventListener('DOMContentLoaded', function() {
    // Додавання до кошика
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.id;
            addToCart(productId);
        });
    });

    // Фільтрація товарів
    const filterButtons = document.querySelectorAll('[data-filter]');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            
            // Активна кнопка
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Фільтрація
            const products = document.querySelectorAll('.product-item');
            products.forEach(product => {
                if (filter === 'all' || product.dataset.category === filter) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        });
    });
});

// Функція додавання до кошика
function addToCart(productId, quantity = 1) {
    fetch('cart-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Товар додано до кошика!', 'success');
            updateCartBadge(data.cartCount);
        } else {
            showNotification(data.message || 'Помилка додавання товару', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Помилка з\'єднання', 'danger');
    });
}

// Оновлення бейджа кошика
function updateCartBadge(count) {
    const badge = document.querySelector('.nav-link .badge');
    if (badge) {
        badge.textContent = count;
    } else if (count > 0) {
        const cartLink = document.querySelector('.nav-link[href="cart.php"]');
        const newBadge = document.createElement('span');
        newBadge.className = 'badge bg-danger ms-1';
        newBadge.textContent = count;
        cartLink.appendChild(newBadge);
    }
}

// Показ повідомлень
function showNotification(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Видалення з кошика
function removeFromCart(productId) {
    if (confirm('Видалити товар з кошика?')) {
        fetch('cart-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

// Оновлення кількості товару в кошику
function updateQuantity(productId, quantity) {
    if (quantity < 1) {
        removeFromCart(productId);
        return;
    }
    
    fetch('cart-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update&product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Помилка оновлення');
        }
    });
}

// Очищення кошика
function clearCart() {
    if (confirm('Очистити кошик повністю?')) {
        fetch('cart-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=clear'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}