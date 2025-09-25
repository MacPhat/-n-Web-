<?php
// File kết nối cơ sở dữ liệu cho Phát Technology Spirit
class KetNoiDB {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "phat_technology_spirit";
    private $conn;
    
    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->database};charset=utf8mb4", 
                                 $this->username, 
                                 $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Phương thức thực thi truy vấn SELECT
    public function select($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Lỗi SELECT: " . $e->getMessage());
            return false;
        }
    }
    
    // Phương thức thực thi truy vấn SELECT một dòng
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("Lỗi SELECT ONE: " . $e->getMessage());
            return false;
        }
    }
    
    // Phương thức thực thi truy vấn INSERT, UPDATE, DELETE
    public function execute($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute($params);
        } catch(PDOException $e) {
            error_log("Lỗi EXECUTE: " . $e->getMessage());
            return false;
        }
    }
    
    // Phương thức thực thi INSERT và trả về ID vừa chèn
    public function insert($query, $params = []) {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            error_log("Lỗi INSERT: " . $e->getMessage());
            return false;
        }
    }
    
    // Phương thức đếm số dòng
    public function count($table, $condition = "", $params = []) {
        try {
            $query = "SELECT COUNT(*) as total FROM {$table}";
            if ($condition) {
                $query .= " WHERE {$condition}";
            }
            $result = $this->selectOne($query, $params);
            return $result['total'] ?? 0;
        } catch(PDOException $e) {
            error_log("Lỗi COUNT: " . $e->getMessage());
            return 0;
        }
    }
    
    // Phương thức tìm kiếm sản phẩm
    public function searchProducts($keyword, $limit = 10, $offset = 0) {
        $query = "SELECT sp.*, dm.ten_danh_muc 
                  FROM san_pham sp 
                  LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id 
                  WHERE MATCH(sp.ten_san_pham, sp.mo_ta_ngan) AGAINST(? IN NATURAL LANGUAGE MODE)
                     OR sp.ten_san_pham LIKE ? 
                     OR sp.thuong_hieu LIKE ?
                  ORDER BY sp.noi_bat DESC, sp.luot_xem DESC 
                  LIMIT ? OFFSET ?";
        
        $searchTerm = "%{$keyword}%";
        return $this->select($query, [$keyword, $searchTerm, $searchTerm, $limit, $offset]);
    }
    
    // Phương thức lọc sản phẩm
    public function filterProducts($filters = [], $limit = 12, $offset = 0) {
        $conditions = ["sp.trang_thai = 'con_hang'"];
        $params = [];
        
        if (!empty($filters['danh_muc_id'])) {
            $conditions[] = "sp.danh_muc_id = ?";
            $params[] = $filters['danh_muc_id'];
        }
        
        if (!empty($filters['gia_min'])) {
            $conditions[] = "sp.gia >= ?";
            $params[] = $filters['gia_min'];
        }
        
        if (!empty($filters['gia_max'])) {
            $conditions[] = "sp.gia <= ?";
            $params[] = $filters['gia_max'];
        }
        
        if (!empty($filters['thuong_hieu'])) {
            $conditions[] = "sp.thuong_hieu = ?";
            $params[] = $filters['thuong_hieu'];
        }
        
        $orderBy = "sp.ngay_tao DESC";
        if (!empty($filters['sap_xep'])) {
            switch($filters['sap_xep']) {
                case 'gia_thap':
                    $orderBy = "sp.gia ASC";
                    break;
                case 'gia_cao':
                    $orderBy = "sp.gia DESC";
                    break;
                case 'ban_chay':
                    $orderBy = "sp.luot_ban DESC";
                    break;
                case 'danh_gia':
                    $orderBy = "sp.diem_danh_gia DESC";
                    break;
            }
        }
        
        $query = "SELECT sp.*, dm.ten_danh_muc 
                  FROM san_pham sp 
                  LEFT JOIN danh_muc dm ON sp.danh_muc_id = dm.id 
                  WHERE " . implode(" AND ", $conditions) . "
                  ORDER BY {$orderBy}
                  LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->select($query, $params);
    }
    
    // Phương thức thống kê cho admin
    public function getStatistics() {
        $stats = [];
        
        // Tổng số sản phẩm
        $stats['tong_san_pham'] = $this->count('san_pham');
        
        // Tổng số đơn hàng
        $stats['tong_don_hang'] = $this->count('don_hang');
        
        // Tổng số người dùng
        $stats['tong_nguoi_dung'] = $this->count('nguoi_dung', "vai_tro = 'khach_hang'");
        
        // Doanh thu hôm nay
        $stats['doanh_thu_hom_nay'] = $this->selectOne(
            "SELECT COALESCE(SUM(tong_tien), 0) as total 
             FROM don_hang 
             WHERE DATE(ngay_dat_hang) = CURDATE() 
             AND trang_thai_thanh_toan = 'da_thanh_toan'"
        )['total'] ?? 0;
        
        // Doanh thu tháng này
        $stats['doanh_thu_thang'] = $this->selectOne(
            "SELECT COALESCE(SUM(tong_tien), 0) as total 
             FROM don_hang 
             WHERE MONTH(ngay_dat_hang) = MONTH(CURDATE()) 
             AND YEAR(ngay_dat_hang) = YEAR(CURDATE())
             AND trang_thai_thanh_toan = 'da_thanh_toan'"
        )['total'] ?? 0;
        
        return $stats;
    }
    
    // Đóng kết nối
    public function close() {
        $this->conn = null;
    }
}

// Khởi tạo đối tượng kết nối toàn cục
$db = new KetNoiDB();

// Hàm tiện ích để format giá tiền
function formatPrice($price) {
    return number_format($price, 0, '.', ',') . ' ₫';
}

// Hàm tiện ích để tính phần trăm giảm giá
function calculateDiscount($originalPrice, $currentPrice) {
    if ($originalPrice <= $currentPrice) return 0;
    return round((($originalPrice - $currentPrice) / $originalPrice) * 100);
}

// Hàm tiện ích để xử lý session
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Hàm kiểm tra đăng nhập
function isLoggedIn() {
    startSession();
    return isset($_SESSION['nguoi_dung_id']);
}

// Hàm kiểm tra quyền admin
function isAdmin() {
    startSession();
    return isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'admin';
}

// Hàm redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Hàm sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Hàm tạo CSRF token
function generateCSRFToken() {
    startSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Hàm xác minh CSRF token
function verifyCSRFToken($token) {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Set timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Error reporting (tắt trong production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>