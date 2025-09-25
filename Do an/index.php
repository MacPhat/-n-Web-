<?php
require_once 'ket_noi.php';
startSession();

// Lấy dữ liệu banner
$banners = $db->select("SELECT * FROM banner WHERE trang_thai = 'hien' ORDER BY thu_tu ASC LIMIT 5");

// Lấy sản phẩm nổi bật
$sanPhamNoiBat = $db->select("
    SELECT sp.*, dm.ten_danh_muc 
    FROM san_pham sp 
    LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id 
    WHERE sp.noi_bat = 1 AND sp.trang_thai = 'con_hang' 
    ORDER BY sp.luot_xem DESC 
    LIMIT 8
");

// Lấy sản phẩm mới nhất
$sanPhamMoi = $db->select("
    SELECT sp.*, dm.ten_danh_muc 
    FROM san_pham sp 
    LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id 
    WHERE sp.trang_thai = 'con_hang' 
    ORDER BY sp.ngay_tao DESC 
    LIMIT 8
");

// Lấy danh mục
$danhMucs = $db->select("SELECT * FROM danh_muc WHERE trang_thai = 'hien' ORDER BY thu_tu ASC");

// Lấy sản phẩm bán chạy
$sanPhamBanChay = $db->select("
    SELECT sp.*, dm.ten_danh_muc 
    FROM san_pham sp 
    LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id 
    WHERE sp.trang_thai = 'con_hang' 
    ORDER BY sp.luot_ban DESC 
    LIMIT 8
");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phát Technology Spirit - Shop Thiết Bị Thông Minh Hàng Đầu Việt Nam</title>
    <meta name="description" content="Phát Technology Spirit - Chuỗi cửa hàng thiết bị thông minh uy tín với iPhone, MacBook, Samsung, gaming gear và smart home. Giao hàng nhanh, bảo hành chính hãng.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="https://images.pexels.com/photos/788946/pexels-photo-788946.jpeg">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --secondary: #764ba2;
            --accent: #f093fb;
            --success: #48bb78;
            --warning: #ed8936;
            --error: #f56565;
            --dark: #1a202c;
            --light: #f7fafc;
            --gray-100: #f5f5f5;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e0;
            --gray-400: #a0aec0;
            --gray-500: #718096;
            --gray-600: #4a5568;
            --gray-700: #2d3748;
            --gray-800: #1a202c;
            
            --gradient-primary: linear-gradient(135deg, var(--primary), var(--secondary));
            --gradient-accent: linear-gradient(135deg, var(--accent), var(--primary));
            --gradient-dark: linear-gradient(135deg, var(--dark), var(--gray-700));
            
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.2);
            --shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.25);
            
            --border-radius: 12px;
            --border-radius-lg: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--gray-800);
            background: var(--light);
            overflow-x: hidden;
        }

        /* Header Styles */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--gray-200);
            z-index: 1000;
            transition: var(--transition);
        }

        .header.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: var(--shadow-lg);
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 800;
            font-size: 1.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .logo i {
            font-size: 2rem;
            margin-right: 0.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .search-container {
            flex: 1;
            max-width: 600px;
            margin: 0 2rem;
            position: relative;
        }

        .search-box {
            width: 100%;
            padding: 12px 50px 12px 20px;
            border: 2px solid var(--gray-200);
            border-radius: 50px;
            font-size: 1rem;
            background: white;
            transition: var(--transition);
        }

        .search-box:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--gradient-primary);
            border: none;
            padding: 8px 12px;
            border-radius: 50px;
            color: white;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-btn:hover {
            transform: translateY(-50%) scale(1.05);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-btn {
            position: relative;
            padding: 12px;
            background: transparent;
            border: none;
            color: var(--gray-600);
            font-size: 1.2rem;
            cursor: pointer;
            transition: var(--transition);
            border-radius: 12px;
        }

        .header-btn:hover {
            background: var(--gray-100);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--error);
            color: white;
            border-radius: 50px;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: 600;
            min-width: 18px;
            text-align: center;
        }

        .auth-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-outline {
            padding: 8px 16px;
            border: 2px solid var(--primary);
            background: transparent;
            color: var(--primary);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }

        .btn-primary {
            padding: 8px 16px;
            background: var(--gradient-primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Hero Section */
        .hero {
            margin-top: 80px;
            position: relative;
            overflow: hidden;
        }

        .hero-slider {
            position: relative;
            height: 600px;
        }

        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .hero-slide.active {
            opacity: 1;
        }

        .hero-slide::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8));
        }

        .hero-content {
            position: relative;
            text-align: center;
            color: white;
            max-width: 800px;
            padding: 0 2rem;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-cta {
            display: inline-flex;
            padding: 16px 32px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
        }

        .hero-cta:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
            box-shadow: var(--shadow-xl);
        }

        /* Floating Elements */
        .floating-element {
            position: absolute;
            pointer-events: none;
            opacity: 0.6;
        }

        .float-1 {
            top: 20%;
            left: 10%;
            animation: float 6s ease-in-out infinite;
        }

        .float-2 {
            top: 60%;
            right: 15%;
            animation: float 8s ease-in-out infinite reverse;
        }

        .float-3 {
            bottom: 20%;
            left: 15%;
            animation: float 7s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Category Section */
        .categories {
            padding: 80px 0;
            background: white;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
            position: relative;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: var(--gray-600);
            max-width: 600px;
            margin: 0 auto;
        }

        .view-all-btn {
            position: absolute;
            top: 0;
            right: 0;
            background: var(--gradient-primary);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .category-card {
            position: relative;
            height: 300px;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            cursor: pointer;
            transition: var(--transition);
            background-size: cover;
            background-position: center;
            transform-style: preserve-3d;
        }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.6));
            transition: var(--transition);
        }

        .category-card:hover {
            transform: translateY(-10px) rotateX(5deg) rotateY(5deg);
            box-shadow: var(--shadow-xl);
        }

        .category-card:hover::before {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.7), rgba(118, 75, 162, 0.7));
        }

        .category-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 2rem;
            color: white;
            transform: translateY(20px);
            transition: var(--transition);
        }

        .category-card:hover .category-content {
            transform: translateY(0);
        }

        .category-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .category-desc {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        /* Product Grid */
        .products {
            padding: 80px 0;
            background: var(--gray-100);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: white;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            transition: var(--transition);
            position: relative;
            transform-style: preserve-3d;
        }

        .product-card:hover {
            transform: translateY(-15px) rotateX(10deg);
            box-shadow: var(--shadow-xl);
        }

        .product-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-image a {
            display: block;
            width: 100%;
            height: 100%;
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .quick-view {
            background: white;
            color: var(--primary);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .quick-view:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: var(--error);
            color: white;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }

        .product-actions {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            opacity: 0;
            transform: translateX(20px);
            transition: var(--transition);
        }

        .product-card:hover .product-actions {
            opacity: 1;
            transform: translateX(0);
        }

        .action-btn {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: var(--gray-600);
        }

        .action-btn:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-brand {
            color: var(--gray-500);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .product-title a {
            color: inherit;
            text-decoration: none;
            transition: var(--transition);
        }

        .product-title a:hover {
            color: var(--primary);
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .stars {
            color: #fbbf24;
        }

        .rating-text {
            color: var(--gray-500);
            font-size: 0.9rem;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .current-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--error);
        }

        .original-price {
            font-size: 1rem;
            color: var(--gray-500);
            text-decoration: line-through;
        }

        .add-to-cart {
            width: 100%;
            padding: 12px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .add-to-cart:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* AI Chatbot */
        .chatbot-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }

        .chatbot-toggle {
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            animation: pulse 2s infinite;
        }

        .chatbot-toggle:hover {
            transform: scale(1.1);
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(102, 126, 234, 0); }
            100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0); }
        }

        .chatbot-window {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            transform: translateY(20px) scale(0.9);
            opacity: 0;
            transition: var(--transition);
            pointer-events: none;
        }

        .chatbot-window.active {
            transform: translateY(0) scale(1);
            opacity: 1;
            pointer-events: all;
        }

        .chatbot-header {
            background: var(--gradient-primary);
            color: white;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chatbot-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .chatbot-messages {
            height: 360px;
            padding: 1rem;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            animation: fadeInUp 0.3s ease;
        }

        .message.user {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }

        .message.bot .message-avatar {
            background: var(--primary);
            color: white;
        }

        .message.user .message-avatar {
            background: var(--gray-300);
            color: var(--dark);
        }

        .message-content {
            background: var(--gray-100);
            padding: 0.75rem;
            border-radius: 12px;
            max-width: 80%;
        }

        .message.user .message-content {
            background: var(--primary);
            color: white;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chatbot-input {
            padding: 1rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: 0.5rem;
        }

        .chatbot-input input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid var(--gray-300);
            border-radius: 20px;
            outline: none;
        }

        .chatbot-input button {
            padding: 8px 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            transition: var(--transition);
        }

        .chatbot-input button:hover {
            background: var(--primary-dark);
        }

        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 60px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-section h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .footer-section p,
        .footer-section a {
            color: var(--gray-300);
            text-decoration: none;
            line-height: 1.8;
            transition: var(--transition);
        }

        .footer-section a:hover {
            color: var(--accent);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .social-link:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 2rem;
            text-align: center;
            color: var(--gray-400);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .header-container {
                padding: 0 1rem;
            }
            
            .search-container {
                margin: 0 1rem;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                height: 70px;
            }
            
            .search-container {
                display: none;
            }
            
            .logo {
                font-size: 1.2rem;
            }
            
            .hero {
                margin-top: 70px;
            }
            
            .hero-slider {
                height: 400px;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .categories-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }
            
            .category-card {
                height: 200px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1rem;
            }
            
            .chatbot-window {
                width: 300px;
                height: 450px;
            }
            
            .container {
                padding: 0 1rem;
            }

            body {
                padding-top: 80px;
            }
            
            .view-all-btn {
                position: static;
                margin-top: 20px;
                align-self: center;
            }
            
            .section-header {
                display: flex;
                flex-direction: column;
                align-items: center;
            }
        }

        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Smooth scroll */
        html {
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        /* Parallax background */
        .parallax-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            opacity: 0.05;
            z-index: -1;
            transform: translateZ(-1px) scale(2);
        }
    </style>
</head>
<body>
    <!-- Parallax Background -->
    <div class="parallax-bg"></div>

    <!-- Header -->
    <header class="header" id="header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <i class="fas fa-microchip"></i>
                Phát Technology Spirit
            </a>
            
            
            
            <div class="header-actions">
                <button class="header-btn" onclick="window.location='gio_hang.php'">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (isLoggedIn()): ?>
                        <?php 
                        $cartCount = $db->selectOne("SELECT COUNT(*) as count FROM gio_hang WHERE nguoi_dung_id = ?", [$_SESSION['nguoi_dung_id']]);
                        if ($cartCount['count'] > 0):
                        ?>
                        <span class="cart-count"><?= $cartCount['count'] ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </button>
                
                <?php if (isLoggedIn()): ?>
                    <div class="auth-buttons">
                        <a href="tai_khoan.php" class="btn-outline">
                            <i class="fas fa-user"></i> <?= sanitize($_SESSION['ho_ten']) ?>
                        </a>
                        <a href="dang_xuat.php" class="btn-primary">Đăng xuất</a>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="dang_nhap.php" class="btn-outline">Đăng nhập</a>
                        <a href="dang_ky.php" class="btn-primary">Đăng ký</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-slider">
            <?php foreach ($banners as $index => $banner): ?>
            <div class="hero-slide <?= $index === 0 ? 'active' : '' ?>" style="background-image: url('<?= $banner['hinh_anh'] ?>')">
                <div class="hero-content">
                    <h1 class="hero-title" data-aos="fade-up"><?= sanitize($banner['tieu_de']) ?></h1>
                    <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="200"><?= sanitize($banner['mo_ta']) ?></p>
                    
                </div>
                
                <!-- Floating Elements -->
                <div class="floating-element float-1">
                    <i class="fas fa-mobile-alt" style="font-size: 3rem; color: rgba(255,255,255,0.3);"></i>
                </div>
                <div class="floating-element float-2">
                    <i class="fas fa-laptop" style="font-size: 4rem; color: rgba(255,255,255,0.2);"></i>
                </div>
                <div class="floating-element float-3">
                    <i class="fas fa-headphones" style="font-size: 2.5rem; color: rgba(255,255,255,0.4);"></i>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <!-- Featured Products -->
    <section class="products">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
                <p class="section-subtitle">Những sản phẩm được yêu thích nhất với công nghệ tiên tiến và chất lượng vượt trội</p>
                
            </div>
            
            <div class="products-grid">
                <?php foreach ($sanPhamNoiBat as $index => $sanPham): ?>
                <div class="product-card" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <div class="product-image">
                        <a href="chi_tiet_san_pham.php?id=<?= $sanPham['id'] ?>">
                            <img src="<?= $sanPham['hinh_anh_chinh'] ?>" alt="<?= sanitize($sanPham['ten_san_pham']) ?>">
                        </a>
                        
                        <?php if ($sanPham['giam_gia_percent'] > 0): ?>
                        <div class="product-badge">-<?= $sanPham['giam_gia_percent'] ?>%</div>
                        <?php endif; ?>
                        
                        <div class="product-overlay">
                            <a href="chi_tiet_san_pham.php?id=<?= $sanPham['id'] ?>" class="quick-view">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        
                        <div class="product-actions">
                            <button class="action-btn" onclick="addToWishlist(<?= $sanPham['id'] ?>)">
                                <i class="far fa-heart"></i>
                            </button>
                            <button class="action-btn" onclick="quickView(<?= $sanPham['id'] ?>)">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="product-info">
                        <div class="product-brand"><?= sanitize($sanPham['thuong_hieu']) ?></div>
                        <h3 class="product-title">
                            <a href="chi_tiet_san_pham.php?id=<?= $sanPham['id'] ?>"><?= sanitize($sanPham['ten_san_pham']) ?></a>
                        </h3>
                        
                        <div class="product-rating">
                            <div class="stars">
                                <?php 
                                $rating = $sanPham['diem_danh_gia'];
                                for ($i = 1; $i <= 5; $i++): 
                                ?>
                                    <i class="<?= $i <= $rating ? 'fas' : 'far' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text">(<?= $sanPham['so_danh_gia'] ?> đánh giá)</span>
                        </div>
                        
                        <div class="product-price">
                            <span class="current-price"><?= formatPrice($sanPham['gia']) ?></span>
                            <?php if ($sanPham['gia_goc'] && $sanPham['gia_goc'] > $sanPham['gia']): ?>
                            <span class="original-price"><?= formatPrice($sanPham['gia_goc']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <button class="add-to-cart" onclick="addToCart(<?= $sanPham['id'] ?>)">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="text-align: center; margin-top: 3rem;" data-aos="fade-up">
                
            </div>
        </div>
    </section>

    <!-- New Products -->
    <section class="products" style="background: white;">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2 class="section-title">Sản Phẩm Mới Nhất</h2>
                <p class="section-subtitle">Cập nhật những sản phẩm công nghệ mới nhất từ các thương hiệu hàng đầu</p>
            </div>
            
            <div class="products-grid">
                <?php foreach ($sanPhamMoi as $index => $sanPham): ?>
                <div class="product-card" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                    <div class="product-image">
                        <img src="<?= $sanPham['hinh_anh_chinh'] ?>" alt="<?= sanitize($sanPham['ten_san_pham']) ?>">
                        
                        <div class="product-badge" style="background: var(--success);">Mới</div>
                        
                        <div class="product-actions">
                            <button class="action-btn" onclick="addToWishlist(<?= $sanPham['id'] ?>)">
                                <i class="far fa-heart"></i>
                            </button>
                            <button class="action-btn" onclick="quickView(<?= $sanPham['id'] ?>)">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="product-info">
                        <div class="product-brand"><?= sanitize($sanPham['thuong_hieu']) ?></div>
                        <h3 class="product-title"><?= sanitize($sanPham['ten_san_pham']) ?></h3>
                        
                        <div class="product-rating">
                            <div class="stars">
                                <?php 
                                $rating = $sanPham['diem_danh_gia'];
                                for ($i = 1; $i <= 5; $i++): 
                                ?>
                                    <i class="<?= $i <= $rating ? 'fas' : 'far' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text">(<?= $sanPham['so_danh_gia'] ?> đánh giá)</span>
                        </div>
                        
                        <div class="product-price">
                            <span class="current-price"><?= formatPrice($sanPham['gia']) ?></span>
                            <?php if ($sanPham['gia_goc'] && $sanPham['gia_goc'] > $sanPham['gia']): ?>
                            <span class="original-price"><?= formatPrice($sanPham['gia_goc']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <button class="add-to-cart" onclick="addToCart(<?= $sanPham['id'] ?>)">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- AI Chatbot -->
    <div class="chatbot-container">
        <button class="chatbot-toggle" onclick="toggleChatbot()">
            <i class="fas fa-robot"></i>
        </button>
        
        <div class="chatbot-window" id="chatbot-window">
            <div class="chatbot-header">
                <div class="chatbot-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <h4>AI Assistant</h4>
                    <small>Hỗ trợ 24/7</small>
                </div>
            </div>
            
            <div class="chatbot-messages" id="chatbot-messages">
                <div class="message bot">
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        Xin chào! Tôi là AI Assistant của Phát Technology Spirit. Tôi có thể giúp bạn tìm kiếm sản phẩm, tư vấn mua hàng và trả lời các câu hỏi. Bạn cần hỗ trợ gì?
                    </div>
                </div>
            </div>
            
            <div class="chatbot-input">
                <input type="text" id="chatbot-input" placeholder="Nhập tin nhắn...">
                <button onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Phát Technology Spirit</h3>
                    <p>Chuỗi cửa hàng thiết bị công nghệ hàng đầu Việt Nam với hơn 10 năm kinh nghiệm. Chúng tôi cam kết mang đến những sản phẩm chính hãng, chất lượng cao với dịch vụ tốt nhất.</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Danh Mục</h3>
                    <?php foreach ($danhMucs as $danhMuc): ?>
                    <p><a href="danh_sach_san_pham.php?danh_muc=<?= $danhMuc['id'] ?>"><?= sanitize($danhMuc['ten_danh_muc']) ?></a></p>
                    <?php endforeach; ?>
                </div>
                
                <div class="footer-section">
                    <h3>Chính Sách</h3>
                    <p><a href="#">Chính sách bảo hành</a></p>
                    <p><a href="#">Chính sách đổi trả</a></p>
                    <p><a href="#">Chính sách giao hàng</a></p>
                    <p><a href="#">Chính sách bảo mật</a></p>
                    <p><a href="#">Điều khoản sử dụng</a></p>
                </div>
                
                <div class="footer-section">
                    <h3>Liên Hệ</h3>
                    <p><i class="fas fa-map-marker-alt"></i> 111 P.Vĩnh Hưng, Q.Hoàng Mai, Hà Nội</p>
                    <p><i class="fas fa-phone"></i> 0123 456 789</p>
                    <p><i class="fas fa-envelope"></i> contact@phat-tech.com</p>
                    <p><i class="fas fa-clock"></i> 8:00 - 22:00 (Thứ 2 - CN)</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2024 Phát Technology Spirit. Tất cả quyền được bảo lưu. Thiết kế bởi AI Technology.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });

        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.getElementById('header');
            if (window.scrollY > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Hero slider
        let currentSlide = 0;
        const slides = document.querySelectorAll('.hero-slide');
        const totalSlides = slides.length;

        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % totalSlides;
            slides[currentSlide].classList.add('active');
        }

        if (totalSlides > 1) {
            setInterval(nextSlide, 5000);
        }

        // Chatbot functionality
        let chatbotOpen = false;

        function toggleChatbot() {
            const chatbotWindow = document.getElementById('chatbot-window');
            chatbotOpen = !chatbotOpen;
            chatbotWindow.classList.toggle('active', chatbotOpen);
        }

        function sendMessage() {
            const input = document.getElementById('chatbot-input');
            const message = input.value.trim();
            
            if (message === '') return;
            
            // Add user message
            addMessage(message, 'user');
            input.value = '';
            
            // Simulate AI response
            setTimeout(() => {
                const response = generateAIResponse(message);
                addMessage(response, 'bot');
            }, 1000);
        }

        function addMessage(content, sender) {
            const messagesContainer = document.getElementById('chatbot-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-${sender === 'user' ? 'user' : 'robot'}"></i>
                </div>
                <div class="message-content">${content}</div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function generateAIResponse(message) {
            const lowerMessage = message.toLowerCase();
            
            // Product recommendations
            if (lowerMessage.includes('iphone') || lowerMessage.includes('điện thoại')) {
                return 'Tôi khuyên bạn nên xem iPhone 15 Pro Max hoặc Samsung Galaxy S24 Ultra. Cả hai đều có hiệu năng mạnh mẽ và camera tuyệt vời. Bạn có muốn tôi so sánh chi tiết không?';
            }
            
            if (lowerMessage.includes('laptop') || lowerMessage.includes('macbook')) {
                return 'MacBook Pro M3 và Dell XPS 13 Plus đang rất được ưa chuộng. MacBook phù hợp cho creative work, Dell XPS phù hợp cho business. Bạn dùng chủ yếu để làm gì?';
            }
            
            if (lowerMessage.includes('tai nghe') || lowerMessage.includes('headphone')) {
                return 'AirPods Pro 3 và Bose QuietComfort 45 đều là lựa chọn tuyệt vời. AirPods tích hợp tốt với Apple, Bose có chống ồn hàng đầu. Bạn ưu tiên tính năng nào?';
            }
            
            if (lowerMessage.includes('gaming') || lowerMessage.includes('game')) {
                return 'PlayStation 5 Pro và Xbox Series X đều là console tuyệt vời. PS5 Pro có exclusive games hay, Xbox có Game Pass đa dạng. Bạn thích thể loại game nào?';
            }
            
            // Price questions
            if (lowerMessage.includes('giá') || lowerMessage.includes('bao nhiêu')) {
                return 'Chúng tôi có nhiều mức giá phù hợp mọi ngân sách. Từ 5 triệu cho tai nghe cao cấp đến 60 triệu cho laptop workstation. Bạn có thể cho biết ngân sách dự kiến không?';
            }
            
            // Warranty questions
            if (lowerMessage.includes('bảo hành') || lowerMessage.includes('warranty')) {
                return 'Tất cả sản phẩm đều có bảo hành chính hãng từ 12-24 tháng. Chúng tôi hỗ trợ bảo hành tại cửa hàng và có dịch vụ pickup miễn phí trong nội thành TP.HCM.';
            }
            
            // Shipping questions
            if (lowerMessage.includes('giao hàng') || lowerMessage.includes('ship')) {
                return 'Chúng tôi giao hàng toàn quốc. Nội thành TP.HCM: 2-4 giờ. Các tỉnh thành khác: 1-3 ngày. Miễn phí ship cho đơn hàng trên 2 triệu đồng.';
            }
            
            // General greetings
            if (lowerMessage.includes('xin chào') || lowerMessage.includes('hello') || lowerMessage.includes('hi')) {
                return 'Xin chào! Cảm ơn bạn đã quan tâm đến Phát Technology Spirit. Tôi có thể giúp bạn tìm hiểu về sản phẩm, giá cả, bảo hành hay bất kỳ thông tin nào khác. Bạn muốn hỏi gì?';

            }
            if (lowerMessage.includes('dien thoai') || lowerMessage.includes('samsung')) {
                return 'Samsung Galaxy S24 Ultra 512GB là flagship cao cấp của Samsung với khung viền titanium bền bỉ, màn hình Dynamic AMOLED 2X 6.8 inch QHD+ 120Hz siêu mượt. Máy trang bị chip Snapdragon 8 Gen 3 for Galaxy, dung lượng 512GB, camera chính 200MP với khả năng zoom 100x và quay video 8K. Pin 5000mAh hỗ trợ sạc nhanh 45W, đi kèm bút S Pen tiện lợi, là lựa chọn hàng đầu cho người cần hiệu năng mạnh, chụp ảnh đẹp và lưu trữ lớn. ';
            }
            if (lowerMessage.includes('google nest') || lowerMessage.includes('google')) {
                return 'Samsung Galaxy S24 Ultra 512GB là flagship cao cấp của Samsung với khung viền titanium bền bỉ, màn hình Dynamic AMOLED 2X 6.8 inch QHD+ 120Hz siêu mượt. Máy trang bị chip Snapdragon 8 Gen 3 for Galaxy, dung lượng 512GB, camera chính 200MP với khả năng zoom 100x và quay video 8K. Pin 5000mAh hỗ trợ sạc nhanh 45W, đi kèm bút S Pen tiện lợi, là lựa chọn hàng đầu cho người cần hiệu năng mạnh, chụp ảnh đẹp và lưu trữ lớn. ';
            }
            // Default response
            return 'Cảm ơn bạn đã liên hệ! Để được hỗ trợ tốt nhất, bạn có thể gọi hotline 0123 456 789 hoặc ghé thăm cửa hàng 111 P.Vĩnh Hưng, Q.Hoàng Mai, Hà Nội. Cảm ơn quý khách đã quan tâm và ủng hộ !';

        }

        // Allow Enter key to send message
        document.getElementById('chatbot-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Shopping cart functions
        function addToCart(productId) {
            <?php if (!isLoggedIn()): ?>
                alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
                window.location.href = 'dang_nhap.php';
                return;
            <?php endif; ?>
            
            fetch('xu_ly_gio_hang.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = data.cartCount;
                    } else if (data.cartCount > 0) {
                        const cartBtn = document.querySelector('.header-btn');
                        cartBtn.innerHTML += `<span class="cart-count">${data.cartCount}</span>`;
                    }
                    
                    // Show success message
                    showNotification('Đã thêm sản phẩm vào giỏ hàng!', 'success');
                } else {
                    showNotification(data.message || 'Có lỗi xảy ra!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra!', 'error');
            });
        }

        function addToWishlist(productId) {
            // Wishlist functionality can be implemented later
            showNotification('Tính năng đang phát triển!', 'info');
        }

        function quickView(productId) {
            // Quick view functionality can be implemented later
            window.location.href = `chi_tiet_san_pham.php?id=${productId}`;
        }

        // Notification system
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}"></i>
                    ${message}
                </div>
            `;
            
            // Add notification styles
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: ${type === 'success' ? '#48bb78' : type === 'error' ? '#f56565' : '#667eea'};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: var(--shadow-lg);
                z-index: 10000;
                animation: slideIn 0.3s ease;
                max-width: 300px;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Add notification animations to head
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Parallax effect
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelector('.parallax-bg');
            const speed = scrolled * 0.5;
            parallax.style.transform = `translateY(${speed}px)`;
        });

        // Loading animation for forms
        function showLoading(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="loading-spinner"></span> Đang xử lý...';
            button.disabled = true;
            
            return function hideLoading() {
                button.innerHTML = originalText;
                button.disabled = false;
            };
        }

        // Lazy loading for images
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));

        // Product card 3D effect
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 10;
                const rotateY = (centerX - x) / 10;
                
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(10px)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0px)';
            });
        });
    </script>
</body>
</html>