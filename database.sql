-- Створення бази даних
CREATE DATABASE IF NOT EXISTS internet_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE internet_shop;

-- Таблиця користувачів
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблиця категорій товарів
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблиця товарів
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблиця замовлень
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    delivery_address TEXT,
    phone VARCHAR(20),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблиця товарів у замовленні
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Додаємо адміністратора (пароль: admin123)
INSERT INTO users (email, password, full_name, role) VALUES 
('admin@shop.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Адміністратор', 'admin');

-- Додаємо категорії
INSERT INTO categories (name, description) VALUES 
('Електроніка', 'Смартфони, ноутбуки, планшети'),
('Одяг', 'Чоловічий та жіночий одяг'),
('Книги', 'Художня та технічна література'),
('Побутова техніка', 'Техніка для дому');

-- Додаємо тестові товари
INSERT INTO products (category_id, name, description, price, image, stock) VALUES 
(1, 'iPhone 15 Pro', 'Потужний смартфон від Apple', 45999.00, 'iphone15.jpg', 10),
(1, 'Samsung Galaxy S24', 'Флагманський Android-смартфон', 38999.00, 'samsung_s24.jpg', 15),
(1, 'MacBook Air M3', 'Легкий та продуктивний ноутбук', 54999.00, 'macbook_air.jpg', 8),
(2, 'Футболка Nike', 'Спортивна футболка з бавовни', 899.00, 'tshirt_nike.jpg', 50),
(2, 'Джинси Levi\'s', 'Класичні джинси синього кольору', 2499.00, 'jeans_levis.jpg', 30),
(3, 'Clean Code', 'Книга про чистий код від Robert Martin', 599.00, 'clean_code.jpg', 20),
(4, 'Пилосос Dyson', 'Бездротовий пилосос преміум класу', 18999.00, 'dyson_vacuum.jpg', 5);