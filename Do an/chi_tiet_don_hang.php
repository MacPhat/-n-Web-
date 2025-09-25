<?php
require_once 'ket_noi.php';
startSession();

if (!isLoggedIn()) {
    redirect('dang_nhap.php');
}

$don_hang_id = (int)($_GET['id'] ?? 0);
$nguoi_dung_id = $_SESSION['nguoi_dung_id'];

if (!$don_hang_id) {
    redirect('theo_doi_don_hang.php');
}

// Lấy thông tin đơn hàng
$donHang = $db->selectOne("
    SELECT dh.*, nd.ho_ten, nd.email 
    FROM don_hang dh 
    JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id 
    WHERE dh.id = ? AND dh.nguoi_dung_id = ?
", [$don_hang_id, $nguoi_dung_id]);

if (!$donHang) {
    redirect('theo_doi_don_hang.php');
}

// Lấy chi tiết đơn hàng
$chiTietDonHang = $db->select("
    SELECT ctdh.*, sp.hinh_anh_chinh, sp.thuong_hieu
    FROM chi_tiet_don_hang ctdh
    LEFT JOIN san_pham sp ON ctdh.san_pham_id = sp.id
    WHERE ctdh.don_hang_id = ?
", [$don_hang_id]);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Đơn Hàng #<?= $donHang['ma_don_hang'] ?> - Phát Technology Spirit</title>
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
            --warning: #ed8936;
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .page-header {
            background: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 15px;
            box-shadow: var(--shadow-md);
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .breadcrumb {
            color: var(--gray-600);
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        .order-detail {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--gray-200);
        }

        .order-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .order-date {
            color: var(--gray-600);
        }

        .order-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--error);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .info-section {
            background: var(--gray-100);
            padding: 20px;
            border-radius: 10px;
        }

        .info-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .info-label {
            color: var(--gray-600);
        }

        .info-value {
            font-weight: 500;
            color: var(--dark);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .status-cho-xac-nhan {
            background: #fef3c7;
            color: #d97706;
        }

        .status-da-xac-nhan {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-dang-chuan-bi {
            background: #e0e7ff;
            color: #5b21b6;
        }

        .status-dang-giao {
            background: #fef3c7;
            color: #d97706;
        }

        .status-da-giao {
            background: #d1fae5;
            color: #065f46;
        }

        .status-da-huy {
            background: #fee2e2;
            color: #dc2626;
        }

        .payment-status {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .payment-chua-thanh-toan {
            background: #fee2e2;
            color: #dc2626;
        }

        .payment-da-thanh-toan {
            background: #d1fae5;
            color: #065f46;
        }

        .products-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-item {
            display: flex;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .product-brand {
            color: var(--gray-600);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .product-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-quantity {
            color: var(--gray-600);
        }

        .product-price {
            font-weight: 700;
            color: var(--error);
        }

        .product-subtotal {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
        }

        .order-summary {
            background: var(--gray-100);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            padding-top: 15px;
            border-top: 2px solid var(--gray-200);
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .progress-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
        }

        .progress-bar {
            display: flex;
            align-items: center;
            margin: 30px 0;
            position: relative;
        }

        .progress-step {
            flex: 1;
            text-align: center;
            position: relative;
        }

        .progress-step::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            right: -50%;
            height: 2px;
            background: var(--gray-200);
            z-index: 1;
        }

        .progress-step:last-child::before {
            display: none;
        }

        .progress-step.active::before,
        .progress-step.completed::before {
            background: var(--success);
        }

        .progress-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--gray-200);
            color: var(--gray-600);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            position: relative;
            z-index: 2;
            font-size: 0.9rem;
        }

        .progress-step.active .progress-icon,
        .progress-step.completed .progress-icon {
            background: var(--success);
            color: white;
        }

        .progress-label {
            font-size: 0.9rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        .progress-step.active .progress-label,
        .progress-step.completed .progress-label {
            color: var(--success);
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-secondary {
            background: white;
            color: var(--gray-600);
            border: 1px solid var(--gray-300);
        }

        .btn-danger {
            background: var(--error);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .product-item {
                flex-direction: column;
                gap: 15px;
            }
            
            .product-details {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'header_simple.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Chi Tiết Đơn Hàng</h1>
            <div class="breadcrumb">
                <a href="index.php">Trang chủ</a> / 
                <a href="theo_doi_don_hang.php">Đơn hàng của tôi</a> / 
                Chi tiết đơn hàng
            </div>
        </div>
        
        <div class="order-detail">
            <div class="order-header">
                <div>
                    <div class="order-number">Đơn hàng #<?= $donHang['ma_don_hang'] ?></div>
                    <div class="order-date">Đặt ngày: <?= date('d/m/Y H:i', strtotime($donHang['ngay_dat_hang'])) ?></div>
                </div>
                <div class="order-total"><?= formatPrice($donHang['tong_tien']) ?></div>
            </div>
            
            <div class="info-grid">
                <div class="info-section">
                    <div class="info-title">
                        <i class="fas fa-user"></i> Thông tin người nhận
                    </div>
                    <div class="info-item">
                        <span class="info-label">Họ tên:</span>
                        <span class="info-value"><?= sanitize($donHang['ten_nguoi_nhan']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Số điện thoại:</span>
                        <span class="info-value"><?= sanitize($donHang['so_dien_thoai_nhan']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Địa chỉ:</span>
                        <span class="info-value"><?= sanitize($donHang['dia_chi_giao_hang']) ?></span>
                    </div>
                </div>
                
                <div class="info-section">
                    <div class="info-title">
                        <i class="fas fa-info-circle"></i> Trạng thái đơn hàng
                    </div>
                    <div class="info-item">
                        <span class="info-label">Trạng thái:</span>
                        <span class="status-badge status-<?= str_replace('_', '-', $donHang['trang_thai_don_hang']) ?>">
                            <?php
                            $status_labels = [
                                'cho_xac_nhan' => '<i class="fas fa-clock"></i> Chờ xác nhận',
                                'da_xac_nhan' => '<i class="fas fa-check"></i> Đã xác nhận',
                                'dang_chuan_bi' => '<i class="fas fa-box"></i> Đang chuẩn bị',
                                'dang_giao' => '<i class="fas fa-truck"></i> Đang giao',
                                'da_giao' => '<i class="fas fa-check-circle"></i> Đã giao',
                                'da_huy' => '<i class="fas fa-times-circle"></i> Đã hủy'
                            ];
                            echo $status_labels[$donHang['trang_thai_don_hang']] ?? 'Không xác định';
                            ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Thanh toán:</span>
                        <span class="payment-status payment-<?= str_replace('_', '-', $donHang['trang_thai_thanh_toan']) ?>">
                            <?php
                            $payment_labels = [
                                'chua_thanh_toan' => 'Chưa thanh toán',
                                'da_thanh_toan' => 'Đã thanh toán',
                                'hoan_tien' => 'Hoàn tiền'
                            ];
                            echo $payment_labels[$donHang['trang_thai_thanh_toan']] ?? 'Không xác định';
                            ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Phương thức:</span>
                        <span class="info-value">
                            <?php
                            $payment_methods = [
                                'tien_mat' => 'Tiền mặt (COD)',
                                'chuyen_khoan' => 'Chuyển khoản',
                                'the_tin_dung' => 'Thẻ tín dụng',
                                'vi_dien_tu' => 'Ví điện tử'
                            ];
                            echo $payment_methods[$donHang['phuong_thuc_thanh_toan']] ?? 'Không xác định';
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <?php if ($donHang['ghi_chu']): ?>
            <div class="info-section" style="margin-bottom: 0;">
                <div class="info-title">
                    <i class="fas fa-sticky-note"></i> Ghi chú
                </div>
                <p style="color: var(--gray-600); line-height: 1.6;">
                    <?= nl2br(sanitize($donHang['ghi_chu'])) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Progress Tracking -->
        <?php if ($donHang['trang_thai_don_hang'] !== 'da_huy'): ?>
        <div class="progress-section">
            <h3 class="section-title">
                <i class="fas fa-route"></i> Tiến trình đơn hàng
            </h3>
            
            <div class="progress-bar">
                <div class="progress-step <?= in_array($donHang['trang_thai_don_hang'], ['cho_xac_nhan', 'da_xac_nhan', 'dang_chuan_bi', 'dang_giao', 'da_giao']) ? 'completed' : '' ?>">
                    <div class="progress-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="progress-label">Đặt hàng</div>
                </div>
                
                <div class="progress-step <?= in_array($donHang['trang_thai_don_hang'], ['da_xac_nhan', 'dang_chuan_bi', 'dang_giao', 'da_giao']) ? 'completed' : ($donHang['trang_thai_don_hang'] === 'cho_xac_nhan' ? 'active' : '') ?>">
                    <div class="progress-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="progress-label">Xác nhận</div>
                </div>
                
                <div class="progress-step <?= in_array($donHang['trang_thai_don_hang'], ['dang_chuan_bi', 'dang_giao', 'da_giao']) ? 'completed' : ($donHang['trang_thai_don_hang'] === 'da_xac_nhan' ? 'active' : '') ?>">
                    <div class="progress-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="progress-label">Chuẩn bị</div>
                </div>
                
                <div class="progress-step <?= in_array($donHang['trang_thai_don_hang'], ['dang_giao', 'da_giao']) ? 'completed' : ($donHang['trang_thai_don_hang'] === 'dang_chuan_bi' ? 'active' : '') ?>">
                    <div class="progress-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="progress-label">Vận chuyển</div>
                </div>
                
                <div class="progress-step <?= $donHang['trang_thai_don_hang'] === 'da_giao' ? 'completed' : ($donHang['trang_thai_don_hang'] === 'dang_giao' ? 'active' : '') ?>">
                    <div class="progress-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="progress-label">Hoàn thành</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Products -->
        <div class="products-section">
            <h3 class="section-title">
                <i class="fas fa-box-open"></i> Sản phẩm đã đặt
            </h3>
            
            <?php foreach ($chiTietDonHang as $item): ?>
            <div class="product-item">
                <div class="product-image">
                    <?php if ($item['hinh_anh_chinh']): ?>
                    <img src="<?= $item['hinh_anh_chinh'] ?>" alt="<?= sanitize($item['ten_san_pham']) ?>">
                    <?php else: ?>
                    <div style="background: var(--gray-200); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-image" style="color: var(--gray-400);"></i>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <div class="product-name"><?= sanitize($item['ten_san_pham']) ?></div>
                    <?php if ($item['thuong_hieu']): ?>
                    <div class="product-brand"><?= sanitize($item['thuong_hieu']) ?></div>
                    <?php endif; ?>
                    
                    <div class="product-details">
                        <div>
                            <span class="product-quantity">Số lượng: <?= $item['so_luong'] ?></span>
                            <span class="product-price"> × <?= formatPrice($item['gia']) ?></span>
                        </div>
                        <div class="product-subtotal"><?= formatPrice($item['thanh_tien']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="order-summary">
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <span><?= formatPrice($donHang['tong_tien'] - $donHang['phi_van_chuyen'] + $donHang['giam_gia']) ?></span>
                </div>
                
                <?php if ($donHang['phi_van_chuyen'] > 0): ?>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span><?= formatPrice($donHang['phi_van_chuyen']) ?></span>
                </div>
                <?php else: ?>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span style="color: var(--success);">Miễn phí</span>
                </div>
                <?php endif; ?>
                
                <?php if ($donHang['giam_gia'] > 0): ?>
                <div class="summary-row">
                    <span>Giảm giá:</span>
                    <span style="color: var(--success);">-<?= formatPrice($donHang['giam_gia']) ?></span>
                </div>
                <?php endif; ?>
                
                <div class="summary-total">
                    <span>Tổng cộng:</span>
                    <span><?= formatPrice($donHang['tong_tien']) ?></span>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="theo_doi_don_hang.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
            
            <?php if ($donHang['trang_thai_don_hang'] === 'cho_xac_nhan'): ?>
            <button class="btn btn-danger" onclick="cancelOrder(<?= $donHang['id'] ?>)">
                <i class="fas fa-times"></i> Hủy đơn hàng
            </button>
            <?php endif; ?>
            
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
            </a>
        </div>
    </div>
    
    <script>
        function cancelOrder(orderId) {
            if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
                fetch('xu_ly_don_hang.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=cancel&order_id=${orderId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Đã hủy đơn hàng thành công!');
                        location.reload();
                    } else {
                        alert(data.message || 'Có lỗi xảy ra!');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra!');
                });
            }
        }
    </script>
</body>
</html>