<?php
require_once 'ket_noi.php';
startSession();

if (!isLoggedIn()) {
    redirect('dang_nhap.php');
}

$nguoi_dung_id = $_SESSION['nguoi_dung_id'];
$error = '';
$success = '';

// Lấy thông tin người dùng
$nguoiDung = $db->selectOne("SELECT * FROM nguoi_dung WHERE id = ?", [$nguoi_dung_id]);

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_info') {
        $ho_ten = sanitize($_POST['ho_ten']);
        $so_dien_thoai = sanitize($_POST['so_dien_thoai']);
        $dia_chi = sanitize($_POST['dia_chi']);
        
        if (empty($ho_ten)) {
            $error = 'Vui lòng nhập họ tên!';
        } else {
            $updated = $db->execute(
                "UPDATE nguoi_dung SET ho_ten = ?, so_dien_thoai = ?, dia_chi = ? WHERE id = ?",
                [$ho_ten, $so_dien_thoai, $dia_chi, $nguoi_dung_id]
            );
            
            if ($updated) {
                $_SESSION['ho_ten'] = $ho_ten;
                $success = 'Cập nhật thông tin thành công!';
                $nguoiDung = $db->selectOne("SELECT * FROM nguoi_dung WHERE id = ?", [$nguoi_dung_id]);
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật!';
            }
        }
    } elseif ($action === 'change_password') {
        $mat_khau_cu = $_POST['mat_khau_cu'];
        $mat_khau_moi = $_POST['mat_khau_moi'];
        $xac_nhan_mat_khau = $_POST['xac_nhan_mat_khau'];
        
        if (empty($mat_khau_cu) || empty($mat_khau_moi)) {
            $error = 'Vui lòng điền đầy đủ thông tin!';
        } elseif (strlen($mat_khau_moi) < 6) {
            $error = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
        } elseif ($mat_khau_moi !== $xac_nhan_mat_khau) {
            $error = 'Mật khẩu xác nhận không khớp!';
        } elseif (!password_verify($mat_khau_cu, $nguoiDung['mat_khau'])) {
            $error = 'Mật khẩu cũ không đúng!';
        } else {
            $hashedPassword = password_hash($mat_khau_moi, PASSWORD_DEFAULT);
            $updated = $db->execute(
                "UPDATE nguoi_dung SET mat_khau = ? WHERE id = ?",
                [$hashedPassword, $nguoi_dung_id]
            );
            
            if ($updated) {
                $success = 'Đổi mật khẩu thành công!';
            } else {
                $error = 'Có lỗi xảy ra khi đổi mật khẩu!';
            }
        }
    }
}

