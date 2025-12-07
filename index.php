<?php
require_once 'config.php';

$stmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - <?= SITE_SLOGAN ?></title>
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
                        <a class="nav-link active" href="index.php">
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

    <!-- Hero Section -->
    <section class="bg-dark text-white py-5" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/images/hero.jpg') center/cover; min-height: 400px;">
        <div class="container h-100">
            <div class="row align-items-center h-100 py-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-3 fw-bold mb-3"><?= SITE_NAME ?></h1>
                    <p class="lead mb-4" style="font-size: 1.5rem; font-weight: 300;"><?= SITE_SLOGAN ?></p>
                    <p class="mb-4" style="opacity: 0.9;">Найдорожчі бренди за найвищими цінами. Переплачуйте зі стилем! 💎</p>
                    <a href="#products" class="btn btn-warning btn-lg px-5 py-3 shadow">
                        <i class="bi bi-shop"></i> Переглянути товари
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="py-5 bg-light" id="products">
        <div class="container">
            <h2 class="text-center mb-4 display-6">Наші товари</h2>
            <p class="text-center text-muted mb-5">Тільки перевірені бренди з максимальною націнкою</p>
            
            <!-- Filter -->
            <div class="row mb-4">
                <div class="col-md-12 text-center">
                    <div class="btn-group shadow-sm" role="group">
                        <button type="button" class="btn btn-outline-dark active" data-filter="all">
                            <i class="bi bi-grid"></i> Всі
                        </button>
                        <?php foreach ($categories as $cat): ?>
                            <button type="button" class="btn btn-outline-dark" data-filter="<?= $cat['id'] ?>">
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
                        <div class="card h-100 shadow-sm border-0" style="transition: transform 0.3s;">
                            <img src="uploads/<?= e($product['image']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= e($product['name']) ?>"
                                 style="height: 220px; object-fit: cover;"
                                 onerror="this.src='https://via.placeholder.com/300x220/2c3e50/ecf0f1?text=<?= urlencode($product['name']) ?>'">
                            
                            <div class="card-body d-flex flex-column">
                                <span class="badge bg-dark mb-2 align-self-start">
                                    <?= e($product['category_name']) ?>
                                </span>
                                <h5 class="card-title"><?= e($product['name']) ?></h5>
                                <p class="card-text text-muted small flex-grow-1">
                                    <?= e(mb_substr($product['description'], 0, 70)) ?>...
                                </p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="h4 mb-0 text-warning fw-bold"><?= number_format($product['price'], 2) ?> ₴</span>
                                        <span class="text-muted small">
                                            <?php if ($product['stock'] > 0): ?>
                                                <i class="bi bi-check-circle-fill text-success"></i> Є в наявності
                                            <?php else: ?>
                                                <i class="bi bi-x-circle-fill text-danger"></i> Немає
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="product.php?id=<?= $product['id'] ?>" class="btn btn-outline-dark btn-sm">
                                            <i class="bi bi-eye"></i> Детальніше
                                        </a>
                                        <?php if ($product['stock'] > 0): ?>
                                            <button class="btn btn-warning btn-sm add-to-cart" data-id="<?= $product['id'] ?>">
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
    <script src="assets/js/main.js"></script>
    <script>
        // Hover ефект для карток
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px)';
                this.style.boxShadow = '0 10px 30px rgba(0,0,0,0.2)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '';
            });
        });
    </script>
</body>
</html>