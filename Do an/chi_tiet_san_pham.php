<?php
require_once 'ket_noi.php';
startSession();

$san_pham_id = (int)($_GET['id'] ?? 0);

if (!$san_pham_id) {
    redirect('index.php');
}

// Lấy thông tin sản phẩm
$sanPham = $db->selectOne("
    SELECT sp.*, dm.ten_danh_muc 
    FROM san_pham sp 
    LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id 
    WHERE sp.id = ?
", [$san_pham_id]);

if (!$sanPham) {
    redirect('index.php');
}

// Cập nhật lượt xem
$db->execute("UPDATE san_pham SET luot_xem = luot_xem + 1 WHERE id = ?", [$san_pham_id]);

// Lấy sản phẩm liên quan
$sanPhamLienQuan = $db->select("
    SELECT * FROM san_pham 
    WHERE danh_muc_id = ? AND id != ? AND trang_thai = 'con_hang' 
    ORDER BY luot_xem DESC 
    LIMIT 4
", [$sanPham['danh_muc_id'], $san_pham_id]);

// Lấy đánh giá sản phẩm
$danhGia = $db->select("
    SELECT dg.*, nd.ho_ten 
    FROM danh_gia dg 
    JOIN nguoi_dung nd ON dg.nguoi_dung_id = nd.id 
    WHERE dg.san_pham_id = ? AND dg.trang_thai = 'hien' 
    ORDER BY dg.ngay_danh_gia DESC 
    LIMIT 10
", [$san_pham_id]);

// Decode thông số kỹ thuật
$thongSoKyThuat = json_decode($sanPham['thong_so_ky_thuat'], true) ?? [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($sanPham['ten_san_pham']) ?> - Phát Technology Spirit</title>
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

        .breadcrumb {
            background: white;
            padding: 15px 30px;
            border-radius: 10px;
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 40px;
        }

        .product-images {
            position: relative;
        }

        .main-image {
            width: 100%;
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: var(--shadow-md);
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .main-image:hover img {
            transform: scale(1.05);
        }

        .product-info h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .product-brand {
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .stars {
            color: #fbbf24;
        }

        .rating-text {
            color: var(--gray-600);
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .current-price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--error);
        }

        .original-price {
            font-size: 1.2rem;
            color: var(--gray-600);
            text-decoration: line-through;
        }

        .discount-badge {
            background: var(--error);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .product-description {
            color: var(--gray-600);
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .product-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            border: 1px solid var(--gray-300);
            border-radius: 8px;
            overflow: hidden;
        }

        .qty-btn {
            width: 40px;
            height: 40px;
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
            height: 40px;
            border: none;
            text-align: center;
            font-weight: 600;
        }

        .add-to-cart-btn {
            flex: 1;
            padding: 12px 24px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .product-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: var(--gray-100);
            border-radius: 8px;
        }

        .feature-icon {
            color: var(--primary);
            font-size: 1.2rem;
        }

        .tabs {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 40px;
            overflow: hidden;
        }

        .tab-buttons {
            display: flex;
            background: var(--gray-100);
        }

        .tab-btn {
            flex: 1;
            padding: 15px 20px;
            background: none;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .tab-btn.active {
            background: white;
            color: var(--primary);
        }

        .tab-content {
            padding: 30px;
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .specs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .spec-label {
            font-weight: 600;
            color: var(--gray-800);
        }

        .spec-value {
            color: var(--gray-600);
        }

        .review-item {
            padding: 20px 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .reviewer-name {
            font-weight: 600;
            color: var(--dark);
        }

        .review-date {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .review-stars {
            color: #fbbf24;
            margin-bottom: 10px;
        }

        .review-text {
            color: var(--gray-600);
            line-height: 1.6;
        }

        .related-products {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
        }

        .related-products h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 30px;
            text-align: center;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .related-card {
            border: 1px solid var(--gray-200);
            border-radius: 15px;
            overflow: hidden;
            transition: var(--transition);
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .related-image {
            height: 150px;
            overflow: hidden;
        }

        .related-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .related-info {
            padding: 15px;
        }

        .related-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .related-price {
            color: var(--error);
            font-weight: 700;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .product-detail {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 20px;
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .tab-buttons {
                flex-direction: column;
            }
            
            .related-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <?php include 'header_simple.php'; ?>
    
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Trang chủ</a> / 
            <a href="danh_sach_san_pham.php?danh_muc=<?= $sanPham['danh_muc_id'] ?>"><?= sanitize($sanPham['ten_danh_muc']) ?></a> / 
            <?= sanitize($sanPham['ten_san_pham']) ?>
        </div>
        
        <div class="product-detail">
            <div class="product-images">
                <div class="main-image">
                    <img src="<?= $sanPham['hinh_anh_chinh'] ?>" alt="<?= sanitize($sanPham['ten_san_pham']) ?>">
                </div>
            </div>
            
            <div class="product-info">
                <div class="product-brand"><?= sanitize($sanPham['thuong_hieu']) ?></div>
                <h1><?= sanitize($sanPham['ten_san_pham']) ?></h1>
                
                <div class="product-rating">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="<?= $i <= $sanPham['diem_danh_gia'] ? 'fas' : 'far' ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-text">(<?= $sanPham['so_danh_gia'] ?> đánh giá)</span>
                    <span class="rating-text">• <?= $sanPham['luot_xem'] ?> lượt xem</span>
                </div>
                
                <div class="product-price">
                    <span class="current-price"><?= formatPrice($sanPham['gia']) ?></span>
                    <?php if ($sanPham['gia_goc'] && $sanPham['gia_goc'] > $sanPham['gia']): ?>
                    <span class="original-price"><?= formatPrice($sanPham['gia_goc']) ?></span>
                    <span class="discount-badge">-<?= $sanPham['giam_gia_percent'] ?>%</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-description">
                    <?= nl2br(sanitize($sanPham['mo_ta_ngan'])) ?>
                </div>
                
                <div class="product-features">
                    <div class="feature-item">
                        <i class="fas fa-shield-alt feature-icon"></i>
                        <span>Bảo hành <?= sanitize($sanPham['bao_hanh']) ?></span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-shipping-fast feature-icon"></i>
                        <span>Miễn phí vận chuyển</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-undo feature-icon"></i>
                        <span>Đổi trả trong 7 ngày</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-headset feature-icon"></i>
                        <span>Hỗ trợ 24/7</span>
                    </div>
                </div>
                
                <?php if ($sanPham['so_luong_ton'] > 0): ?>
                <div class="product-actions">
                    <div class="quantity-selector">
                        <button class="qty-btn" onclick="changeQuantity(-1)">-</button>
                        <input type="number" class="qty-input" id="quantity" value="1" min="1" max="<?= $sanPham['so_luong_ton'] ?>">
                        <button class="qty-btn" onclick="changeQuantity(1)">+</button>
                    </div>
                    <button class="add-to-cart-btn" onclick="addToCart()">
                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                    </button>
                </div>
                <p style="color: var(--success); font-weight: 600;">
                    <i class="fas fa-check-circle"></i> Còn <?= $sanPham['so_luong_ton'] ?> sản phẩm
                </p>
                <?php else: ?>
                <div style="color: var(--error); font-weight: 600; text-align: center; padding: 20px;">
                    <i class="fas fa-times-circle"></i> Sản phẩm tạm hết hàng
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="showTab('description')">Mô tả chi tiết</button>
                <button class="tab-btn" onclick="showTab('specs')">Thông số kỹ thuật</button>
                <button class="tab-btn" onclick="showTab('reviews')">Đánh giá (<?= count($danhGia) ?>)</button>
            </div>
            
            <div class="tab-content active" id="description">
                <div style="line-height: 1.8; color: var(--gray-600);">
                    <?= nl2br(sanitize($sanPham['mo_ta_chi_tiet'])) ?>
                </div>
            </div>
            
            <div class="tab-content" id="specs">
                <div class="specs-grid">
                    <?php if (!empty($thongSoKyThuat)): ?>
                        <?php foreach ($thongSoKyThuat as $key => $value): ?>
                        <div class="spec-item">
                            <span class="spec-label"><?= ucfirst(str_replace('_', ' ', $key)) ?>:</span>
                            <span class="spec-value"><?= sanitize($value) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <div class="spec-item">
                        <span class="spec-label">Thương hiệu:</span>
                        <span class="spec-value"><?= sanitize($sanPham['thuong_hieu']) ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Xuất xứ:</span>
                        <span class="spec-value"><?= sanitize($sanPham['xuat_xu']) ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Bảo hành:</span>
                        <span class="spec-value"><?= sanitize($sanPham['bao_hanh']) ?></span>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="reviews">
                <?php if (empty($danhGia)): ?>
                <p style="text-align: center; color: var(--gray-600); padding: 40px;">
                    Chưa có đánh giá nào cho sản phẩm này.
                </p>
                <?php else: ?>
                    <?php foreach ($danhGia as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <span class="reviewer-name"><?= sanitize($review['ho_ten']) ?></span>
                            <span class="review-date"><?= date('d/m/Y', strtotime($review['ngay_danh_gia'])) ?></span>
                        </div>
                        <div class="review-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="<?= $i <= $review['diem_danh_gia'] ? 'fas' : 'far' ?> fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <div class="review-text"><?= nl2br(sanitize($review['binh_luan'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($sanPhamLienQuan)): ?>
        <div class="related-products">
            <h3>Sản phẩm liên quan</h3>
            <div class="related-grid">
                <?php foreach ($sanPhamLienQuan as $related): ?>
                <a href="chi_tiet_san_pham.php?id=<?= $related['id'] ?>" class="related-card" style="text-decoration: none; color: inherit;">
                    <div class="related-image">
                        <img src="<?= $related['hinh_anh_chinh'] ?>" alt="<?= sanitize($related['ten_san_pham']) ?>">
                    </div>
                    <div class="related-info">
                        <div class="related-title"><?= sanitize($related['ten_san_pham']) ?></div>
                        <div class="related-price"><?= formatPrice($related['gia']) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        function changeQuantity(change) {
            const input = document.getElementById('quantity');
            const newValue = parseInt(input.value) + change;
            const max = parseInt(input.max);
            
            if (newValue >= 1 && newValue <= max) {
                input.value = newValue;
            }
        }
        
        function addToCart() {
            <?php if (!isLoggedIn()): ?>
                alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
                window.location.href = 'dang_nhap.php';
                return;
            <?php endif; ?>
            
            const quantity = document.getElementById('quantity').value;
            
            fetch('xu_ly_gio_hang.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=<?= $san_pham_id ?>&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã thêm sản phẩm vào giỏ hàng!');
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
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