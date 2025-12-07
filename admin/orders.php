<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Зміна статусу
if (isset($_POST['change_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $allowed = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($status, $allowed)) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $order_id]);
    }
    redirect('orders.php?msg=updated');
}

$orders = $pdo->query("
    SELECT o.*, u.full_name, u.email,
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
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
    <title>Замовлення - Адмін</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div class="flex-grow-1 p-4 bg-light">
            <h1 class="mb-4"><i class="bi bi-cart-check"></i> Замовлення</h1>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible">
                    Статус оновлено!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <?php if (empty($orders)): ?>
                        <p class="text-muted text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i><br>
                            Замовлень ще немає
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Клієнт</th>
                                        <th>Email</th>
                                        <th>Товарів</th>
                                        <th>Сума</th>
                                        <th>Статус</th>
                                        <th>Дата</th>
                                        <th>Дії</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td><strong>#<?= $order['id'] ?></strong></td>
                                            <td><?= e($order['full_name']) ?></td>
                                            <td><?= e($order['email']) ?></td>
                                            <td><?= $order['items_count'] ?></td>
                                            <td><strong><?= number_format($order['total_amount'], 2) ?> ₴</strong></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                    <select name="status" class="form-select form-select-sm" 
                                                            style="width:auto;" 
                                                            onchange="if(confirm('Змінити статус?')) this.form.submit();">
                                                        <?php foreach ($statusLabels as $key => $label): ?>
                                                            <option value="<?= $key ?>" 
                                                                <?= $order['status'] == $key ? 'selected' : '' ?>>
                                                                <?= $label ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <input type="hidden" name="change_status" value="1">
                                                </form>
                                            </td>
                                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#orderModal<?= $order['id'] ?>">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Modal деталей замовлення -->
                                        <div class="modal fade" id="orderModal<?= $order['id'] ?>">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5>Замовлення #<?= $order['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6><i class="bi bi-person"></i> Клієнт:</h6>
                                                                <p class="ms-3">
                                                                    <strong><?= e($order['full_name']) ?></strong><br>
                                                                    <?= e($order['email']) ?><br>
                                                                    <?= e($order['phone']) ?>
                                                                </p>

                                                                <h6><i class="bi bi-geo-alt"></i> Адреса доставки:</h6>
                                                                <p class="ms-3"><?= nl2br(e($order['delivery_address'])) ?></p>

                                                                <?php if ($order['notes']): ?>
                                                                    <h6><i class="bi bi-chat-left-text"></i> Примітки:</h6>
                                                                    <p class="ms-3"><?= nl2br(e($order['notes'])) ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                            
                                                            <div class="col-md-6">
                                                                <h6><i class="bi bi-box-seam"></i> Товари:</h6>
                                                                <?php
                                                                $items = $pdo->prepare("
                                                                    SELECT oi.*, p.name 
                                                                    FROM order_items oi
                                                                    LEFT JOIN products p ON oi.product_id = p.id
                                                                    WHERE oi.order_id = ?
                                                                ");
                                                                $items->execute([$order['id']]);
                                                                $items = $items->fetchAll();
                                                                ?>
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Товар</th>
                                                                            <th>Кіл-ть</th>
                                                                            <th>Ціна</th>
                                                                            <th>Сума</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php foreach ($items as $item): ?>
                                                                            <tr>
                                                                                <td><?= e($item['name']) ?></td>
                                                                                <td><?= $item['quantity'] ?></td>
                                                                                <td><?= number_format($item['price'], 2) ?> ₴</td>
                                                                                <td><strong><?= number_format($item['quantity'] * $item['price'], 2) ?> ₴</strong></td>
                                                                            </tr>
                                                                        <?php endforeach; ?>
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <th colspan="3">Разом:</th>
                                                                            <th><?= number_format($order['total_amount'], 2) ?> ₴</th>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрити</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>