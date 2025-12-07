<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неправильний метод запиту']);
    exit;
}

$action = $_POST['action'] ?? '';

// Ініціалізація кошика
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

switch ($action) {
    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Неправильний ID товару']);
            exit;
        }
        
        // Перевіряємо наявність товару
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Товар не знайдено']);
            exit;
        }
        
        // Перевіряємо кількість в кошику
        $currentQty = $_SESSION['cart'][$productId] ?? 0;
        $newQty = $currentQty + $quantity;
        
        if ($newQty > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Недостатньо товару на складі']);
            exit;
        }
        
        // Додаємо до кошика
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] = $newQty;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
        
        echo json_encode([
            'success' => true,
            'cartCount' => count($_SESSION['cart']),
            'message' => 'Товар додано до кошика'
        ]);
        break;
        
    case 'remove':
        $productId = (int)($_POST['product_id'] ?? 0);
        
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
        
        echo json_encode([
            'success' => true,
            'cartCount' => count($_SESSION['cart'])
        ]);
        break;
        
    case 'update':
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        if ($quantity < 1) {
            echo json_encode(['success' => false, 'message' => 'Неправильна кількість']);
            exit;
        }
        
        // Перевіряємо наявність
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product || $quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Недостатньо товару на складі']);
            exit;
        }
        
        $_SESSION['cart'][$productId] = $quantity;
        
        echo json_encode([
            'success' => true,
            'cartCount' => count($_SESSION['cart'])
        ]);
        break;
        
    case 'clear':
        $_SESSION['cart'] = [];
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Невідома дія']);
}
?>