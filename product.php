<?php
require_once 'config.php';

$productId = (int)($_GET['id'] ?? 0);

if ($productId <= 0) {
    redirect('index.php');
}

// Отримуємо товар з БД
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    redirect('index.php');
}

// Отримуємо схожі товари
$stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE category_id = ? AND id != ? 
    LIMIT 4
");
$stmt->execute([$product['category_id'], $productId]);
$relatedProducts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($product['name']) ?> - <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
                <img src="assets/images/logo.png" alt="<?= SITE_NAME ?>" style="height: 40px; margin-right: 12px;">
                <div>
                    <span style="font-size: 1.3rem;"><?= SITE_NAME ?></span>
                    <small class="d-block" style="font-size: 0.65rem; opacity: 0.75; margin-top: -3px;"><?= SITE_SLOGAN ?></small>
                </div>
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
                                <i class="bi bi-box-arrow-right"></i> Вихід (<?= e($_SESSION['user_name']) ?>)
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

    <!-- Breadcrumb -->
    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Головна</a></li>
                <li class="breadcrumb-item"><a href="index.php"><?= e($product['category_name']) ?></a></li>
                <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
            </ol>
        </nav>
    </div>

    <!-- Product Detail -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <!-- Product Image -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <img src="uploads/<?= e($product['image']) ?>" 
                             class="card-img-top product-detail-image p-3" 
                             alt="<?= e($product['name']) ?>"
                             onerror="this.src='https://via.placeholder.com/600x400?text=<?= urlencode($product['name']) ?>'">
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-md-6">
                    <span class="badge bg-dark mb-2"><?= e($product['category_name']) ?></span>
                    <h1 class="display-5 mb-3"><?= e($product['name']) ?></h1>
                    
                    <div class="mb-4">
                        <span class="h2 text-warning"><?= number_format($product['price'], 2) ?> ₴</span>
                    </div>

                    <div class="mb-4">
                        <?php if ($product['stock'] > 0): ?>
                            <p class="text-success mb-2">
                                <i class="bi bi-check-circle-fill"></i> 
                                <strong>В наявності</strong> (<?= $product['stock'] ?> шт.)
                            </p>
                        <?php else: ?>
                            <p class="text-danger mb-2">
                                <i class="bi bi-x-circle-fill"></i> 
                                <strong>Немає в наявності</strong>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <h5>Опис:</h5>
                        <p class="text-muted"><?= nl2br(e($product['description'])) ?></p>
                    </div>

                    <?php if ($product['stock'] > 0): ?>
                        <div class="mb-4">
                            <label class="form-label">Кількість:</label>
                            <div class="input-group" style="max-width: 150px;">
                                <button class="btn btn-outline-secondary" type="button" id="decreaseQty">-</button>
                                <input type="number" class="form-control text-center" value="1" min="1" max="<?= $product['stock'] ?>" id="quantity">
                                <button class="btn btn-outline-secondary" type="button" id="increaseQty">+</button>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button class="btn btn-warning btn-lg" id="addToCartBtn" data-id="<?= $product['id'] ?>">
                                <i class="bi bi-cart-plus"></i> Додати до кошика
                            </button>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Повернутися до каталогу
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> 
                            Товар тимчасово відсутній. Спробуйте пізніше.
                        </div>
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Повернутися до каталогу
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Products -->
            <?php if (count($relatedProducts) > 0): ?>
                <div class="row mt-5">
                    <div class="col-12">
                        <h3 class="mb-4">Схожі товари</h3>
                    </div>
                    <?php foreach ($relatedProducts as $related): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="card h-100 shadow-sm">
                                <img src="uploads/<?= e($related['image']) ?>" 
                                     class="card-img-top" 
                                     style="height: 200px; object-fit: cover;"
                                     alt="<?= e($related['name']) ?>"
                                     onerror="this.src='https://via.placeholder.com/300x200?text=<?= urlencode($related['name']) ?>'">
                                <div class="card-body">
                                    <h5 class="card-title"><?= e($related['name']) ?></h5>
                                    <p class="text-warning fw-bold"><?= number_format($related['price'], 2) ?> ₴</p>
                                    <a href="product.php?id=<?= $related['id'] ?>" class="btn btn-outline-dark btn-sm">
                                        Переглянути
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <img src="assets/images/logo.png" alt="Logo" style="height: 30px; margin-right: 10px;">
                        <h5 class="mb-0"><?= SITE_NAME ?></h5>
                    </div>
                    <p class="text-muted small"><?= SITE_SLOGAN ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-1 small">© 2024 <?= SITE_NAME ?>. Всі права захищені</p>
                    <p class="text-muted small">Курсова робота з дисципліни "Веб-технології"</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Зміна кількості
        const qtyInput = document.getElementById('quantity');
        const maxQty = parseInt(qtyInput.max);

        document.getElementById('decreaseQty').addEventListener('click', () => {
            if (qtyInput.value > 1) {
                qtyInput.value = parseInt(qtyInput.value) - 1;
            }
        });

        document.getElementById('increaseQty').addEventListener('click', () => {
            if (qtyInput.value < maxQty) {
                qtyInput.value = parseInt(qtyInput.value) + 1;
            }
        });

        // Додавання до кошика
        document.getElementById('addToCartBtn')?.addEventListener('click', function() {
            const productId = this.dataset.id;
            const quantity = qtyInput.value;

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
                    alert('Товар додано до кошика!');
                    // Оновлюємо бейдж
                    const badge = document.querySelector('.nav-link .badge');
                    if (badge) {
                        badge.textContent = data.cartCount;
                    }
                } else {
                    alert(data.message || 'Помилка додавання товару');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Помилка з\'єднання');
            });
        });
    </script>
</body>
</html>