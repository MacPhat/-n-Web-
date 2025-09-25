<?php
require_once 'ket_noi.php';
startSession();

if (!isLoggedIn()) {
    redirect('dang_nhap.php');
}

$nguoi_dung_id = $_SESSION['nguoi_dung_id'];

// Lấy thông tin người dùng
$nguoiDung = $db->selectOne("SELECT * FROM nguoi_dung WHERE id = ?", [$nguoi_dung_id]);

// Lấy giỏ hàng
$cartItems = $db->select("
    SELECT gh.*, sp.ten_san_pham, sp.hinh_anh_chinh, sp.thuong_hieu, sp.so_luong_ton
    FROM gio_hang gh
    JOIN san_pham sp ON gh.san_pham_id = sp.id
    WHERE gh.nguoi_dung_id = ?
    ORDER BY gh.ngay_them DESC
", [$nguoi_dung_id]);

if (empty($cartItems)) {
    redirect('gio_hang.php');
}

$tongTien = 0;
foreach ($cartItems as $item) {
    $tongTien += $item['gia'] * $item['so_luong'];
}

$error = '';
$success = '';

// Xử lý đặt hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_nguoi_nhan = sanitize($_POST['ten_nguoi_nhan']);
    $so_dien_thoai_nhan = sanitize($_POST['so_dien_thoai_nhan']);
    $dia_chi_giao_hang = sanitize($_POST['dia_chi_giao_hang']);
    $phuong_thuc_thanh_toan = sanitize($_POST['phuong_thuc_thanh_toan']);
    $ghi_chu = sanitize($_POST['ghi_chu']);
    
    if (empty($ten_nguoi_nhan) || empty($so_dien_thoai_nhan) || empty($dia_chi_giao_hang)) {
        $error = 'Vui lòng điền đầy đủ thông tin giao hàng!';
    } else {
        // Tạo mã đơn hàng
        $ma_don_hang = 'PTS' . date('Ymd') . sprintf('%04d', rand(1, 9999));
        
        // Kiểm tra tồn kho
        $stockError = false;
        foreach ($cartItems as $item) {
            if ($item['so_luong'] > $item['so_luong_ton']) {
                $stockError = true;
                break;
            }
        }
        
        if ($stockError) {
            $error = 'Một số sản phẩm trong giỏ hàng không đủ số lượng!';
        } else {
            // Tạo đơn hàng
            $don_hang_id = $db->insert("
                INSERT INTO don_hang (ma_don_hang, nguoi_dung_id, tong_tien, phuong_thuc_thanh_toan, 
                                     dia_chi_giao_hang, so_dien_thoai_nhan, ten_nguoi_nhan, ghi_chu)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ", [$ma_don_hang, $nguoi_dung_id, $tongTien, $phuong_thuc_thanh_toan, 
                $dia_chi_giao_hang, $so_dien_thoai_nhan, $ten_nguoi_nhan, $ghi_chu]);
            
            if ($don_hang_id) {
                // Thêm chi tiết đơn hàng
                foreach ($cartItems as $item) {
                    $thanh_tien = $item['gia'] * $item['so_luong'];
                    $db->insert("
                        INSERT INTO chi_tiet_don_hang (don_hang_id, san_pham_id, ten_san_pham, gia, so_luong, thanh_tien)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ", [$don_hang_id, $item['san_pham_id'], $item['ten_san_pham'], 
                        $item['gia'], $item['so_luong'], $thanh_tien]);
                    
                    // Cập nhật tồn kho và lượt bán
                    $db->execute("
                        UPDATE san_pham 
                        SET so_luong_ton = so_luong_ton - ?, luot_ban = luot_ban + ?
                        WHERE id = ?
                    ", [$item['so_luong'], $item['so_luong'], $item['san_pham_id']]);
                }
                
                // Xóa giỏ hàng
                $db->execute("DELETE FROM gio_hang WHERE nguoi_dung_id = ?", [$nguoi_dung_id]);
                
                // Chuyển đến trang thành công
                redirect("thanh_cong.php?ma_don_hang=" . $ma_don_hang);
            } else {
                $error = 'Có lỗi xảy ra khi tạo đơn hàng!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - Phát Technology Spirit</title>
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

        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        .checkout-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-md);
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gray-200);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 20px;
            flex: 1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--gray-800);
            font-weight: 500;
        }

        .required {
            color: var(--error);
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .payment-option {
            position: relative;
        }

        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payment-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
        }

        .payment-option input[type="radio"]:checked + .payment-label {
            border-color: var(--primary);
            background: rgba(102, 126, 234, 0.1);
        }

        .payment-icon {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .order-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-md);
            height: fit-content;
            position: sticky;
            top: 120px;
        }

        .summary-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 20px;
            text-align: center;
        }

        .order-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
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

        .item-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .item-details {
            color: var(--gray-600);
            font-size: 0.8rem;
        }

        .item-price {
            font-weight: 700;
            color: var(--error);
            text-align: right;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
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

        .place-order-btn {
            width: 100%;
            padding: 15px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
        }

        .place-order-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .error-message {
            background: #fed7d7;
            color: var(--error);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #feb2b2;
        }

        .back-cart {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .back-cart:hover {
            gap: 15px;
        }

        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
            
            .form-row {
                flex-direction: column;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header_simple.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Thanh Toán</h1>
        </div>
        
        <a href="gio_hang.php" class="back-cart">
            <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
        </a>
        
        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
        <?php endif; ?>
        
        <div class="checkout-container">
            <form method="POST" class="checkout-form">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i> Thông tin người nhận
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ten_nguoi_nhan">
                                Họ và tên <span class="required">*</span>
                            </label>
                            <input type="text" id="ten_nguoi_nhan" name="ten_nguoi_nhan" class="form-control" required
                                   value="<?= isset($_POST['ten_nguoi_nhan']) ? sanitize($_POST['ten_nguoi_nhan']) : sanitize($nguoiDung['ho_ten']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="so_dien_thoai_nhan">
                                Số điện thoại <span class="required">*</span>
                            </label>
                            <input type="tel" id="so_dien_thoai_nhan" name="so_dien_thoai_nhan" class="form-control" required
                                   value="<?= isset($_POST['so_dien_thoai_nhan']) ? sanitize($_POST['so_dien_thoai_nhan']) : sanitize($nguoiDung['so_dien_thoai']) ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="dia_chi_giao_hang">
                            Địa chỉ giao hàng <span class="required">*</span>
                        </label>
                        <textarea id="dia_chi_giao_hang" name="dia_chi_giao_hang" class="form-control" rows="3" required><?= isset($_POST['dia_chi_giao_hang']) ? sanitize($_POST['dia_chi_giao_hang']) : ($nguoiDung['dia_chi'] ? sanitize($nguoiDung['dia_chi']) : '') ?></textarea>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-credit-card"></i> Phương thức thanh toán
                    </h3>
                    
                    <div class="payment-methods">
                        <div class="payment-option">
                            <input type="radio" id="tien_mat" name="phuong_thuc_thanh_toan" value="tien_mat" checked>
                            <label for="tien_mat" class="payment-label">
                                <i class="fas fa-money-bill-wave payment-icon"></i>
                                <span>Tiền mặt (COD)</span>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" id="chuyen_khoan" name="phuong_thuc_thanh_toan" value="chuyen_khoan">
                            <label for="chuyen_khoan" class="payment-label">
                                <i class="fas fa-university payment-icon"></i>
                                <span>Chuyển khoản</span>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" id="the_tin_dung" name="phuong_thuc_thanh_toan" value="the_tin_dung">
                            <label for="the_tin_dung" class="payment-label">
                                <i class="fas fa-credit-card payment-icon"></i>
                                <span>Thẻ tín dụng</span>
                            </label>
                        </div>
                        
                        <div class="payment-option">
                            <input type="radio" id="vi_dien_tu" name="phuong_thuc_thanh_toan" value="vi_dien_tu">
                            <label for="vi_dien_tu" class="payment-label">
                                <i class="fas fa-mobile-alt payment-icon"></i>
                                <span>Ví điện tử</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-sticky-note"></i> Ghi chú đơn hàng
                    </h3>
                    
                    <div class="form-group">
                        <textarea id="ghi_chu" name="ghi_chu" class="form-control" rows="3" 
                                  placeholder="Ghi chú thêm về đơn hàng (tùy chọn)"><?= isset($_POST['ghi_chu']) ? sanitize($_POST['ghi_chu']) : '' ?></textarea>
                    </div>
                </div>
                
                <button type="submit" class="place-order-btn">
                    <i class="fas fa-check-circle"></i> Đặt hàng ngay
                </button>
            </form>
            
            <div class="order-summary">
                <h3 class="summary-title">Đơn hàng của bạn</h3>
                
                <?php foreach ($cartItems as $item): ?>
                <div class="order-item">
                    <div class="item-image">
                        <img src="<?= $item['hinh_anh_chinh'] ?>" alt="<?= sanitize($item['ten_san_pham']) ?>">
                    </div>
                    <div class="item-info">
                        <div class="item-name"><?= sanitize($item['ten_san_pham']) ?></div>
                        <div class="item-details">
                            <?= sanitize($item['thuong_hieu']) ?> • SL: <?= $item['so_luong'] ?>
                        </div>
                    </div>
                    <div class="item-price">
                        <?= formatPrice($item['gia'] * $item['so_luong']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <span><?= formatPrice($tongTien) ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span style="color: var(--success);">Miễn phí</span>
                </div>
                
                <div class="summary-total">
                    <span>Tổng cộng:</span>
                    <span><?= formatPrice($tongTien) ?></span>
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--gray-200);">
                    <h4 style="margin-bottom: 10px; color: var(--dark);">Chính sách:</h4>
                    <ul style="color: var(--gray-600); font-size: 0.9rem; line-height: 1.6; list-style: none;">
                        <li><i class="fas fa-check" style="color: var(--success); margin-right: 8px;"></i> Miễn phí giao hàng toàn quốc</li>
                        <li><i class="fas fa-check" style="color: var(--success); margin-right: 8px;"></i> Bảo hành chính hãng</li>
                        <li><i class="fas fa-check" style="color: var(--success); margin-right: 8px;"></i> Đổi trả trong 7 ngày</li>
                        <li><i class="fas fa-check" style="color: var(--success); margin-right: 8px;"></i> Hỗ trợ kỹ thuật 24/7</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>