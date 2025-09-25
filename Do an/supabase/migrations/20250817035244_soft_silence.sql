-- Database cho Phát Technology Spirit Shop
DROP DATABASE IF EXISTS phat_technology_spirit;
CREATE DATABASE phat_technology_spirit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE phat_technology_spirit;

-- Bảng người dùng
CREATE TABLE nguoi_dung (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ten_dang_nhap VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mat_khau VARCHAR(255) NOT NULL,
    ho_ten VARCHAR(100) NOT NULL,
    so_dien_thoai VARCHAR(15),
    dia_chi TEXT,
    vai_tro ENUM('khach_hang', 'admin') DEFAULT 'khach_hang',
    trang_thai ENUM('hoat_dong', 'tam_khoa') DEFAULT 'hoat_dong',
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lan_dang_nhap_cuoi TIMESTAMP NULL
);

-- Bảng danh mục sản phẩm
CREATE TABLE danh_muc (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ten_danh_muc VARCHAR(100) NOT NULL,
    mo_ta TEXT,
    hinh_anh VARCHAR(255),
    thu_tu INT DEFAULT 0,
    trang_thai ENUM('hien', 'an') DEFAULT 'hien'
);

-- Bảng sản phẩm
CREATE TABLE san_pham (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ten_san_pham VARCHAR(200) NOT NULL,
    mo_ta_ngan TEXT,
    mo_ta_chi_tiet LONGTEXT,
    gia DECIMAL(15,2) NOT NULL,
    gia_goc DECIMAL(15,2),
    so_luong_ton INT DEFAULT 0,
    hinh_anh_chinh VARCHAR(255),
    hinh_anh_phu JSON,
    danh_muc_id INT,
    luot_xem INT DEFAULT 0,
    luot_ban INT DEFAULT 0,
    diem_danh_gia DECIMAL(3,2) DEFAULT 0,
    so_danh_gia INT DEFAULT 0,
    trang_thai ENUM('con_hang', 'het_hang', 'ngung_ban') DEFAULT 'con_hang',
    noi_bat BOOLEAN DEFAULT FALSE,
    giam_gia_percent INT DEFAULT 0,
    thuong_hieu VARCHAR(100),
    xuat_xu VARCHAR(100),
    bao_hanh VARCHAR(100),
    thong_so_ky_thuat JSON,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (danh_muc_id) REFERENCES danh_muc(id) ON DELETE SET NULL,
    INDEX idx_danh_muc (danh_muc_id),
    INDEX idx_gia (gia),
    INDEX idx_noi_bat (noi_bat),
    FULLTEXT KEY ft_tim_kiem (ten_san_pham, mo_ta_ngan)
);

-- Bảng giỏ hàng
CREATE TABLE gio_hang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nguoi_dung_id INT NOT NULL,
    san_pham_id INT NOT NULL,
    so_luong INT NOT NULL DEFAULT 1,
    gia DECIMAL(15,2) NOT NULL,
    ngay_them TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (san_pham_id) REFERENCES san_pham(id) ON DELETE CASCADE,
    UNIQUE KEY uk_nguoi_dung_san_pham (nguoi_dung_id, san_pham_id)
);

-- Bảng đơn hàng
CREATE TABLE don_hang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ma_don_hang VARCHAR(20) UNIQUE NOT NULL,
    nguoi_dung_id INT NOT NULL,
    tong_tien DECIMAL(15,2) NOT NULL,
    phi_van_chuyen DECIMAL(10,2) DEFAULT 0,
    giam_gia DECIMAL(10,2) DEFAULT 0,
    trang_thai_don_hang ENUM('cho_xac_nhan', 'da_xac_nhan', 'dang_chuan_bi', 'dang_giao', 'da_giao', 'da_huy') DEFAULT 'cho_xac_nhan',
    phuong_thuc_thanh_toan ENUM('tien_mat', 'chuyen_khoan', 'the_tin_dung', 'vi_dien_tu') DEFAULT 'tien_mat',
    trang_thai_thanh_toan ENUM('chua_thanh_toan', 'da_thanh_toan', 'hoan_tien') DEFAULT 'chua_thanh_toan',
    dia_chi_giao_hang TEXT NOT NULL,
    so_dien_thoai_nhan VARCHAR(15) NOT NULL,
    ten_nguoi_nhan VARCHAR(100) NOT NULL,
    ghi_chu TEXT,
    ngay_dat_hang TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id),
    INDEX idx_ma_don_hang (ma_don_hang),
    INDEX idx_trang_thai (trang_thai_don_hang)
);

