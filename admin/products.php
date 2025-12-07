<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Видалення
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    redirect('products.php?msg=deleted');
}

// Додавання/редагування
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $category_id = (int)$_POST['category_id'];
    $stock = (int)$_POST['stock'];
    
    $image = 'placeholder.jpg';
    
    // Завантаження фото
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $image = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_DIR . $image);
        }
    }
    
    if ($id > 0) {
        // Оновлення
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, stock=?, image=? WHERE id=?")
                ->execute([$name, $description, $price, $category_id, $stock, $image, $id]);
        } else {
            $pdo->prepare("UPDATE products SET name=?, description=?, price=?, category_id=?, stock=? WHERE id=?")
                ->execute([$name, $description, $price, $category_id, $stock, $id]);
        }
    } else {
        // Додавання
        $pdo->prepare("INSERT INTO products (name, description, price, category_id, stock, image) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$name, $description, $price, $category_id, $stock, $image]);
    }
    
    redirect('products.php?msg=saved');
}

$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Товари - Адмін</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div class="flex-grow-1 p-4 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-box-seam"></i> Товари</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">
                    <i class="bi bi-plus-circle"></i> Додати товар
                </button>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <?= $_GET['msg'] == 'saved' ? 'Товар збережено!' : 'Товар видалено!' ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Фото</th>
                                    <th>Назва</th>
                                    <th>Категорія</th>
                                    <th>Ціна</th>
                                    <th>Залишок</th>
                                    <th>Дії</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                    <tr>
                                        <td><?= $p['id'] ?></td>
                                        <td>
                                            <img src="../uploads/<?= e($p['image']) ?>" 
                                                 style="width:50px;height:50px;object-fit:cover;border-radius:5px;"
                                                 onerror="this.src='https://via.placeholder.com/50'">
                                        </td>
                                        <td><strong><?= e($p['name']) ?></strong></td>
                                        <td><?= e($p['category_name']) ?></td>
                                        <td><strong><?= number_format($p['price'], 2) ?> ₴</strong></td>
                                        <td>
                                            <?php if ($p['stock'] > 10): ?>
                                                <span class="badge bg-success"><?= $p['stock'] ?></span>
                                            <?php elseif ($p['stock'] > 0): ?>
                                                <span class="badge bg-warning"><?= $p['stock'] ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Немає</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" 
                                                    onclick="editProduct(<?= htmlspecialchars(json_encode($p)) ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <a href="?delete=<?= $p['id'] ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Видалити товар?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="productModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data" id="productForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Додати товар</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="productId">
                        
                        <div class="mb-3">
                            <label class="form-label">Назва *</label>
                            <input type="text" name="name" id="productName" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Опис</label>
                            <textarea name="description" id="productDesc" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ціна *</label>
                                    <input type="number" name="price" id="productPrice" class="form-control" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Кількість</label>
                                    <input type="number" name="stock" id="productStock" class="form-control" value="0">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Категорія</label>
                            <select name="category_id" id="productCategory" class="form-select">
                                <option value="0">Без категорії</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Фото</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                        <button type="submit" class="btn btn-primary">Зберегти</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(product) {
            document.getElementById('modalTitle').textContent = 'Редагувати товар';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productDesc').value = product.description;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productCategory').value = product.category_id || 0;
            
            new bootstrap.Modal(document.getElementById('productModal')).show();
        }
        
        document.getElementById('productModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('productForm').reset();
            document.getElementById('modalTitle').textContent = 'Додати товар';
            document.getElementById('productId').value = '';
        });
    </script>
</body>
</html>