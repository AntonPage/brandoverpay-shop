<div class="bg-dark text-white" style="width: 250px; min-height: 100vh;">
    <div class="p-3 border-bottom border-secondary">
        <h4 class="mb-0">
            <i class="bi bi-speedometer2"></i> Адмін
        </h4>
    </div>
    <ul class="nav flex-column p-2">
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active bg-primary' : '' ?>" 
               href="index.php">
                <i class="bi bi-house-door"></i> Головна
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active bg-primary' : '' ?>" 
               href="products.php">
                <i class="bi bi-box-seam"></i> Товари
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active bg-primary' : '' ?>" 
               href="categories.php">
                <i class="bi bi-tags"></i> Категорії
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active bg-primary' : '' ?>" 
               href="orders.php">
                <i class="bi bi-cart-check"></i> Замовлення
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active bg-primary' : '' ?>" 
               href="users.php">
                <i class="bi bi-people"></i> Користувачі
            </a>
        </li>
        <li class="nav-item">
            <hr class="border-secondary">
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="../index.php">
                <i class="bi bi-globe"></i> На сайт
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white" href="../logout.php">
                <i class="bi bi-box-arrow-right"></i> Вихід
            </a>
        </li>
    </ul>
</div>