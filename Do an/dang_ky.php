<?php
require_once 'ket_noi.php';
startSession();

$error = '';
$success = '';

if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten = sanitize($_POST['ho_ten']);
    $email = sanitize($_POST['email']);
    $ten_dang_nhap = sanitize($_POST['ten_dang_nhap']);
    $mat_khau = $_POST['mat_khau'];
    $xac_nhan_mat_khau = $_POST['xac_nhan_mat_khau'];
    $so_dien_thoai = sanitize($_POST['so_dien_thoai']);
    
    // Validation
    if (empty($ho_ten) || empty($email) || empty($ten_dang_nhap) || empty($mat_khau)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } elseif (strlen($mat_khau) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($mat_khau !== $xac_nhan_mat_khau) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        // Check email exists
        $existingEmail = $db->selectOne("SELECT id FROM nguoi_dung WHERE email = ?", [$email]);
        if ($existingEmail) {
            $error = 'Email này đã được sử dụng!';
        } else {
            // Check username exists
            $existingUsername = $db->selectOne("SELECT id FROM nguoi_dung WHERE ten_dang_nhap = ?", [$ten_dang_nhap]);
            if ($existingUsername) {
                $error = 'Tên đăng nhập này đã được sử dụng!';
            } else {
                // Create new user
                $hashedPassword = password_hash($mat_khau, PASSWORD_DEFAULT);
                $userId = $db->insert(
                    "INSERT INTO nguoi_dung (ho_ten, email, ten_dang_nhap, mat_khau, so_dien_thoai) VALUES (?, ?, ?, ?, ?)",
                    [$ho_ten, $email, $ten_dang_nhap, $hashedPassword, $so_dien_thoai]
                );
                
                if ($userId) {
                    $success = 'Đăng ký thành công! Vui lòng đăng nhập.';
                } else {
                    $error = 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại!';
                }
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
    <title>Đăng Ký - Phát Technology Spirit</title>
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
            --gray-600: #4a5568;
            --gray-800: #1a202c;
            --gradient-primary: linear-gradient(135deg, var(--primary), var(--secondary));
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gradient-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo i {
            font-size: 3rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo h1 {
            font-size: 1.5rem;
            color: var(--dark);
            margin-top: 10px;
            font-weight: 700;
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
            border-radius: 12px;
            font-size: 16px;
            transition: var(--transition);
            background: var(--gray-100);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 14px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
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

        .form-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 24px;
            transition: var(--transition);
        }

        .back-home:hover {
            transform: translateY(-2px);
        }

        .password-strength {
            font-size: 12px;
            margin-top: 5px;
            color: var(--gray-600);
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 30px 20px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">
        <i class="fas fa-arrow-left"></i>
    </a>
    
    <div class="register-container">
        <div class="logo">
            <i class="fas fa-microchip"></i>
            <h1>Phát Technology Spirit</h1>
            <p style="color: var(--gray-600); margin-top: 5px;">Tạo tài khoản mới</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> <?= $success ?>
            <div style="margin-top: 10px;">
                <a href="dang_nhap.php" style="color: var(--success); font-weight: bold;">Đăng nhập ngay</a>
            </div>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="ho_ten">
                    <i class="fas fa-user"></i> Họ và Tên <span class="required">*</span>
                </label>
                <input type="text" id="ho_ten" name="ho_ten" class="form-control" required 
                       value="<?= isset($_POST['ho_ten']) ? sanitize($_POST['ho_ten']) : '' ?>" 
                       placeholder="Nhập họ và tên">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="ten_dang_nhap">
                        <i class="fas fa-at"></i> Tên đăng nhập <span class="required">*</span>
                    </label>
                    <input type="text" id="ten_dang_nhap" name="ten_dang_nhap" class="form-control" required 
                           value="<?= isset($_POST['ten_dang_nhap']) ? sanitize($_POST['ten_dang_nhap']) : '' ?>" 
                           placeholder="Username">
                </div>
                
                <div class="form-group">
                    <label for="so_dien_thoai">
                        <i class="fas fa-phone"></i> Số điện thoại
                    </label>
                    <input type="tel" id="so_dien_thoai" name="so_dien_thoai" class="form-control" 
                           value="<?= isset($_POST['so_dien_thoai']) ? sanitize($_POST['so_dien_thoai']) : '' ?>" 
                           placeholder="0123456789">
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email <span class="required">*</span>
                </label>
                <input type="email" id="email" name="email" class="form-control" required 
                       value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>" 
                       placeholder="email@example.com">
            </div>
            
            <div class="form-group">
                <label for="mat_khau">
                    <i class="fas fa-lock"></i> Mật khẩu <span class="required">*</span>
                </label>
                <input type="password" id="mat_khau" name="mat_khau" class="form-control" required 
                       placeholder="Tối thiểu 6 ký tự">
                <div class="password-strength">Mật khẩu nên chứa ít nhất 6 ký tự</div>
            </div>
            
            <div class="form-group">
                <label for="xac_nhan_mat_khau">
                    <i class="fas fa-lock"></i> Xác nhận mật khẩu <span class="required">*</span>
                </label>
                <input type="password" id="xac_nhan_mat_khau" name="xac_nhan_mat_khau" class="form-control" required 
                       placeholder="Nhập lại mật khẩu">
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="fas fa-user-plus"></i> Đăng Ký
            </button>
        </form>
        
        <div class="form-footer">
            <p>Đã có tài khoản? <a href="dang_nhap.php">Đăng nhập ngay</a></p>
        </div>
    </div>
    
    <script>
        // Password confirmation validation
        const matKhau = document.getElementById('mat_khau');
        const xacNhanMatKhau = document.getElementById('xac_nhan_mat_khau');
        
        function validatePassword() {
            if (matKhau.value !== xacNhanMatKhau.value) {
                xacNhanMatKhau.setCustomValidity('Mật khẩu xác nhận không khớp');
            } else {
                xacNhanMatKhau.setCustomValidity('');
            }
        }
        
        matKhau.addEventListener('input', validatePassword);
        xacNhanMatKhau.addEventListener('input', validatePassword);
    </script>
</body>
</html>