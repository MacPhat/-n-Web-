<?php
require_once 'ket_noi.php';
startSession();

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    exit;
}

$action = $_POST['action'] ?? '';
$nguoi_dung_id = $_SESSION['nguoi_dung_id'];

switch ($action) {
    case 'add':
        $san_pham_id = (int)$_POST['product_id'];
        $so_luong = (int)($_POST['quantity'] ?? 1);
        
        // Check if product exists and has stock
        $sanPham = $db->selectOne(
            "SELECT * FROM san_pham WHERE id = ? AND trang_thai = 'con_hang'", 
            [$san_pham_id]
        );
        
        if (!$sanPham) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại!']);
            exit;
        }
        
        if ($sanPham['so_luong_ton'] < $so_luong) {
            echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho!']);
            exit;
        }
        
        // Check if product already in cart
        $cartItem = $db->selectOne(
            "SELECT * FROM gio_hang WHERE nguoi_dung_id = ? AND san_pham_id = ?", 
            [$nguoi_dung_id, $san_pham_id]
        );
        
        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem['so_luong'] + $so_luong;
            if ($newQuantity > $sanPham['so_luong_ton']) {
                echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho!']);
                exit;
            }
            
            $db->execute(
                "UPDATE gio_hang SET so_luong = ? WHERE nguoi_dung_id = ? AND san_pham_id = ?",
                [$newQuantity, $nguoi_dung_id, $san_pham_id]
            );
        } else {
            // Add new item to cart
            $db->insert(
                "INSERT INTO gio_hang (nguoi_dung_id, san_pham_id, so_luong, gia) VALUES (?, ?, ?, ?)",
                [$nguoi_dung_id, $san_pham_id, $so_luong, $sanPham['gia']]
            );
        }
        
        // Get cart count
        $cartCount = $db->selectOne("SELECT COUNT(*) as count FROM gio_hang WHERE nguoi_dung_id = ?", [$nguoi_dung_id]);
        
        echo json_encode(['success' => true, 'cartCount' => $cartCount['count']]);
        break;
        
    case 'update':
        $san_pham_id = (int)$_POST['product_id'];
        $so_luong = (int)$_POST['quantity'];
        
        if ($so_luong <= 0) {
            $db->execute(
                "DELETE FROM gio_hang WHERE nguoi_dung_id = ? AND san_pham_id = ?",
                [$nguoi_dung_id, $san_pham_id]
            );
        } else {
            // Check stock
            $sanPham = $db->selectOne("SELECT so_luong_ton FROM san_pham WHERE id = ?", [$san_pham_id]);
            if ($so_luong > $sanPham['so_luong_ton']) {
                echo json_encode(['success' => false, 'message' => 'Không đủ hàng trong kho!']);
                exit;
            }
            
            $db->execute(
                "UPDATE gio_hang SET so_luong = ? WHERE nguoi_dung_id = ? AND san_pham_id = ?",
                [$so_luong, $nguoi_dung_id, $san_pham_id]
            );
        }
        
        echo json_encode(['success' => true]);
        break;
        
    case 'remove':
        $san_pham_id = (int)$_POST['product_id'];
        
        $db->execute(
            "DELETE FROM gio_hang WHERE nguoi_dung_id = ? AND san_pham_id = ?",
            [$nguoi_dung_id, $san_pham_id]
        );
        
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ!']);
}
?>