-- Bảng chi tiết đơn hàng
CREATE TABLE chi_tiet_don_hang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    don_hang_id INT NOT NULL,
    san_pham_id INT NOT NULL,
    ten_san_pham VARCHAR(200) NOT NULL,
    gia DECIMAL(15,2) NOT NULL,
    so_luong INT NOT NULL,
    thanh_tien DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (don_hang_id) REFERENCES don_hang(id) ON DELETE CASCADE,
    FOREIGN KEY (san_pham_id) REFERENCES san_pham(id)
);

-- Bảng đánh giá sản phẩm
CREATE TABLE danh_gia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    san_pham_id INT NOT NULL,
    nguoi_dung_id INT NOT NULL,
    diem_danh_gia INT NOT NULL CHECK (diem_danh_gia BETWEEN 1 AND 5),
    binh_luan TEXT,
    hinh_anh JSON,
    trang_thai ENUM('hien', 'an', 'cho_duyet') DEFAULT 'cho_duyet',
    ngay_danh_gia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (san_pham_id) REFERENCES san_pham(id) ON DELETE CASCADE,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    UNIQUE KEY uk_nguoi_dung_san_pham_danh_gia (nguoi_dung_id, san_pham_id)
);

-- Bảng tin nhắn chatbot
CREATE TABLE tin_nhan_chatbot (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nguoi_dung_id INT,
    session_id VARCHAR(100) NOT NULL,
    tin_nhan TEXT NOT NULL,
    phan_hoi TEXT,
    loai_tin_nhan ENUM('cau_hoi', 'goi_y_san_pham', 'ho_tro', 'khac') DEFAULT 'cau_hoi',
    ngay_gui TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE SET NULL,
    INDEX idx_session (session_id)
);

