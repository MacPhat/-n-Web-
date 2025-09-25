<?php
require_once 'ket_noi.php';
startSession();

if (!isLoggedIn()) {
    redirect('dang_nhap.php');
}

$nguoi_dung_id = $_SESSION['nguoi_dung_id'];

// Lấy danh sách đơn hàng của người dùng
$donHangs = $db->select("
    SELECT * FROM don_hang 
    WHERE nguoi_dung_id = ? 
    ORDER BY ngay_dat_hang DESC
", [$nguoi_dung_id]);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theo Dõi Đơn Hàng - Phát Technology Spirit</title>
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

        .orders-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-md);
        }

        .order-card {
            border: 1px solid var(--gray-200);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .order-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray-200);
        }

        .order-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
        }

        .order-date {
            color: var(--gray-600);
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 0.9rem;
        }

        .info-value {
            color: var(--gray-600);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
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
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
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

        .order-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--error);
            text-align: right;
        }

        .order-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 15px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
            font-size: 0.9rem;
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
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .empty-orders {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-orders i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 20px;
        }

        .empty-orders h3 {
            color: var(--gray-600);
            margin-bottom: 20px;
        }

        .progress-bar {
            display: flex;
            align-items: center;
            margin: 20px 0;
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

        .progress-step.active::before {
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

        .progress-step.active .progress-icon {
            background: var(--success);
            color: white;
        }

        .progress-step.completed .progress-icon {
            background: var(--success);
            color: white;
        }

        .progress-label {
            font-size: 0.8rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        .progress-step.active .progress-label {
            color: var(--success);
            font-weight: 600;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .order-info {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                justify-content: flex-start;
                flex-wrap: wrap;
            }
            
            .progress-bar {
                flex-direction: column;
                gap: 20px;
            }
            
            .progress-step::before {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'header_simple.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Theo Dõi Đơn Hàng</h1>
        </div>
        
        <div class="orders-container">
            <?php if (empty($donHangs)): ?>
            <div class="empty-orders">
                <i class="fas fa-clipboard-list"></i>
                <h3>Bạn chưa có đơn hàng nào</h3>
                <p style="color: var(--gray-600); margin-bottom: 30px;">
                    Hãy khám phá các sản phẩm tuyệt vời của chúng tôi!
                </p>
                <a href="danh_sach_san_pham.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Mua sắm ngay
                </a>
            </div>
            <?php else: ?>
                <?php foreach ($donHangs as $donHang): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">Đơn hàng #<?= $donHang['ma_don_hang'] ?></div>
                            <div class="order-date">Đặt ngày: <?= date('d/m/Y H:i', strtotime($donHang['ngay_dat_hang'])) ?></div>
                        </div>
                        <div class="order-total"><?= formatPrice($donHang['tong_tien']) ?></div>
                    </div>
                    
                    <div class="order-info">
                        <div class="info-item">
                            <span class="info-label">Người nhận:</span>
                            <span class="info-value"><?= sanitize($donHang['ten_nguoi_nhan']) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Số điện thoại:</span>
                            <span class="info-value"><?= sanitize($donHang['so_dien_thoai_nhan']) ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Trạng thái đơn hàng:</span>
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
                    </div>
                    
                    <!-- Progress Bar -->
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
                    
                    <div class="order-actions">
                        <a href="chi_tiet_don_hang.php?id=<?= $donHang['id'] ?>" class="btn btn-primary">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </a>
                        
                        <?php if ($donHang['trang_thai_don_hang'] === 'cho_xac_nhan'): ?>
                        <button class="btn btn-danger" onclick="cancelOrder(<?= $donHang['id'] ?>)">
                            <i class="fas fa-times"></i> Hủy đơn
                        </button>
                        <?php endif; ?>
                        
                        <?php if ($donHang['trang_thai_don_hang'] === 'da_giao'): ?>
                        <a href="danh_gia_san_pham.php?don_hang_id=<?= $donHang['id'] ?>" class="btn btn-secondary">
                            <i class="fas fa-star"></i> Đánh giá
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
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