-- ========================================
-- RESTful API Demo Database
-- Database: api_demo
-- ========================================

CREATE DATABASE IF NOT EXISTS api_demo
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE api_demo;

-- ----------------------------------------
-- Table: users
-- ----------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id         INT          AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------
-- Sample data
-- ----------------------------------------
INSERT INTO users (name, email) VALUES
('John Doe',     'john@example.com'),
('Jane Smith',   'jane@example.com'),
('Bob Johnson',  'bob@example.com');
