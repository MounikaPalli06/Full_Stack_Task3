-- Create Database
CREATE DATABASE IF NOT EXISTS user_management;
USE user_management;

-- Create Roles Table (Normalized - 3NF)
CREATE TABLE IF NOT EXISTS roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert Default Roles
INSERT INTO roles (role_name, description) VALUES
('user', 'Regular User'),
('admin', 'Administrator');

-- Create Users Table (Normalized - 3NF)
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role_id INT NOT NULL DEFAULT 1,
    profile_picture VARCHAR(255),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ER Diagram Explanation:
-- roles: 1:N relationship with users
-- users: Contains user data with foreign key reference to roles
-- Normalization:
-- 1NF: All attributes are atomic (no repeating groups)
-- 2NF: All non-key attributes depend on the entire primary key
-- 3NF: No transitive dependencies, role information is separated into roles table
