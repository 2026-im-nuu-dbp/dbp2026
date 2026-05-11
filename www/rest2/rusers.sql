-- =====================================================
-- RESTful API 教学範例 - 使用者資料表
-- =====================================================

-- 建立資料庫
CREATE DATABASE IF NOT EXISTS restful_demo;
USE restful_demo;

-- =====================================================
-- 使用者表 (users)
-- =====================================================
DROP TABLE IF EXISTS users;

CREATE TABLE rusers (
    -- 主鍵：使用者ID
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- 基本資訊
    name VARCHAR(100) NOT NULL COMMENT '使用者名稱',
    email VARCHAR(120) NOT NULL UNIQUE COMMENT '電子郵件（唯一值）',
    phone VARCHAR(20) COMMENT '電話號碼',
    
    -- 描述資訊
    bio TEXT COMMENT '個人簡介',
    age INT COMMENT '年齡',
    
    -- 位置資訊
    city VARCHAR(50) COMMENT '城市',
    country VARCHAR(50) COMMENT '國家',
    
    -- 帳戶狀態
    status ENUM('active', 'inactive', 'deleted') DEFAULT 'active' COMMENT '使用者狀態',
    
    -- 時間戳記
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新時間',
    
    -- 索引優化查詢
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
  COMMENT='使用者基本資訊表';

-- =====================================================
-- 初始範例資料
-- =====================================================
INSERT INTO rusers (name, email, phone, bio, age, city, country, status) VALUES
('張小明', 'ming@example.com', '0912345678', '軟體開發工程師', 28, '台北', '台灣', 'active'),
('李美芬', 'meifn@example.com', '0923456789', '產品經理', 32, '新竹', '台灣', 'active'),
('王建文', 'jianwen@example.com', '0934567890', 'UI設計師', 25, '台中', '台灣', 'active'),
('陳思琪', 'siqi@example.com', '0945678901', '資料分析師', 29, '高雄', '台灣', 'inactive'),
('黃怡庭', 'yiting@example.com', '0956789012', '專案管理', 31, '桃園', '台灣', 'active');

-- =====================================================
-- 驗證表結構
-- =====================================================
SELECT * FROM users;
