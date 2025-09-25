<?php
if (!isset($db)) {
    require_once 'ket_noi.php';
}
?>

<style>
.simple-header {
    position: fixed;
    top: 0;
    width: 100%;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 1000;
    height: 70px;
    display: flex;
    align-items: center;
}

.simple-header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.simple-logo {
    color: var(--primary);
    text-decoration: none;
    font-weight: 700;
    font-size: 1.3rem;
}

.simple-nav {
    display: flex;
    gap: 2rem;
    align-items: center;
}

.simple-nav a {
    color: var(--gray-600);
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s ease;
}

.simple-nav a:hover {
    color: var(--primary);
}
</style>

<header class="simple-header">
    <div class="simple-header-container">
        <a href="index.php" class="simple-logo">
            <i class="fas fa-microchip"></i> Phát Technology Spirit
        </a>
        
        <nav class="simple-nav">
            <a href="index.php">Trang chủ</a>
            
            <?php if (isLoggedIn()): ?>
                <a href="gio_hang.php">
                    <i class="fas fa-shopping-cart"></i> Giỏ hàng
                </a>
                <a href="tai_khoan.php">
                    <i class="fas fa-user"></i> <?= sanitize($_SESSION['ho_ten']) ?>
                </a>
                <a href="dang_xuat.php">Đăng xuất</a>
            <?php else: ?>
                <a href="dang_nhap.php">Đăng nhập</a>
                <a href="dang_ky.php">Đăng ký</a>
            <?php endif; ?>
        </nav>
    </div>
</header>