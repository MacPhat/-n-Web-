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
    case 'cancel':
        $order_id = (int)$_POST['order_id'];
        
        // Kiểm tra đơn hàng thuộc về người dùng và có thể hủy
        $donHang = $db->selectOne(
            "SELECT * FROM don_hang WHERE id = ? AND nguoi_dung_id = ? AND trang_thai_don_hang = 'cho_xac_nhan'", 
            [$order_id, $nguoi_dung_id]
        );
        
        if (!$donHang) {
            echo json_encode(['success' => false, 'message' => 'Không thể hủy đơn hàng này!']);
            exit;
        }
        
        // Cập nhật trạng thái đơn hàng
        $updated = $db->execute(
            "UPDATE don_hang SET trang_thai_don_hang = 'da_huy' WHERE id = ?", 
            [$order_id]
        );
        
        if ($updated) {
            // Hoàn lại số lượng tồn kho
            $chiTietDonHang = $db->select(
                "SELECT * FROM chi_tiet_don_hang WHERE don_hang_id = ?", 
                [$order_id]
            );
            
            foreach ($chiTietDonHang as $item) {
                $db->execute(
                    "UPDATE san_pham SET so_luong_ton = so_luong_ton + ?, luot_ban = luot_ban - ? WHERE id = ?",
                    [$item['so_luong'], $item['so_luong'], $item['san_pham_id']]
                );
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi hủy đơn hàng!']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ!']);
}
?>