-- Bảng banner quảng cáo
CREATE TABLE banner (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tieu_de VARCHAR(200) NOT NULL,
    mo_ta TEXT,
    hinh_anh VARCHAR(255) NOT NULL,
    link_dich VARCHAR(255),
    thu_tu INT DEFAULT 0,
    trang_thai ENUM('hien', 'an') DEFAULT 'hien',
    ngay_bat_dau DATE,
    ngay_ket_thuc DATE,
    ngay_tao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng thống kê truy cập
CREATE TABLE thong_ke_truy_cap (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    trang_truy_cap VARCHAR(255),
    nguoi_dung_id INT,
    thoi_gian_truy_cap TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE SET NULL,
    INDEX idx_ngay (thoi_gian_truy_cap),
    INDEX idx_ip (ip_address)
);

-- Thêm dữ liệu mẫu

-- Thêm admin mặc định
INSERT INTO nguoi_dung (ten_dang_nhap, email, mat_khau, ho_ten, vai_tro) 
VALUES ('admin', 'admin@phat-tech.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản trị viên', 'admin');

-- Thêm danh mục
INSERT INTO danh_muc (ten_danh_muc, mo_ta, hinh_anh) VALUES
('Điện Thoại Thông Minh', 'Các dòng điện thoại thông minh cao cấp', 'https://images.pexels.com/photos/788946/pexels-photo-788946.jpeg'),
('Laptop & Máy Tính', 'Laptop, PC và phụ kiện máy tính', 'https://images.pexels.com/photos/2047905/pexels-photo-2047905.jpeg'),
('Thiết Bị Đeo Thông Minh', 'Smartwatch, tai nghe, fitness tracker', 'https://images.pexels.com/photos/393047/pexels-photo-393047.jpeg'),
('Nhà Thông Minh', 'Thiết bị IoT cho ngôi nhà thông minh', 'https://images.pexels.com/photos/6394519/pexels-photo-6394519.jpeg'),
('Gaming & Giải Trí', 'Thiết bị gaming và giải trí số', 'https://images.pexels.com/photos/442576/pexels-photo-442576.jpeg');

-- Thêm 20 sản phẩm mẫu
INSERT INTO san_pham (ten_san_pham, mo_ta_ngan, mo_ta_chi_tiet, gia, gia_goc, so_luong_ton, hinh_anh_chinh, danh_muc_id, noi_bat, giam_gia_percent, thuong_hieu, xuat_xu, bao_hanh, thong_so_ky_thuat) VALUES

('iPhone 15 Pro Max 256GB', 'Điện thoại thông minh cao cấp với chip A17 Pro', 'iPhone 15 Pro Max mang đến trải nghiệm smartphone đỉnh cao với chip A17 Pro tiên tiến, camera 48MP chuyên nghiệp, và màn hình Super Retina XDR 6.7 inch. Thiết kế titan chuẩn hàng không vũ trụ, bền bỉ và nhẹ hơn bao giờ hết.', 28990000, 32990000, 50, 'https://images.pexels.com/photos/788946/pexels-photo-788946.jpeg', 1, 1, 12, 'Apple', 'Mỹ', '12 tháng', '{"man_hinh": "6.7 inch Super Retina XDR", "chip": "A17 Pro", "ram": "8GB", "bo_nho": "256GB", "camera": "48MP"}'),

('Samsung Galaxy S24 Ultra 512GB', 'Flagship Android với S Pen và AI tiên tiến', 'Galaxy S24 Ultra định nghĩa lại smartphone cao cấp với bộ xử lý Snapdragon 8 Gen 3, camera zoom 200MP đột phá, S Pen tích hợp và các tính năng AI Galaxy thông minh. Màn hình Dynamic AMOLED 2X 6.8 inch siêu sáng.', 26990000, 29990000, 35, 'https://images.pexels.com/photos/404280/pexels-photo-404280.jpeg', 1, 1, 10, 'Samsung', 'Hàn Quốc', '12 tháng', '{"man_hinh": "6.8 inch Dynamic AMOLED 2X", "chip": "Snapdragon 8 Gen 3", "ram": "12GB", "bo_nho": "512GB", "camera": "200MP"}'),

('MacBook Pro M3 16 inch 512GB', 'Laptop chuyên nghiệp với chip M3 mạnh mẽ', 'MacBook Pro 16 inch với chip M3 mang lại hiệu năng đỉnh cao cho công việc chuyên nghiệp. Màn hình Liquid Retina XDR 16.2 inch, hệ thống âm thanh 6 loa và thời lượng pin lên đến 22 giờ.', 59990000, 65990000, 20, 'https://images.pexels.com/photos/2047905/pexels-photo-2047905.jpeg', 2, 1, 9, 'Apple', 'Mỹ', '12 tháng', '{"man_hinh": "16.2 inch Liquid Retina XDR", "chip": "Apple M3", "ram": "16GB", "ssd": "512GB", "gpu": "M3 GPU"}'),

('Dell XPS 13 Plus i7 32GB RAM', 'Ultrabook cao cấp thiết kế tối giản', 'Dell XPS 13 Plus với thiết kế Edge-to-Edge, bàn phím cảm ứng Zero Lattice và trackpad vô hình. Trang bị Intel Core i7 thế hệ 13, RAM 32GB LPDDR5 và SSD 1TB PCIe 4.0 cho hiệu năng vượt trội.', 45990000, 52990000, 15, 'https://images.pexels.com/photos/18105/pexels-photo-18105.jpeg', 2, 1, 13, 'Dell', 'Mỹ', '24 tháng', '{"man_hinh": "13.4 inch 4K OLED", "cpu": "Intel Core i7-1360P", "ram": "32GB LPDDR5", "ssd": "1TB PCIe 4.0"}'),

('Apple Watch Series 9 GPS 45mm', 'Smartwatch với chip S9 và màn hình sáng hơn', 'Apple Watch Series 9 với chip S9 SiP mạnh mẽ, màn hình sáng gấp đôi, và tính năng Double Tap hoàn toàn mới. Theo dõi sức khỏe toàn diện với cảm biến nhịp tim, SpO2 và ECG.', 10990000, 12990000, 80, 'https://images.pexels.com/photos/393047/pexels-photo-393047.jpeg', 3, 1, 15, 'Apple', 'Mỹ', '12 tháng', '{"man_hinh": "45mm Always-On Retina", "chip": "S9 SiP", "thoi_luong_pin": "18 giờ", "chong_nuoc": "50 mét"}'),

('AirPods Pro (3rd Gen) USB-C', 'Tai nghe true wireless với chống ồn chủ động', 'AirPods Pro thế hệ 3 với driver tùy chỉnh của Apple, chip H2 tiên tiến và Adaptive Transparency. Chống ồn chủ động lên đến 2x tốt hơn thế hệ trước, âm thanh Spatial Audio sống động.', 5990000, 6990000, 120, 'https://images.pexels.com/photos/8534088/pexels-photo-8534088.jpeg', 3, 1, 14, 'Apple', 'Mỹ', '12 tháng', '{"driver": "Tùy chỉnh của Apple", "chip": "H2", "chong_on": "Adaptive", "pin": "6h + 24h với case"}'),

('Samsung Galaxy Buds2 Pro', 'Tai nghe không dây với âm thanh Hi-Fi 24bit', 'Galaxy Buds2 Pro mang đến chất lượng âm thanh Hi-Fi 24bit với codec Samsung Seamless và 360 Audio. Chống ồn thông minh ANC, thiết kế ergonomic thoải mái suốt ngày dài.', 3990000, 4990000, 90, 'https://images.pexels.com/photos/8534088/pexels-photo-8534088.jpeg', 3, 0, 20, 'Samsung', 'Hàn Quốc', '12 tháng', '{"am_thanh": "Hi-Fi 24bit", "codec": "Samsung Seamless", "anc": "Thông minh", "pin": "5h + 18h"}'),

('Google Nest Hub Max', 'Màn hình thông minh 10 inch với Google Assistant', 'Nest Hub Max với màn hình HD 10 inch, camera tích hợp và Google Assistant thông minh. Điều khiển nhà thông minh, video call, xem YouTube và nghe nhạc với chất lượng âm thanh premium.', 6990000, 7990000, 40, 'https://images.pexels.com/photos/6394519/pexels-photo-6394519.jpeg', 4, 1, 12, 'Google', 'Mỹ', '12 tháng', '{"man_hinh": "10 inch HD", "camera": "6.5MP", "loa": "Stereo", "ket_noi": "WiFi 802.11ac"}'),

('Amazon Echo Studio', 'Loa thông minh với âm thanh Dolby Atmos', 'Echo Studio mang đến trải nghiệm âm thanh vòm 360 độ với công nghệ Dolby Atmos. 5 driver chuyên nghiệp, tự động điều chỉnh âm thanh theo không gian phòng và tích hợp Alexa thông minh.', 5990000, 6990000, 60, 'https://images.pexels.com/photos/6394519/pexels-photo-6394519.jpeg', 4, 0, 14, 'Amazon', 'Mỹ', '12 tháng', '{"driver": "5 driver", "cong_nghe": "Dolby Atmos", "ket_noi": "WiFi, Bluetooth", "voice": "Alexa"}'),

('PlayStation 5 Pro 2TB', 'Console game thế hệ mới với SSD siêu tốc', 'PS5 Pro với SSD NVMe siêu tốc 2TB, GPU custom RDNA 2, và công nghệ Ray Tracing tiên tiến. Hỗ trợ độ phân giải 4K@120fps, 3D Audio Tempest và controller DualSense với haptic feedback.', 18990000, 21990000, 25, 'https://images.pexels.com/photos/442576/pexels-photo-442576.jpeg', 5, 1, 13, 'Sony', 'Nhật Bản', '12 tháng', '{"gpu": "Custom RDNA 2", "ssd": "2TB NVMe", "ray_tracing": "Có", "do_phan_giai": "4K@120fps"}'),

('Xbox Series X 1TB', 'Console Microsoft với hiệu năng 12 TFLOPS', 'Xbox Series X mang lại hiệu năng gaming đỉnh cao với GPU 12 TFLOPS, SSD NVMe 1TB và Quick Resume. Hỗ trợ 4K gaming, ray tracing và Xbox Game Pass với hàng trăm game AAA.', 14990000, 16990000, 30, 'https://images.pexels.com/photos/442576/pexels-photo-442576.jpeg', 5, 1, 11, 'Microsoft', 'Mỹ', '12 tháng', '{"gpu": "12 TFLOPS", "cpu": "AMD Zen 2", "ssd": "1TB NVMe", "game_pass": "Có"}'),

('ASUS ROG Strix RTX 4080 16GB', 'Card đồ họa gaming cao cấp RTX 4080', 'RTX 4080 ROG Strix với 16GB GDDR6X, công nghệ DLSS 3.0 và Ray Tracing thế hệ 3. Hệ thống tản nhiệt Axial-tech 3 quạt, OC mode và RGB Aura Sync đẹp mắt.', 28990000, 32990000, 12, 'https://images.pexels.com/photos/2582937/pexels-photo-2582937.jpeg', 2, 1, 12, 'ASUS', 'Đài Loan', '24 tháng', '{"gpu": "RTX 4080", "vram": "16GB GDDR6X", "dlss": "3.0", "tan_nhiet": "Axial-tech 3x"}'),

('iPad Air M2 256GB WiFi', 'Máy tính bảng với chip M2 mạnh mẽ', 'iPad Air M2 với màn hình Liquid Retina 10.9 inch, chip M2 8 nhân và camera 12MP. Hỗ trợ Apple Pencil thế hệ 2 và Magic Keyboard, hoàn hảo cho công việc và sáng tạo.', 16990000, 18990000, 45, 'https://images.pexels.com/photos/1334597/pexels-photo-1334597.jpeg', 1, 0, 10, 'Apple', 'Mỹ', '12 tháng', '{"man_hinh": "10.9 inch Liquid Retina", "chip": "Apple M2", "bo_nho": "256GB", "camera": "12MP"}'),

('Surface Pro 9 i7 16GB 512GB', '2-trong-1 laptop tablet cao cấp', 'Surface Pro 9 với Intel Core i7 thế hệ 12, màn hình PixelSense Flow 13 inch 120Hz. Thiết kế 2-trong-1 linh hoạt với Surface Pen và Type Cover, pin lên đến 15.5 giờ.', 35990000, 39990000, 20, 'https://images.pexels.com/photos/1334597/pexels-photo-1334597.jpeg', 2, 0, 10, 'Microsoft', 'Mỹ', '12 tháng', '{"cpu": "Intel Core i7-1255U", "ram": "16GB LPDDR5", "ssd": "512GB", "man_hinh": "13 inch 120Hz"}'),

('Xiaomi 13 Ultra 512GB', 'Camera phone chuyên nghiệp với Leica', 'Xiaomi 13 Ultra trang bị hệ thống camera Leica 4 ống kính 50MP, chip Snapdragon 8 Gen 2 và màn hình AMOLED 6.73 inch 120Hz. Sạc nhanh 90W và sạc không dây 50W.', 22990000, 25990000, 30, 'https://images.pexels.com/photos/404280/pexels-photo-404280.jpeg', 1, 0, 11, 'Xiaomi', 'Trung Quốc', '18 tháng', '{"camera": "Leica 4x50MP", "chip": "Snapdragon 8 Gen 2", "man_hinh": "6.73 inch AMOLED 120Hz", "sac": "90W"}'),

('Garmin Fenix 7X Solar', 'Smartwatch thể thao với pin năng lượng mặt trời', 'Fenix 7X Solar với pin Power Glass sạc năng lượng mặt trời, GPS đa vệ tinh chính xác và hơn 40 ứng dụng thể thao. Màn hình 1.4 inch, chống nước 100m và bản đồ TopoActive.', 18990000, 21990000, 25, 'https://images.pexels.com/photos/393047/pexels-photo-393047.jpeg', 3, 0, 14, 'Garmin', 'Mỹ', '12 tháng', '{"man_hinh": "1.4 inch", "pin": "Solar charging", "gps": "Đa vệ tinh", "chong_nuoc": "10ATM"}'),

('Bose QuietComfort 45', 'Tai nghe chống ồn cao cấp', 'QC45 với công nghệ chống ồn hàng đầu thế giới, âm thanh TriPort và EQ có thể điều chỉnh. Pin 24 giờ, sạc nhanh 15 phút cho 3 giờ nghe nhạc và kết nối Bluetooth multipoint.', 7990000, 8990000, 50, 'https://images.pexels.com/photos/8534088/pexels-photo-8534088.jpeg', 3, 0, 11, 'Bose', 'Mỹ', '12 tháng', '{"chong_on": "World-class", "pin": "24 giờ", "bluetooth": "Multipoint", "sac_nhanh": "15 phút = 3 giờ"}'),

('LG OLED C3 65 inch 4K', 'Smart TV OLED với α9 Gen6 AI Processor', 'LG C3 OLED với độ tương phản vô cực, màu sắc hoàn hảo 100% và bộ xử lý α9 Gen6 AI. Hỗ trợ Dolby Vision IQ, Dolby Atmos và các tính năng gaming HDMI 2.1 4K@120Hz.', 35990000, 39990000, 18, 'https://images.pexels.com/photos/6394519/pexels-photo-6394519.jpeg', 4, 1, 10, 'LG', 'Hàn Quốc', '24 tháng', '{"man_hinh": "65 inch OLED 4K", "processor": "α9 Gen6 AI", "hdr": "Dolby Vision IQ", "gaming": "4K@120Hz VRR"}'),

('Philips Hue Starter Kit', 'Hệ thống đèn thông minh RGB', 'Bộ kit khởi động Philips Hue với 3 bóng đèn A60 E27, cầu Hue Bridge và ứng dụng điều khiển. 16 triệu màu sắc, điều chỉnh độ sáng và đồng bộ với nhạc, phim ảnh.', 4990000, 5990000, 70, 'https://images.pexels.com/photos/6394519/pexels-photo-6394519.jpeg', 4, 0, 16, 'Philips', 'Hà Lan', '24 tháng', '{"mau_sac": "16 triệu màu", "den": "3x A60 E27", "ket_noi": "Zigbee 3.0", "app": "Hue App"}'),

('Tesla Model Y Accessories Kit', 'Phụ kiện cao cấp cho Tesla Model Y', 'Bộ phụ kiện hoàn chỉnh cho Tesla Model Y bao gồm thảm sàn, ốp nội thất carbon, bọc ghế da và các phụ kiện thông minh. Chất liệu premium, lắp đặt dễ dàng không cần thay đổi nguyên bản.', 12990000, 14990000, 15, 'https://images.pexels.com/photos/6394519/pexels-photo-6394519.jpeg', 4, 0, 13, 'Tesla', 'Mỹ', '12 tháng', '{"chat_lieu": "Carbon fiber + Da thật", "bao_gom": "Thảm sàn, ốp nội thất", "lap_dat": "Không phá nguyên bản"}');

-- Thêm banner mẫu
INSERT INTO banner (tieu_de, mo_ta, hinh_anh, link_dich, thu_tu) VALUES
('iPhone 15 Pro Max - Giảm ngay 12%', 'Siêu phẩm công nghệ với nhiều ưu đãi hấp dẫn', 'https://images.pexels.com/photos/788946/pexels-photo-788946.jpeg', 'san_pham.php?id=1', 1),
('MacBook Pro M3 - Mạnh mẽ vượt trội', 'Laptop chuyên nghiệp cho mọi nhu cầu làm việc', 'https://images.pexels.com/photos/2047905/pexels-photo-2047905.jpeg', 'san_pham.php?id=3', 2),
('PlayStation 5 Pro - Gaming đỉnh cao', 'Trải nghiệm game thế hệ mới với công nghệ tiên tiến', 'https://images.pexels.com/photos/442576/pexels-photo-442576.jpeg', 'san_pham.php?id=10', 3);

-- Cập nhật điểm đánh giá cho một số sản phẩm
UPDATE san_pham SET diem_danh_gia = 4.8, so_danh_gia = 156 WHERE id = 1;
UPDATE san_pham SET diem_danh_gia = 4.7, so_danh_gia = 128 WHERE id = 2;
UPDATE san_pham SET diem_danh_gia = 4.9, so_danh_gia = 203 WHERE id = 3;
UPDATE san_pham SET diem_danh_gia = 4.6, so_danh_gia = 89 WHERE id = 5;
UPDATE san_pham SET diem_danh_gia = 4.5, so_danh_gia = 95 WHERE id = 10;