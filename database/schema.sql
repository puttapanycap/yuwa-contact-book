-- Corporate Phonebook Database Schema (Simplified Version)
-- Created for PHP 8.0+ with MySQL

-- Create database
CREATE DATABASE IF NOT EXISTS corporate_phonebook CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE corporate_phonebook;

-- ปรับปรุงโครงสร้างตาราง employees ให้เหลือเฉพาะข้อมูลที่จำเป็น
-- เอาออก: name, position, email
-- เหลือไว้: department, internal_phone, building, floor, room_number

DROP TABLE IF EXISTS employees;

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(100) NOT NULL,
    internal_phone VARCHAR(20) NOT NULL UNIQUE,
    building VARCHAR(50) NOT NULL,
    floor INT NOT NULL,
    room_number VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_department (department),
    INDEX idx_building_floor (building, floor),
    INDEX idx_internal_phone (internal_phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data with simplified structure
INSERT INTO employees (department, internal_phone, building, floor, room_number) VALUES
('บริหาร', '1001', 'อาคาร A', 5, '501'),
('ทรัพยากรบุคคล', '1002', 'อาคาร A', 4, '401'),
('เทคโนโลยีสารสนเทศ', '2001', 'อาคาร B', 3, '301'),
('การเงินและบัญชี', '1003', 'อาคาร A', 2, '201'),
('การตลาดและขาย', '3001', 'อาคาร C', 1, '101'),
('บริหาร', '1004', 'อาคาร A', 5, '502'),
('เทคโนโลยีสารสนเทศ', '2002', 'อาคาร B', 3, '302'),
('การตลาดและขาย', '3002', 'อาคาร C', 2, '201'),
('การผลิต', '4001', 'อาคาร D', 1, '101'),
('เทคโนโลยีสารสนเทศ', '2003', 'อาคาร B', 4, '401'),
('ทรัพยากรบุคคล', '1005', 'อาคาร A', 4, '402'),
('การเงินและบัญชี', '1006', 'อาคาร A', 2, '202'),
('บริหาร', '1000', 'อาคาร A', 1, '100'),
('สนับสนุน', '5001', 'อาคาร E', 1, '101'),
('จัดซื้อจัดจ้าง', '6001', 'อาคาร A', 3, '301');

-- Keep admin users table unchanged
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
('admin', '$2y$10$8Dw90pYSnNrL2nEmS5xgaOwWhv/3nhFWQ3lbLxoXILfcqpCZKSwui', 'ผู้ดูแลระบบ', 'admin@company.com');

-- Keep activity log table unchanged
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
