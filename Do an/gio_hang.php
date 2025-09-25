<?php
require_once 'ket_noi.php';
startSession();

if (!isLoggedIn()) {
    redirect('dang_nhap.php');
}

// Lấy danh sách sản phẩm trong giỏ hàng
$cartItems = $db->select("
    SELECT gh.*, sp.ten_san_pham, sp.hinh_anh_chinh, sp.thuong_hieu, sp.so_luong_ton
    FROM gio_hang gh
    JOIN san_pham sp ON gh.san_pham_id = sp.id
    WHERE gh.nguoi_dung_id = ?
    ORDER BY gh.ngay_them DESC
", [$_SESSION['nguoi_dung_id']]);

$tongTien = 0;
foreach ($cartItems as $item) {
    $tongTien += $item['gia'] * $item['so_luong'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - Phát Technology Spirit</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --accent: #f093fb;
            --error: #f56565;
            --success: #48bb78;
            --dark: #1a202c;
            --gray-100: #f7fafc;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e0;
            --gray-600: #4a5568;
            --gray-800: #1a202c;
            --gradient-primary: linear-gradient(135deg, var(--primary), var(--secondary));
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-100);
            color: var(--gray-800);
            padding-top: 100px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-header {
            background: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 15px;
            box-shadow: var(--shadow-md);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            text-align: center;
        }

        .breadcrumb {
            text-align: center;
            margin-top: 10px;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        .cart-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
        }

        .cart-items {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-md);
        }

        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-md);
            height: fit-content;
            position: sticky;
            top: 120px;
        }

        .cart-item {
            display: flex;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-info {
            flex: 1;
        }

        .item-brand {
            color: var(--gray-600);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .item-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin: 5px 0;
        }

        .item-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--error);
        }

        .item-actions {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            overflow: hidden;
        }

        .qty-btn {
            width: 35px;
            height: 35px;
            border: none;
            background: var(--gray-100);
            color: var(--gray-600);
            cursor: pointer;
            transition: var(--transition);
        }

        .qty-btn:hover {
            background: var(--primary);
            color: white;
        }

        .qty-input {
            width: 60px;
            height: 35px;
            border: none;
            text-align: center;
            font-weight: 600;
        }

        .remove-btn {
            background: none;
            border: none;
            color: var(--error);
            cursor: pointer;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .remove-btn:hover {
            transform: scale(1.1);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 20px;
        }

        .empty-cart h3 {
            color: var(--gray-600);
            margin-bottom: 20px;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            padding: 20px 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            border-top: 2px solid var(--gray-200);
        }

        .checkout-btn {
            width: 100%;
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .back-shopping {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .back-shopping:hover {
            gap: 15px;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .cart-container {
                grid-template-columns: 1fr;
            }
            
            .cart-summary {
                position: static;
            }
            
            .cart-item {
                flex-direction: column;
                gap: 15px;
            }
            
            .item-image {
                width: 80px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header_simple.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Giỏ Hàng</h1>
            <div class="breadcrumb">
                <a href="index.php">Trang chủ</a> / Giỏ hàng
            </div>
        </div>
        
        <a href="index.php" class="back-shopping">
            <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
        </a>
        
        <?php if (empty($cartItems)): ?>
        <div class="cart-items">
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Giỏ hàng của bạn đang trống</h3>
                <p style="color: var(--gray-600); margin-bottom: 30px;">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục mua sắm!</p>
                <a href="index.php" class="btn-primary">
                    <i class="fas fa-shopping-bag"></i> Mua sắm ngay
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="cart-container">
            <div class="cart-items">
                <h3 style="margin-bottom: 20px;">Sản phẩm trong giỏ hàng (<?= count($cartItems) ?> sản phẩm)</h3>
                
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item" data-product-id="<?= $item['san_pham_id'] ?>">
                    <div class="item-image">
                        <img src="<?= $item['hinh_anh_chinh'] ?>" alt="<?= sanitize($item['ten_san_pham']) ?>">
                    </div>
                    
                    <div class="item-info">
                        <div class="item-brand"><?= sanitize($item['thuong_hieu']) ?></div>
                        <div class="item-name"><?= sanitize($item['ten_san_pham']) ?></div>
                        <div class="item-price"><?= formatPrice($item['gia']) ?></div>
                        
                        <div class="item-actions">
                            <div class="quantity-control">
                                <button class="qty-btn" onclick="updateQuantity(<?= $item['san_pham_id'] ?>, <?= $item['so_luong'] - 1 ?>)">-</button>
                                <input type="number" class="qty-input" value="<?= $item['so_luong'] ?>" min="1" max="<?= $item['so_luong_ton'] ?>" onchange="updateQuantity(<?= $item['san_pham_id'] ?>, this.value)">
                                <button class="qty-btn" onclick="updateQuantity(<?= $item['san_pham_id'] ?>, <?= $item['so_luong'] + 1 ?>)">+</button>
                            </div>
                            
                            <button class="remove-btn" onclick="removeFromCart(<?= $item['san_pham_id'] ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="cart-summary">
                <h3 style="margin-bottom: 20px;">Tóm tắt đơn hàng</h3>
                
                <div class="summary-item">
                    <span>Tạm tính:</span>
                    <span id="subtotal"><?= formatPrice($tongTien) ?></span>
                </div>
                
                <div class="summary-item">
                    <span>Phí vận chuyển:</span>
                    <span>Miễn phí</span>
                </div>
                
                <div class="summary-total">
                    <span>Tổng cộng:</span>
                    <span id="total"><?= formatPrice($tongTien) ?></span>
                </div>
                
                <button class="checkout-btn" onclick="window.location='thanh_toan.php'">
                    <i class="fas fa-credit-card"></i> Tiến hành thanh toán
                </button>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--gray-200);">
                    <h4 style="margin-bottom: 10px;">Ưu đãi:</h4>
                    <ul style="color: var(--gray-600); font-size: 0.9rem; line-height: 1.6;">
                        <li>✓ Miễn phí giao hàng toàn quốc</li>
                        <li>✓ Bảo hành chính hãng</li>
                        <li>✓ Đổi trả trong 7 ngày</li>
                        <li>✓ Hỗ trợ kỹ thuật 24/7</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function updateQuantity(productId, newQuantity) {
            if (newQuantity < 1) {
                if (confirm('Bạn có muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                    removeFromCart(productId);
                }
                return;
            }
            
            fetch('xu_ly_gio_hang.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&product_id=${productId}&quantity=${newQuantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra!');
            });
        }
        
        function removeFromCart(productId) {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này?')) return;
            
            fetch('xu_ly_gio_hang.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Có lỗi xảy ra!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra!');
            });
        }
    </script>
</body>
</html>