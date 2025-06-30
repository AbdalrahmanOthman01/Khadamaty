-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS event_services CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE event_services;

-- حذف الجداول لتجنب التعارض
DROP TABLE IF EXISTS status, logs, orders, users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    birth_date DATE DEFAULT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    gender ENUM('ذكر', 'أنثى') DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    role ENUM('admin', 'admin_view', 'user') DEFAULT 'user',
    college ENUM('الطب', 'الهندسة', 'العلوم', 'التجارة', 'الزراعة', 'الصيدلة', 'الحقوق', 'التربية', 'الذكاء الاصطناعي', 'الآداب', 'الاقتصاد المنزلي', 'التمريض', 'الحاسبات والمعلومات', 'الطب البيطري', 'التربية الرياضية', 'التربية النوعية') DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- جدول الطلبات (بدون عمود id)
CREATE TABLE orders (
    order_id VARCHAR(50) PRIMARY KEY,
    user_id INT NOT NULL,
    event_type VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL,
    event_date TEXT,
    start_session TIME NOT NULL,
    end_session TIME NOT NULL,
    meals TEXT,
    meals_q INT DEFAULT 0,
    drinks TEXT,
    drinks_q INT DEFAULT 0,
    deserts TEXT,
    deserts_q INT DEFAULT 0,
    clothes TEXT,
    clothes_q INT DEFAULT 0,
    accessory TEXT,
    accessory_q INT DEFAULT 0,
    media TEXT,
    media_q INT DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- جدول الحالة (Status)
CREATE TABLE status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL,
    order_status ENUM('approved', 'declined', 'pending') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- جدول التسجيل (Logs)
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id VARCHAR(50),
    details TEXT,
    log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- تريجرز لجدول users
DELIMITER //

-- تريجر بعد إدراج مستخدم
CREATE TRIGGER after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    INSERT INTO logs (user_id, action, table_name, record_id, details, log_time)
    VALUES (NEW.id, 'USER_INSERT', 'users', NEW.id, CONCAT('تم إنشاء مستخدم جديد: ', NEW.first_name, ' ', NEW.last_name), NOW());
END //

-- تريجر بعد تحديث مستخدم
CREATE TRIGGER after_user_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    INSERT INTO logs (user_id, action, table_name, record_id, details, log_time)
    VALUES (NEW.id, 'USER_UPDATE', 'users', NEW.id, CONCAT('تم تحديث بيانات المستخدم: ', NEW.first_name, ' ', NEW.last_name), NOW());
END //

-- تريجر بعد حذف مستخدم
CREATE TRIGGER after_user_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    INSERT INTO logs (user_id, action, table_name, record_id, details, log_time)
    VALUES (NULL, 'USER_DELETE', 'users', OLD.id, CONCAT('تم حذف المستخدم: ', OLD.first_name, ' ', OLD.last_name), NOW());
END //

-- تريجرز لجدول orders
-- تريجر بعد إدراج طلب
CREATE TRIGGER after_order_insert
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    INSERT INTO logs (user_id, action, table_name, record_id, details, log_time)
    VALUES (NEW.user_id, 'ORDER_INSERT', 'orders', NEW.order_id, CONCAT('تم إنشاء طلب جديد: ', NEW.order_id, ', نوع الحدث: ', NEW.event_type), NOW());
END //

-- تريجر بعد تحديث طلب
CREATE TRIGGER after_order_update
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    INSERT INTO logs (user_id, action, table_name, record_id, details, log_time)
    VALUES (NEW.user_id, 'ORDER_UPDATE', 'orders', NEW.order_id, CONCAT('تم تحديث الطلب: ', NEW.order_id, ', الحالة: ', NEW.status), NOW());
END //

-- تريجر بعد حذف طلب
CREATE TRIGGER after_order_delete
AFTER DELETE ON orders
FOR EACH ROW
BEGIN
    INSERT INTO logs (user_id, action, table_name, record_id, details, log_time)
    VALUES (OLD.user_id, 'ORDER_DELETE', 'orders', OLD.order_id, CONCAT('تم حذف الطلب: ', OLD.order_id), NOW());
END //

-- تريجرز لجدول status
-- تريجر بعد إدراج حالة
CREATE TRIGGER after_status_insert
AFTER INSERT ON status
FOR EACH ROW
BEGIN
    DECLARE user_id_val INT;
    SELECT user_id INTO user_id_val FROM orders WHERE order_id = NEW.order_id LIMIT 1;
    INSERT INTO logs (user_id, action, table_name, record_id, details, log_time)
    VALUES (user_id_val, 'STATUS_INSERT', 'status', NEW.order_id, CONCAT('تم إضافة حالة للطلب: ', NEW.order_id, ', الحال

ة: ', NEW.order_status), NOW());
END //

-- تريجر بعد تحديث حالة
CREATE TRIGGER after_status_update
AFTER UPDATE ON status
FOR EACH ROW
BEGIN
    DECLARE user_id_val INT;
    SELECT user_id INTO user_id_val FROM orders WHERE order_id = NEW.order_id LIMIT 1;
    INSERT INTO logs (user_id, action, table_name, record_id, details, log_time)
    VALUES (user_id_val, 'STATUS_UPDATE', 'status', NEW.order_id, CONCAT('تم تحديث حالة الطلب: ', NEW.order_id, ', الحالة الجديدة: ', NEW.order_status), NOW());
END //

-- تريجر بعد حذف حالة
CREATE TRIGGER after_status_delete
AFTER DELETE ON status
FOR EACH ROW
BEGIN
    DECLARE user_id_val INT;
    SELECT user_id INTO user_id_val FROM orders WHERE order_id = OLD.order_id LIMIT 1;
    INSERT INTO logs (user_id, action, table_name, record_id, details, log_time)
    VALUES (user_id_val, 'STATUS_DELETE', 'status', OLD.order_id, CONCAT('تم حذف حالة الطلب: ', OLD.order_id), NOW());
END //

DELIMITER ;