// Lấy thống kê đơn hàng
$thongKeDonHang = $db->selectOne("
    SELECT 
        COUNT(*) as tong_don_hang,
        SUM(CASE WHEN trang_thai_don_hang = 'da_giao' THEN 1 ELSE 0 END) as da_giao,
        SUM(CASE WHEN trang_thai_don_hang = 'cho_xac_nhan' THEN 1 ELSE 0 END) as cho_xac_nhan,
        SUM(CASE WHEN trang_thai_don_hang = 'da_huy' THEN 1 ELSE 0 END) as da_huy
    FROM don_hang WHERE nguoi_dung_id = ?
", [$nguoi_dung_id]);

// Lấy đơn hàng gần đây
$donHangGanDay = $db->select("
    SELECT * FROM don_hang 
    WHERE nguoi_dung_id = ? 
    ORDER BY ngay_dat_hang DESC 
    LIMIT 5
", [$nguoi_dung_id]);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài Khoản Của Bạn - Phát Technology Spirit</title>
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

        .account-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        .sidebar {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-md);
            height: fit-content;
            position: sticky;
            top: 120px;
        }

        .user-info {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-200);
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
            color: white;
        }

        .user-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .user-email {
            color: var(--gray-600);
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 10px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            color: var(--gray-600);
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }

        .nav-link:hover, .nav-link.active {
            background: var(--gradient-primary);
            color: white;
        }

        .main-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-md);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--gradient-primary);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
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
            gap: 20px;
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

        .btn-primary:hover {
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

        .success-message {
            background: #c6f6d5;
            color: var(--success);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #9ae6b4;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .order-info h4 {
            color: var(--dark);
            margin-bottom: 5px;
        }

        .order-meta {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .order-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-cho-xac-nhan {
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

        @media (max-width: 768px) {
            body {
                padding-top: 80px;
            }
            
            .account-container {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                position: static;
            }
            
            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include 'header_simple.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1 class="page-title">Tài Khoản Của Bạn</h1>
        </div>
        
        <div class="account-container">
            <div class="sidebar">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-name"><?= sanitize($nguoiDung['ho_ten']) ?></div>
                    <div class="user-email"><?= sanitize($nguoiDung['email']) ?></div>
                </div>
                
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="#" class="nav-link active" onclick="showTab('dashboard')">
                            <i class="fas fa-tachometer-alt"></i> Tổng quan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="showTab('profile')">
                            <i class="fas fa-user-edit"></i> Thông tin cá nhân
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="showTab('password')">
                            <i class="fas fa-lock"></i> Đổi mật khẩu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="theo_doi_don_hang.php" class="nav-link">
                            <i class="fas fa-truck"></i> Đơn hàng của tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="dang_xuat.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="main-content">
                <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?= $success ?>
                </div>
                <?php endif; ?>
                
                <!-- Dashboard Tab -->
                <div class="tab-content active" id="dashboard">
                    <h2 class="section-title">Tổng Quan Tài Khoản</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number"><?= $thongKeDonHang['tong_don_hang'] ?></div>
                            <div class="stat-label">Tổng đơn hàng</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $thongKeDonHang['da_giao'] ?></div>
                            <div class="stat-label">Đã giao</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $thongKeDonHang['cho_xac_nhan'] ?></div>
                            <div class="stat-label">Chờ xác nhận</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?= $thongKeDonHang['da_huy'] ?></div>
                            <div class="stat-label">Đã hủy</div>
                        </div>
                    </div>
                    
                    <h3 style="margin-bottom: 20px;">Đơn hàng gần đây</h3>
                    <?php if (empty($donHangGanDay)): ?>
                    <p style="text-align: center; color: var(--gray-600); padding: 40px;">
                        Bạn chưa có đơn hàng nào
                    </p>
                    <?php else: ?>
                        <?php foreach ($donHangGanDay as $donHang): ?>
                        <div class="order-item">
                            <div class="order-info">
                                <h4>Đơn hàng #<?= $donHang['ma_don_hang'] ?></h4>
                                <div class="order-meta">
                                    <?= date('d/m/Y H:i', strtotime($donHang['ngay_dat_hang'])) ?> • 
                                    <?= formatPrice($donHang['tong_tien']) ?>
                                </div>
                            </div>
                            <div class="order-status status-<?= str_replace('_', '-', $donHang['trang_thai_don_hang']) ?>">
                                <?php
                                $status_labels = [
                                    'cho_xac_nhan' => 'Chờ xác nhận',
                                    'da_xac_nhan' => 'Đã xác nhận',
                                    'dang_chuan_bi' => 'Đang chuẩn bị',
                                    'dang_giao' => 'Đang giao',
                                    'da_giao' => 'Đã giao',
                                    'da_huy' => 'Đã hủy'
                                ];
                                echo $status_labels[$donHang['trang_thai_don_hang']] ?? 'Không xác định';
                                ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Profile Tab -->
                <div class="tab-content" id="profile">
                    <h2 class="section-title">Thông Tin Cá Nhân</h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="update_info">
                        
                        <div class="form-group">
                            <label for="ho_ten">Họ và tên</label>
                            <input type="text" id="ho_ten" name="ho_ten" class="form-control" 
                                   value="<?= sanitize($nguoiDung['ho_ten']) ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" class="form-control" 
                                       value="<?= sanitize($nguoiDung['email']) ?>" disabled>
                                <small style="color: var(--gray-600);">Email không thể thay đổi</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="so_dien_thoai">Số điện thoại</label>
                                <input type="tel" id="so_dien_thoai" name="so_dien_thoai" class="form-control" 
                                       value="<?= sanitize($nguoiDung['so_dien_thoai']) ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="dia_chi">Địa chỉ</label>
                            <textarea id="dia_chi" name="dia_chi" class="form-control" rows="3"><?= sanitize($nguoiDung['dia_chi']) ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật thông tin
                        </button>
                    </form>
                </div>
                
                <!-- Password Tab -->
                <div class="tab-content" id="password">
                    <h2 class="section-title">Đổi Mật Khẩu</h2>
                    
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="mat_khau_cu">Mật khẩu hiện tại</label>
                            <input type="password" id="mat_khau_cu" name="mat_khau_cu" class="form-control" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="mat_khau_moi">Mật khẩu mới</label>
                                <input type="password" id="mat_khau_moi" name="mat_khau_moi" class="form-control" required>
                                <small style="color: var(--gray-600);">Tối thiểu 6 ký tự</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="xac_nhan_mat_khau">Xác nhận mật khẩu mới</label>
                                <input type="password" id="xac_nhan_mat_khau" name="xac_nhan_mat_khau" class="form-control" required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Đổi mật khẩu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        
        // Password confirmation validation
        const matKhauMoi = document.getElementById('mat_khau_moi');
        const xacNhanMatKhau = document.getElementById('xac_nhan_mat_khau');
        
        function validatePassword() {
            if (matKhauMoi && xacNhanMatKhau) {
                if (matKhauMoi.value !== xacNhanMatKhau.value) {
                    xacNhanMatKhau.setCustomValidity('Mật khẩu xác nhận không khớp');
                } else {
                    xacNhanMatKhau.setCustomValidity('');
                }
            }
        }
        
        if (matKhauMoi && xacNhanMatKhau) {
            matKhauMoi.addEventListener('input', validatePassword);
            xacNhanMatKhau.addEventListener('input', validatePassword);
        }
    </script>
</body>
</html>