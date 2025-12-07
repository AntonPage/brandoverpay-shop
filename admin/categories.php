<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Видалення
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([(int)$_GET['delete']]);
    redirect('categories.php?msg=deleted');
}

// Додавання/редагування
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    if ($id > 0) {
        $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id=?")->execute([$name, $description, $id]);
    } else {
        $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)")->execute([$name, $description]);
    }
    
    redirect('categories.php?msg=saved');
}

$categories = $pdo->query("SELECT c.*, COUNT(p.id) as products_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Категорії - Адмін</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        
        <div class="flex-grow-1 p-4 bg-light">
            <div class="d-flex justify-content-between mb-4">
                <h1><i class="bi bi-tags"></i> Категорії</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#catModal">
                    <i class="bi bi-plus-circle"></i> Додати категорію
                </button>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <?= $_GET['msg'] == 'saved' ? 'Категорію збережено!' : 'Категорію видалено!' ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Назва</th>
                                <th>Опис</th>
                                <th>Товарів</th>
                                <th>Дії</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?= $cat['id'] ?></td>
                                    <td><strong><?= e($cat['name']) ?></strong></td>
                                    <td><?= e($cat['description']) ?></td>
                                    <td><span class="badge bg-info"><?= $cat['products_count'] ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" 
                                                onclick="editCat(<?= htmlspecialchars(json_encode($cat)) ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-danger"
                                           onclick="return confirm('Видалити категорію?')">
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

    <!-- Modal -->
    <div class="modal fade" id="catModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="catForm">
                    <div class="modal-header">
                        <h5 id="modalTitle">Додати категорію</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="catId">
                        <div class="mb-3">
                            <label class="form-label">Назва *</label>
                            <input type="text" name="name" id="catName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Опис</label>
                            <textarea name="description" id="catDesc" class="form-control" rows="3"></textarea>
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
        function editCat(cat) {
            document.getElementById('modalTitle').textContent = 'Редагувати категорію';
            document.getElementById('catId').value = cat.id;
            document.getElementById('catName').value = cat.name;
            document.getElementById('catDesc').value = cat.description;
            new bootstrap.Modal(document.getElementById('catModal')).show();
        }
        
        document.getElementById('catModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('catForm').reset();
            document.getElementById('modalTitle').textContent = 'Додати категорію';
        });
    </script>
</body>
</html>