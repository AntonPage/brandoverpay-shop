<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Отримуємо всі замовлення користувача
$stmt = $pdo->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Статуси українською
$statusLabels = [
    'pending' => 'Очікує обробки',
    'processing' => 'В обробці',
    'completed' => 'Завершено',
    'cancelled' => 'Скасовано'
];

$statusColors = [
    'pending' => 'warning',
    'processing' => 'info',
    'completed' => 'success',
    'cancelled' => 'danger'
];
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мої замовлення - Інтернет-магазин</title>
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

    <!-- Orders -->
    <section class="py-5">
        <div class="container">
            <h1 class="mb-4"><i class="bi bi-list-ul"></i> Мої замовлення</h1>

            <?php if (empty($orders)): ?>
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">У вас ще немає замовлень</h4>
                    <p class="text-muted">Оформіть своє перше замовлення прямо зараз!</p>
                    <a href="index.php" class="btn btn-primary mt-3">
                        <i class="bi bi-shop"></i> До каталогу
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($orders as $order): ?>
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <small class="text-muted">Замовлення</small>
                                            <h5 class="mb-0">#<?= $order['id'] ?></h5>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <small class="text-muted">Дата</small>
                                            <p class="mb-0"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <small class="text-muted">Товарів</small>
                                            <p class="mb-0"><?= $order['items_count'] ?> шт.</p>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <small class="text-muted">Сума</small>
                                            <p class="mb-0 fw-bold"><?= number_format($order['total_amount'], 2) ?> ₴</p>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <small class="text-muted">Статус</small>
                                            <p class="mb-0">
                                                <span class="badge bg-<?= $statusColors[$order['status']] ?>">
                                                    <?= $statusLabels[$order['status']] ?>
                                                </span>
                                            </p>
                                        </div>
                                        
                                        <div class="col-md-2 text-end">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#order<?= $order['id'] ?>">
                                                <i class="bi bi-eye"></i> Деталі
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Деталі замовлення -->
                                    <div class="collapse mt-3" id="order<?= $order['id'] ?>">
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6>Адреса доставки:</h6>
                                                <p class="text-muted"><?= nl2br(e($order['delivery_address'])) ?></p>
                                                
                                                <h6>Телефон:</h6>
                                                <p class="text-muted"><?= e($order['phone']) ?></p>
                                                
                                                <?php if ($order['notes']): ?>
                                                    <h6>Примітки:</h6>
                                                    <p class="text-muted"><?= nl2br(e($order['notes'])) ?></p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <h6>Товари:</h6>
                                                <?php
                                                $itemsStmt = $pdo->prepare("
                                                    SELECT oi.*, p.name as product_name
                                                    FROM order_items oi
                                                    LEFT JOIN products p ON oi.product_id = p.id
                                                    WHERE oi.order_id = ?
                                                ");
                                                $itemsStmt->execute([$order['id']]);
                                                $items = $itemsStmt->fetchAll();
                                                ?>
                                                
                                                <ul class="list-unstyled">
                                                    <?php foreach ($items as $item): ?>
                                                        <li class="mb-2">
                                                            <strong><?= e($item['product_name']) ?></strong><br>
                                                            <small class="text-muted">
                                                                <?= $item['quantity'] ?> × <?= number_format($item['price'], 2) ?> ₴ = 
                                                                <?= number_format($item['quantity'] * $item['price'], 2) ?> ₴
                                                            </small>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
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
</body>
</html>