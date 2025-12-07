<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($email) || empty($password) || empty($full_name)) {
        $error = 'Заповніть всі обов\'язкові поля';
    } elseif ($password !== $password_confirm) {
        $error = 'Паролі не співпадають';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль має бути не менше 6 символів';
    } else {
        // Перевіряємо чи email вже існує
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Користувач з таким email вже існує';
        } else {
            // Реєструємо
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (email, password, full_name, phone) 
                VALUES (?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$email, $hashed_password, $full_name, $phone])) {
                $success = true;
            } else {
                $error = 'Помилка реєстрації. Спробуйте пізніше';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація - Інтернет-магазин</title>
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

    <!-- Registration Form -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4">
                                <i class="bi bi-person-plus"></i> Реєстрація
                            </h2>

                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-circle"></i> <?= e($error) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle"></i> Реєстрація успішна!
                                    <hr>
                                    <a href="login.php" class="btn btn-success">
                                        Увійти в систему
                                    </a>
                                </div>
                            <?php else: ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Повне ім'я <span class="text-danger">*</span></label>
                                        <input type="text" name="full_name" class="form-control" 
                                               value="<?= e($_POST['full_name'] ?? '') ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" 
                                               value="<?= e($_POST['email'] ?? '') ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Телефон</label>
                                        <input type="tel" name="phone" class="form-control" 
                                               value="<?= e($_POST['phone'] ?? '') ?>"
                                               placeholder="+380...">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Пароль <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control" 
                                               minlength="6" required>
                                        <div class="form-text">Мінімум 6 символів</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Підтвердження паролю <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirm" class="form-control" required>
                                    </div>

                                    <div class="d-grid mb-3">
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="bi bi-person-plus"></i> Зареєструватися
                                        </button>
                                    </div>

                                    <div class="text-center">
                                        <p class="mb-0">Вже є акаунт? 
                                            <a href="login.php">Увійти</a>
                                        </p>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>