<?php
require_once 'config.php';

// Перевірка авторизації
if (!isLoggedIn()) {
    redirect('login.php');
}

// Перевірка кошика
if (empty($_SESSION['cart'])) {
    redirect('cart.php');
}

// Отримуємо товари з кошика
$ids = array_keys($_SESSION['cart']);
$placeholders = str_repeat('?,', count($ids) - 1) . '?';

$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cartItems = [];
$total = 0;

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

$error = '';
$success = false;

// Обробка форми
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_address = trim($_POST['delivery_address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    if (empty($delivery_address) || empty($phone)) {
        $error = 'Заповніть всі обов\'язкові поля';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Створюємо замовлення
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, delivery_address, phone, notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $total,
                $delivery_address,
                $phone,
                $notes
            ]);
            
            $orderId = $pdo->lastInsertId();
            
            // Додаємо товари до замовлення
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($cartItems as $item) {
                $stmt->execute([
                    $orderId,
                    $item['product']['id'],
                    $item['quantity'],
                    $item['product']['price']
                ]);
                
                // Зменшуємо кількість на складі
                $updateStmt = $pdo->prepare("
                    UPDATE products 
                    SET stock = stock - ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([
                    $item['quantity'],
                    $item['product']['id']
                ]);
            }
            
            $pdo->commit();
            
            // Очищаємо кошик
            unset($_SESSION['cart']);
            
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Помилка оформлення замовлення: ' . $e->getMessage();
        }
    }
}

// Отримуємо дані користувача
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформлення замовлення - Інтернет-магазин</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> Інтернет-Магазин
            </a>
        </div>
    </nav>

    <!-- Checkout -->
    <section class="py-5">
        <div class="container">
            <?php if ($success): ?>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body text-center p-5">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                                <h2 class="mt-4">Замовлення оформлено!</h2>
                                <p class="text-muted">Дякуємо за покупку. Ми зв'яжемося з вами найближчим часом.</p>
                                <div class="d-grid gap-2 mt-4">
                                    <a href="orders.php" class="btn btn-primary">
                                        <i class="bi bi-list-ul"></i> Мої замовлення
                                    </a>
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-shop"></i> Продовжити покупки
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <h1 class="mb-4"><i class="bi bi-credit-card"></i> Оформлення замовлення</h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Форма -->
                    <div class="col-lg-7">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h4 class="mb-4">Дані для доставки</h4>
                                
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Повне ім'я</label>
                                        <input type="text" class="form-control" 
                                               value="<?= e($user['full_name']) ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" 
                                               value="<?= e($user['email']) ?>" readonly>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Телефон <span class="text-danger">*</span></label>
                                        <input type="tel" name="phone" class="form-control" 
                                               value="<?= e($user['phone'] ?? $_POST['phone'] ?? '') ?>" 
                                               required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Адреса доставки <span class="text-danger">*</span></label>
                                        <textarea name="delivery_address" class="form-control" 
                                                  rows="3" required><?= e($_POST['delivery_address'] ?? '') ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Примітки до замовлення</label>
                                        <textarea name="notes" class="form-control" 
                                                  rows="2"><?= e($_POST['notes'] ?? '') ?></textarea>
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-check-circle"></i> Підтвердити замовлення
                                        </button>
                                        <a href="cart.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-left"></i> Назад до кошика
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Підсумок -->
                    <div class="col-lg-5">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h4 class="mb-4">Ваше замовлення</h4>
                                
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                        <div>
                                            <h6 class="mb-1"><?= e($item['product']['name']) ?></h6>
                                            <small class="text-muted">
                                                <?= $item['quantity'] ?> × <?= number_format($item['product']['price'], 2) ?> ₴
                                            </small>
                                        </div>
                                        <div class="fw-bold">
                                            <?= number_format($item['subtotal'], 2) ?> ₴
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Доставка:</span>
                                    <span class="text-success">Безкоштовно</span>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between">
                                    <strong>До сплати:</strong>
                                    <strong class="text-primary h4"><?= number_format($total, 2) ?> ₴</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>