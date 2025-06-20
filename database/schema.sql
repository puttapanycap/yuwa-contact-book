-- Corporate Phonebook Database Schema
-- Created for PHP 8.0+ with MySQL

-- Create database
CREATE DATABASE IF NOT EXISTS corporate_phonebook CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE corporate_phonebook;

-- แก้ไขโครงสร้างตาราง employees ให้ใช้เฉพาะข้อมูลที่จำเป็น
-- ลบ first_name, last_name, mobile_phone ออก และใช้ name แทน

DROP TABLE IF EXISTS employees;

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    position VARCHAR(150) NOT NULL,
    department VARCHAR(100) NOT NULL,
    internal_phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) NULL,
    building VARCHAR(50) NOT NULL,
    floor INT NOT NULL,
    room_number VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_department (department),
    INDEX idx_building_floor (building, floor),
    INDEX idx_internal_phone (internal_phone),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data with simplified structure
INSERT INTO employees (name, position, department, internal_phone, email, building, floor, room_number) VALUES
('สมชาย ใจดี', 'ผู้จัดการทั่วไป', 'บริหาร', '1001', 'somchai@company.com', 'อาคาร A', 5, '501'),
('สมหญิง รักงาน', 'หัวหน้าฝ่ายบุคคล', 'ทรัพยากรบุคคล', '1002', 'somying@company.com', 'อาคาร A', 4, '401'),
('วิชัย เก่งมาก', 'นักพัฒนาระบบ', 'เทคโนโลยีสารสนเทศ', '2001', 'wichai@company.com', 'อาคาร B', 3, '301'),
('นิดา ขยันดี', 'นักบัญชี', 'การเงินและบัญชี', '1003', 'nida@company.com', 'อาคาร A', 2, '201'),
('ประยุทธ มั่นคง', 'หัวหน้าฝ่ายขาย', 'การตลาดและขาย', '3001', 'prayuth@company.com', 'อาคาร C', 1, '101'),
('สุดา ใส่ใจ', 'เลขานุการ', 'บริหาร', '1004', 'suda@company.com', 'อาคาร A', 5, '502'),
('อนุชา ทำดี', 'วิศวกรระบบ', 'เทคโนโลยีสารสนเทศ', '2002', 'anucha@company.com', 'อาคาร B', 3, '302'),
('มาลี สวยงาม', 'นักการตลาด', 'การตลาดและขาย', '3002', 'malee@company.com', 'อาคาร C', 2, '201'),
('สมศักดิ์ แข็งแรง', 'หัวหน้าฝ่ายผลิต', 'การผลิต', '4001', 'somsak@company.com', 'อาคาร D', 1, '101'),
('ปิยะ ฉลาด', 'นักวิเคราะห์ระบบ', 'เทคโนโลยีสารสนเทศ', '2003', 'piya@company.com', 'อาคาร B', 4, '401'),
('รัชนี เรียบร้อย', 'เจ้าหน้าที่บุคคล', 'ทรัพยากรบุคคล', '1005', 'ratchanee@company.com', 'อาคาร A', 4, '402'),
('ธนา รวยมาก', 'นักวิเคราะห์การเงิน', 'การเงินและบัญชี', '1006', 'thana@company.com', 'อาคาร A', 2, '202'),
('จิรา ยิ้มแย้ม', 'พนักงานต้อนรับ', 'บริหาร', '1000', 'jira@company.com', 'อาคาร A', 1, '100'),
('บุญชู ช่วยเหลือ', 'ช่างซ่อมบำรุง', 'สนับสนุน', '5001', 'boonchoo@company.com', 'อาคาร E', 1, '101'),
('สิริ เป็นระเบียบ', 'เจ้าหน้าที่จัดซื้อ', 'จัดซื้อจัดจ้าง', '6001', 'siri@company.com', 'อาคาร A', 3, '301');

-- Create admin users table (optional for future authentication)
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password, full_name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ผู้ดูแลระบบ', 'admin@company.com');

-- เปลี่ยนเป็น password hash ที่ถูกต้องสำหรับ 'admin123'
UPDATE admin_users SET password = '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36WQoeG6Lruj3vjPGga31lW' WHERE username = 'admin';

-- Create activity log table (optional for tracking changes)
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
