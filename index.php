<?php
require_once 'config.php';

// Отримуємо всі товари з БД
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();

// Отримуємо категорії для фільтру
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Інтернет-магазин - Головна</title>
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

    <!-- Hero Section -->
    <section class="bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold">Вітаємо в нашому магазині!</h1>
                    <p class="lead">Знайдіть все необхідне за найкращими цінами</p>
                    <a href="#products" class="btn btn-primary btn-lg">Переглянути товари</a>
                </div>
                <div class="col-lg-6">
                    <img src="https://via.placeholder.com/600x400?text=Інтернет-магазин" alt="Shopping" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-5" id="products">
        <div class="container">
            <h2 class="text-center mb-4">Наші товари</h2>
            
            <!-- Filter -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-filter="all">Всі</button>
                        <?php foreach ($categories as $cat): ?>
                            <button type="button" class="btn btn-outline-primary" data-filter="<?= $cat['id'] ?>">
                                <?= e($cat['name']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="row g-4">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-6 col-lg-4 col-xl-3 product-item" data-category="<?= $product['category_id'] ?>">
                        <div class="card h-100 shadow-sm">
                            <img src="https://via.placeholder.com/300x200?text=<?= urlencode($product['name']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= e($product['name']) ?>"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <span class="badge bg-secondary mb-2 align-self-start">
                                    <?= e($product['category_name']) ?>
                                </span>
                                <h5 class="card-title"><?= e($product['name']) ?></h5>
                                <p class="card-text text-muted small">
                                    <?= e(substr($product['description'], 0, 80)) ?>...
                                </p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="h5 mb-0 text-primary"><?= number_format($product['price'], 2) ?> ₴</span>
                                        <span class="text-muted small">
                                            <?php if ($product['stock'] > 0): ?>
                                                <i class="bi bi-check-circle text-success"></i> В наявності
                                            <?php else: ?>
                                                <i class="bi bi-x-circle text-danger"></i> Немає
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> Переглянути
                                        </a>
                                        <?php if ($product['stock'] > 0): ?>
                                            <button class="btn btn-primary btn-sm add-to-cart" data-id="<?= $product['id'] ?>">
                                                <i class="bi bi-cart-plus"></i> До кошика
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                Немає в наявності
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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
                    <p class="text-muted">Курсова робота з дисципліни "Веб-технології"</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>