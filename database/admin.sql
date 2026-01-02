-- ============================================
-- Admin Tables for MyShop
-- Run this in phpMyAdmin
-- ============================================

USE MiniShop;

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user
-- Username: admin
-- Password: admin123 (hashed)
INSERT INTO admin_users (username, password, email, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@minishop.com', 'Administrator');

-- Note: Password is 'admin123' - Change this after first login!

SELECT 'Admin tables created successfully!' as status;
SELECT 'Default login - Username: admin, Password: admin123' as credentials;