<?php
require_once 'ket_noi.php';
startSession();

$error = '';

if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $mat_khau = $_POST['mat_khau'];
    
    if (empty($email) || empty($mat_khau)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } else {
        $nguoi_dung = $db->selectOne(
            "SELECT * FROM nguoi_dung WHERE email = ? AND trang_thai = 'hoat_dong'", 
            [$email]
        );
        
        if ($nguoi_dung && password_verify($mat_khau, $nguoi_dung['mat_khau'])) {
            $_SESSION['nguoi_dung_id'] = $nguoi_dung['id'];
            $_SESSION['ho_ten'] = $nguoi_dung['ho_ten'];
            $_SESSION['email'] = $nguoi_dung['email'];
            $_SESSION['vai_tro'] = $nguoi_dung['vai_tro'];
            
            // Cập nhật lần đăng nhập cuối
            $db->execute(
                "UPDATE nguoi_dung SET lan_dang_nhap_cuoi = NOW() WHERE id = ?", 
                [$nguoi_dung['id']]
            );
            
            redirect('index.php');
        } else {
            $error = 'Email hoặc mật khẩu không đúng!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Phát Technology Spirit</title>
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

        .login-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
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

        .form-group {
            margin-bottom: 20px;
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

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-home">
        <i class="fas fa-arrow-left"></i>
    </a>
    
    <div class="login-container">
        <div class="logo">
            <i class="fas fa-microchip"></i>
            <h1>Phát Technology Spirit</h1>
            <p style="color: var(--gray-600); margin-top: 5px;">Đăng nhập vào tài khoản</p>
        </div>
        
        <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <input type="email" id="email" name="email" class="form-control" required 
                       value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>" 
                       placeholder="Nhập email của bạn">
            </div>
            
            <div class="form-group">
                <label for="mat_khau">
                    <i class="fas fa-lock"></i> Mật khẩu
                </label>
                <input type="password" id="mat_khau" name="mat_khau" class="form-control" required 
                       placeholder="Nhập mật khẩu của bạn">
            </div>
            
            <button type="submit" class="btn-primary">
                <i class="fas fa-sign-in-alt"></i> Đăng Nhập
            </button>
        </form>
        
        <div class="form-footer">
            <p>Chưa có tài khoản? <a href="dang_ky.php">Đăng ký ngay</a></p>
            <p style="margin-top: 10px;"><a href="#">Quên mật khẩu?</a></p>
        </div>
    </div>
</body>
</html>