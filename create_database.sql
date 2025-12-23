-- สร้างฐานข้อมูล running
CREATE DATABASE IF NOT EXISTS running CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE running;

-- ตาราง RUNNER (ข้อมูลนักวิ่ง)
CREATE TABLE IF NOT EXISTS RUNNER (
    runner_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    gender ENUM('Male', 'Female') NOT NULL,
    citizen_id VARCHAR(13) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100),
    address TEXT NOT NULL,
    is_disabled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตาราง RACE_CATEGORY (ประเภทการแข่งขัน)
CREATE TABLE IF NOT EXISTS RACE_CATEGORY (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    distance_km DECIMAL(5,2) NOT NULL,
    start_time TIME,
    time_limit TIME,
    giveaway_type VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตาราง AGE_GROUP (กลุ่มอายุ)
CREATE TABLE IF NOT EXISTS AGE_GROUP (
    age_group_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    gender ENUM('M', 'F', 'A') NOT NULL,
    min_age INT NOT NULL,
    max_age INT NOT NULL,
    label VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES RACE_CATEGORY(category_id) ON DELETE CASCADE
);

-- ตาราง PRICE_RATE (อัตราค่าสมัคร) - ยังคงไว้สำหรับระบบหลังบ้าน
CREATE TABLE IF NOT EXISTS PRICE_RATE (
    price_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    runner_type ENUM('Standard', 'Senior 70+', 'Disabled', 'Student') NOT NULL DEFAULT 'Standard',
    amount DECIMAL(8,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES RACE_CATEGORY(category_id) ON DELETE CASCADE
);

-- ตาราง SHIPPING_OPTION (ตัวเลือกการจัดส่ง)
CREATE TABLE IF NOT EXISTS SHIPPING_OPTION (
    shipping_id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    cost DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    detail TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตาราง REGISTRATION (การสมัครเข้าร่วม) - ใช้ Standard price เป็นค่าเริ่มต้น
CREATE TABLE IF NOT EXISTS REGISTRATION (
    reg_id INT AUTO_INCREMENT PRIMARY KEY,
    runner_id INT NOT NULL,
    category_id INT NOT NULL,
    price_id INT NOT NULL,
    shipping_id INT NOT NULL,
    reg_date DATE NOT NULL,
    shirt_size ENUM('S', 'M', 'L', 'XL', 'XXL') NOT NULL,
    status ENUM('Pending', 'Paid', 'Cancelled') DEFAULT 'Pending',
    bib_number VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (runner_id) REFERENCES RUNNER(runner_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES RACE_CATEGORY(category_id) ON DELETE CASCADE,
    FOREIGN KEY (price_id) REFERENCES PRICE_RATE(price_id) ON DELETE CASCADE,
    FOREIGN KEY (shipping_id) REFERENCES SHIPPING_OPTION(shipping_id) ON DELETE CASCADE
);

-- ตาราง PAYMENT (การชำระเงิน)
CREATE TABLE IF NOT EXISTS PAYMENT (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    reg_id INT NOT NULL,
    total_amount DECIMAL(8,2) NOT NULL,
    payment_time DATETIME NOT NULL,
    payment_method ENUM('Bank Transfer', 'QR Code', 'Cash', 'Credit Card') NOT NULL,
    status ENUM('Pending', 'Success', 'Failed') DEFAULT 'Pending',
    transaction_ref VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reg_id) REFERENCES REGISTRATION(reg_id) ON DELETE CASCADE
);

-- เพิ่มข้อมูลตัวอย่าง
INSERT INTO RACE_CATEGORY (name, distance_km, start_time, time_limit, giveaway_type) VALUES
('Mini Marathon', 5.0, '06:00:00', '01:30:00', 'เสื้อ + เหรียญ'),
('Half Marathon', 21.1, '05:30:00', '03:00:00', 'เสื้อ + เหรียญ + ถ้วย'),
('Full Marathon', 42.2, '05:00:00', '06:00:00', 'เสื้อ + เหรียญ + ถ้วย + ใบประกาศ');

INSERT INTO SHIPPING_OPTION (type, cost, detail) VALUES
('Pickup', 0.00, 'รับด้วยตัวเองที่งาน วันที่ 15-16 มีนาคม 2026'),
('EMS', 50.00, 'จัดส่งทางไปรษณีย์ EMS ภายใน 7-10 วันทำการ'),
('Kerry', 45.00, 'จัดส่งผ่าน Kerry Express ภายใน 3-5 วันทำการ');

-- เพิ่มราคา Standard เป็นค่าเริ่มต้นสำหรับทุกประเภท (ระบบจะใช้ราคานี้อัตโนมัติ)
INSERT INTO PRICE_RATE (category_id, runner_type, amount) VALUES
(1, 'Standard', 500.00),
(1, 'Senior 70+', 400.00),
(1, 'Disabled', 300.00),
(1, 'Student', 350.00),
(2, 'Standard', 800.00),
(2, 'Senior 70+', 650.00),
(2, 'Disabled', 500.00),
(2, 'Student', 600.00),
(3, 'Standard', 1200.00),
(3, 'Senior 70+', 1000.00),
(3, 'Disabled', 800.00),
(3, 'Student', 900.00);

INSERT INTO AGE_GROUP (category_id, gender, min_age, max_age, label) VALUES
(1, 'M', 18, 29, 'ชาย 18-29 ปี'),
(1, 'M', 30, 39, 'ชาย 30-39 ปี'),
(1, 'M', 40, 49, 'ชาย 40-49 ปี'),
(1, 'M', 50, 59, 'ชาย 50-59 ปี'),
(1, 'M', 60, 100, 'ชาย 60+ ปี'),
(1, 'F', 18, 29, 'หญิง 18-29 ปี'),
(1, 'F', 30, 39, 'หญิง 30-39 ปี'),
(1, 'F', 40, 49, 'หญิง 40-49 ปี'),
(1, 'F', 50, 59, 'หญิง 50-59 ปี'),
(1, 'F', 60, 100, 'หญิง 60+ ปี');