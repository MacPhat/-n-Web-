<?php
require_once 'ket_noi.php';
startSession();

if (!isLoggedIn()) {
    redirect('dang_nhap.php');
}

$ma_don_hang = $_GET['ma_don_hang'] ?? '';

if (!$ma_don_hang) {
    redirect('index.php');
}

// Lấy thông tin đơn hàng
$donHang = $db->selectOne("
    SELECT dh.*, nd.ho_ten, nd.email 
    FROM don_hang dh 
    JOIN nguoi_dung nd ON dh.nguoi_dung_id = nd.id 
    WHERE dh.ma_don_hang = ? AND dh.nguoi_dung_id = ?
", [$ma_don_hang, $_SESSION['nguoi_dung_id']]);

if (!$donHang) {
    redirect('index.php');
}

// Lấy chi tiết đơn hàng
$chiTietDonHang = $db->select("
    SELECT * FROM chi_tiet_don_hang WHERE don_hang_id = ?
", [$donHang['id']]);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Hàng Thành Công - Phát Technology Spirit</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .success-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--shadow-lg);
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: bounce 1s ease-in-out;
        }

        .success-icon i {
            font-size: 2.5rem;
            color: white;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .success-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--success);
            margin-bottom: 10px;
        }

        .success-message {
            color: var(--gray-600);
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        .order-info {
            background: var(--gray-100);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .order-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .order-details {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
        }

        .details-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gray-200);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            font-weight: 600;
            color: var(--gray-800);
        }

        .info-value {
            color: var(--gray-600);
        }

        .order-items {
            margin-top: 20px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .item-details {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 700;
            color: var(--error);
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 20px 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            border-top: 2px solid var(--gray-200);
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
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
            background: var(--gradient-primary);
            color: white;
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
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

        .payment-status {
            margin-top: 20px;
            padding: 15px;
            background: #fef3c7;
            border-radius: 8px;
            border-left: 4px solid #d97706;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .success-card {
                padding: 30px 20px;
            }
            
            .order-details {
                padding: 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .item-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header_simple.php'; ?>
    
    <div class="container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h1 class="success-title">Đặt Hàng Thành Công!</h1>
            <p class="success-message">
                Cảm ơn bạn đã tin tưởng và mua sắm tại Phát Technology Spirit. 
                Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.
            </p>
            
            <div class="order-info">
                <div class="order-number">Mã đơn hàng: <?= $ma_don_hang ?></div>
                <p>Ngày đặt: <?= date('d/m/Y H:i', strtotime($donHang['ngay_dat_hang'])) ?></p>
            </div>
        </div>
        
        <div class="order-details">
            <h2 class="details-title">
                <i class="fas fa-receipt"></i> Chi Tiết Đơn Hàng
            </h2>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Người nhận:</span>
                    <span class="info-value"><?= sanitize($donHang['ten_nguoi_nhan']) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Số điện thoại:</span>
                    <span class="info-value"><?= sanitize($donHang['so_dien_thoai_nhan']) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Địa chỉ giao hàng:</span>
                    <span class="info-value"><?= sanitize($donHang['dia_chi_giao_hang']) ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Phương thức thanh toán:</span>
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
                
                <div class="info-item">
                    <span class="info-label">Trạng thái đơn hàng:</span>
                    <span class="status-badge status-cho-xac-nhan">
                        <i class="fas fa-clock"></i> Chờ xác nhận
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Trạng thái thanh toán:</span>
                    <span class="info-value">
                        <?php if ($donHang['phuong_thuc_thanh_toan'] === 'tien_mat'): ?>
                            Thanh toán khi nhận hàng
                        <?php else: ?>
                            Chưa thanh toán
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <?php if ($donHang['ghi_chu']): ?>
            <div class="info-item" style="margin-top: 20px;">
                <span class="info-label">Ghi chú:</span>
                <span class="info-value"><?= nl2br(sanitize($donHang['ghi_chu'])) ?></span>
            </div>
            <?php endif; ?>
            
            <div class="order-items">
                <h3 style="margin-bottom: 15px; color: var(--dark);">Sản phẩm đã đặt:</h3>
                
                <?php foreach ($chiTietDonHang as $item): ?>
                <div class="item-row">
                    <div class="item-info">
                        <div class="item-name"><?= sanitize($item['ten_san_pham']) ?></div>
                        <div class="item-details">
                            Số lượng: <?= $item['so_luong'] ?> × <?= formatPrice($item['gia']) ?>
                        </div>
                    </div>
                    <div class="item-price">
                        <?= formatPrice($item['thanh_tien']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="total-row">
                    <span>Tổng cộng:</span>
                    <span><?= formatPrice($donHang['tong_tien']) ?></span>
                </div>
            </div>
            
            <?php if ($donHang['phuong_thuc_thanh_toan'] !== 'tien_mat'): ?>
            <div class="payment-status">
                <strong><i class="fas fa-info-circle"></i> Thông tin thanh toán:</strong><br>
                Vui lòng chuyển khoản theo thông tin sau và gửi ảnh chụp biên lai để chúng tôi xác nhận đơn hàng:<br>
                <strong>STK:</strong> 1234567890 - Ngân hàng ABC<br>
                <strong>Chủ TK:</strong> PHAT TECHNOLOGY SPIRIT<br>
                <strong>Nội dung:</strong> <?= $ma_don_hang ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="action-buttons">
            <a href="theo_doi_don_hang.php" class="btn btn-primary">
                <i class="fas fa-truck"></i> Theo dõi đơn hàng
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Về trang chủ
            </a>
            <a href="danh_sach_san_pham.php" class="btn btn-secondary">
                <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
            </a>
        </div>
    </div>
    
    <script>
        // Auto redirect after 30 seconds
        setTimeout(() => {
            if (confirm('Bạn có muốn chuyển đến trang theo dõi đơn hàng?')) {
                window.location.href = 'theo_doi_don_hang.php';
            }
        }, 30000);
    </script>
</body>
</html>