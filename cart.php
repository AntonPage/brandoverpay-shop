<?php
require_once 'config.php';

// Отримуємо товари з кошика
$cartItems = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $subtotal = $product['price'] * $quantity;
        $total += $subtotal;
        
        $cartItems[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кошик - Інтернет-магазин</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= SITE_URL ?>/index.php">
            <i class="bi bi-shop-window"></i> <?= SITE_NAME ?>
            <small class="d-block" style="font-size: 0.6rem; opacity: 0.7;"><?= SITE_SLOGAN ?></small>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house"></i> Головна
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="bi bi-cart3"></i> Кошик
                        <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="badge bg-danger"><?= count($_SESSION['cart']) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="bi bi-list-ul"></i> Замовлення
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-warning" href="admin/index.php">
                                <i class="bi bi-gear"></i> Адмін
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Вихід
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Вхід
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="bi bi-person-plus"></i> Реєстрація
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

    <!-- Cart Content -->
    <section class="py-5">
        <div class="container">
            <h1 class="mb-4"><i class="bi bi-cart3"></i> Кошик</h1>

            <?php if (empty($cartItems)): ?>
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-cart-x" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">Ваш кошик порожній</h4>
                    <p class="text-muted">Додайте товари до кошика, щоб продовжити покупки</p>
                    <a href="index.php" class="btn btn-primary mt-3">
                        <i class="bi bi-shop"></i> Перейти до каталогу
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <!-- Cart Items -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="cart-item">
                                        <div class="row align-items-center">
                                            <!-- Image -->
                                            <div class="col-md-2">
                                                <img src="uploads/<?= e($item['product']['image']) ?>" 
                                                     class="img-fluid rounded"
                                                     alt="<?= e($item['product']['name']) ?>"
                                                     onerror="this.src='https://via.placeholder.com/100?text=Товар'">
                                            </div>
                                            
                                            <!-- Info -->
                                            <div class="col-md-4">
                                                <h5 class="mb-1">
                                                    <a href="product.php?id=<?= $item['product']['id'] ?>" class="text-decoration-none">
                                                        <?= e($item['product']['name']) ?>
                                                    </a>
                                                </h5>
                                                <p class="text-muted small mb-0">
                                                    Ціна: <?= number_format($item['product']['price'], 2) ?> ₴
                                                </p>
                                            </div>
                                            
                                            <!-- Quantity -->
                                            <div class="col-md-3">
                                                <div class="input-group input-group-sm">
                                                    <button class="btn btn-outline-secondary" type="button" 
                                                            onclick="updateQuantity(<?= $item['product']['id'] ?>, <?= $item['quantity'] - 1 ?>)">
                                                        <i class="bi bi-dash"></i>
                                                    </button>
                                                    <input type="text" class="form-control text-center" 
                                                           value="<?= $item['quantity'] ?>" readonly>
                                                    <button class="btn btn-outline-secondary" type="button"
                                                            onclick="updateQuantity(<?= $item['product']['id'] ?>, <?= $item['quantity'] + 1 ?>)">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Subtotal -->
                                            <div class="col-md-2 text-end">
                                                <p class="fw-bold mb-0"><?= number_format($item['subtotal'], 2) ?> ₴</p>
                                            </div>
                                            
                                            <!-- Remove -->
                                            <div class="col-md-1 text-end">
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="removeFromCart(<?= $item['product']['id'] ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Продовжити покупки
                            </a>
                            <button class="btn btn-outline-danger" onclick="clearCart()">
                                <i class="bi bi-trash"></i> Очистити кошик
                            </button>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm cart-summary">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Разом</h5>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Товарів:</span>
                                    <span><?= count($cartItems) ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Сума:</span>
                                    <span><?= number_format($total, 2) ?> ₴</span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Доставка:</span>
                                    <span class="text-success">Безкоштовно</span>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>До сплати:</strong>
                                    <strong class="text-primary h5"><?= number_format($total, 2) ?> ₴</strong>
                                </div>
                                
                                <?php if (isLoggedIn()): ?>
                                    <div class="d-grid">
                                        <a href="checkout.php" class="btn btn-primary btn-lg">
                                            <i class="bi bi-credit-card"></i> Оформити замовлення
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning small mb-3">
                                        <i class="bi bi-exclamation-triangle"></i> 
                                        Для оформлення замовлення необхідно увійти
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="login.php" class="btn btn-primary">
                                            <i class="bi bi-box-arrow-in-right"></i> Увійти
                                        </a>
                                        <a href="register.php" class="btn btn-outline-primary">
                                            <i class="bi bi-person-plus"></i> Зареєструватися
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Інтернет-магазин</h5>
                    <p class="text-muted">Якісні товари за доступними цінами</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">© 2024 Всі права захищені</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
                    alert(data.message || 'Помилка оновлення кількості');
                }
            });
        }

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
    </script>
</body>
</html>