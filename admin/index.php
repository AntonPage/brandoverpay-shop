<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Статистика
$stats = [];
$stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['total_products'] = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$stats['pending_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$stats['total_revenue'] = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();

// Останні замовлення
$recent_orders = $pdo->query("
    SELECT o.*, u.full_name, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll();

$statusLabels = [
    'pending' => 'Очікує',
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
    <title>Адмін-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main content -->
        <div class="flex-grow-1 p-4 bg-light">
            <h1 class="mb-4"><i class="bi bi-speedometer2"></i> Dashboard</h1>

            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Користувачі</h6>
                                    <h2 class="mb-0"><?= $stats['total_users'] ?></h2>
                                </div>
                                <i class="bi bi-people" style="font-size: 2rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Товари</h6>
                                    <h2 class="mb-0"><?= $stats['total_products'] ?></h2>
                                </div>
                                <i class="bi bi-box-seam" style="font-size: 2rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Замовлення</h6>
                                    <h2 class="mb-0"><?= $stats['total_orders'] ?></h2>
                                    <small>Нових: <?= $stats['pending_orders'] ?></small>
                                </div>
                                <i class="bi bi-cart" style="font-size: 2rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Виручка</h6>
                                    <h2 class="mb-0"><?= number_format($stats['total_revenue'], 0) ?> ₴</h2>
                                </div>
                                <i class="bi bi-cash" style="font-size: 2rem; opacity: 0.3;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Останні замовлення</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_orders)): ?>
                        <p class="text-muted text-center py-3">Замовлень ще немає</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Клієнт</th>
                                        <th>Email</th>
                                        <th>Сума</th>
                                        <th>Статус</th>
                                        <th>Дата</th>
                                        <th>Дії</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><strong>#<?= $order['id'] ?></strong></td>
                                            <td><?= e($order['full_name']) ?></td>
                                            <td><?= e($order['email']) ?></td>
                                            <td><strong><?= number_format($order['total_amount'], 2) ?> ₴</strong></td>
                                            <td>
                                                <span class="badge bg-<?= $statusColors[$order['status']] ?>">
                                                    <?= $statusLabels[$order['status']] ?>
                                                </span>
                                            </td>
                                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <a href="orders.php" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="orders.php" class="btn btn-outline-primary">
                                Переглянути всі замовлення <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>