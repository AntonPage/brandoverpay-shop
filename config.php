<?php
// Конфігурація бази даних
define('DB_HOST', 'localhost');
define('DB_NAME', 'internet_shop');
define('DB_USER', 'root');
define('DB_PASS', ''); // За замовчуванням в XAMPP пароль порожній

// Конфігурація сайту
define('SITE_NAME', 'BrandOverpay.com');
define('SITE_SLOGAN', 'Premium products, premium prices');
define('SITE_URL', 'http://localhost/internet-shop');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Налаштування сесії
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Підключення до БД
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("<h1>Помилка підключення до БД</h1><p>Перевірте чи створена база даних 'internet_shop' та чи запущений MySQL в XAMPP</p><p>Помилка: " . $e->getMessage() . "</p>");
}

// Функція для перевірки авторизації
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Функція для перевірки прав адміністратора
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Функція для редіректу
function redirect($url) {
    header("Location: $url");
    exit;
}

// Функція для безпечного виведення HTML
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Функція для отримання інформації про користувача
function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>