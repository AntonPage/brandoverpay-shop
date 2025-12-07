<?php
require_once 'config.php';

// Якщо вже авторизований - на головну
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Заповніть всі поля';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            
            redirect('index.php');
        } else {
            $error = 'Неправильний email або пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вхід - <?= SITE_NAME ?></title>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Вхід
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">
                            <i class="bi bi-person-plus"></i> Реєстрація
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4">
                                <i class="bi bi-box-arrow-in-right"></i> Вхід
                            </h2>

                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-circle"></i> <?= e($error) ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?= e($_POST['email'] ?? '') ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Пароль</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-warning btn-lg">
                                        <i class="bi bi-box-arrow-in-right"></i> Увійти
                                    </button>
                                </div>

                                <div class="text-center">
                                    <p class="mb-0">Немає акаунту? 
                                        <a href="register.php">Зареєструватися</a>
                                    </p>
                                </div>
                            </form>

                            <hr class="my-4">

                            <div class="alert alert-info small">
                                <strong>Тестові дані:</strong><br>
                                Адмін: admin@shop.com / admin123<br>
                                Користувач: user@test.com / user123
                            </div>
                        </div>
                    </div>
                </div>
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
</body>
</html>