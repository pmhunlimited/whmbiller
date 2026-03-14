-- Initial Schema for WHMBiller

CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` TEXT
);

CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) UNIQUE NOT NULL,
    `permissions` TEXT -- JSON list of permissions
);

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'staff', 'client', 'reseller') DEFAULT 'client',
    `role_id` INT, -- For granular staff roles
    `status` ENUM('active', 'suspended', 'pending') DEFAULT 'active',
    `two_factor_secret` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
);

CREATE TABLE IF NOT EXISTS `login_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50),
    `ip_address` VARCHAR(45),
    `status` ENUM('success', 'failed') NOT NULL,
    `attempt_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `ip_protection` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) UNIQUE NOT NULL,
    `status` ENUM('whitelist', 'blacklist', 'none') DEFAULT 'none',
    `failed_attempts` INT DEFAULT 0,
    `successful_sessions` INT DEFAULT 0,
    `block_until` TIMESTAMP NULL,
    `last_attempt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `country_protection` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `country_code` CHAR(2) UNIQUE NOT NULL,
    `country_name` VARCHAR(100) NOT NULL,
    `status` ENUM('whitelisted', 'blacklisted', 'not_specified') DEFAULT 'not_specified'
);

CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(15, 2) NOT NULL,
    `recurring_period` ENUM('monthly', 'quarterly', 'semi_annually', 'annually', 'biennially', 'triennially') DEFAULT 'monthly',
    `type` VARCHAR(50) NOT NULL, -- e.g., 'hosting', 'domain', 'vps'
    `module_settings` TEXT, -- JSON settings for WHM/CloudLinux
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `servers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `hostname` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45),
    `username` VARCHAR(50) NOT NULL,
    `api_token` TEXT NOT NULL,
    `type` VARCHAR(50) DEFAULT 'whm',
    `status` ENUM('active', 'disabled') DEFAULT 'active'
);

CREATE TABLE IF NOT EXISTS `user_services` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `server_id` INT,
    `domain` VARCHAR(255),
    `username` VARCHAR(50),
    `password` VARCHAR(255),
    `status` ENUM('active', 'suspended', 'terminated', 'pending') DEFAULT 'pending',
    `next_due_date` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    FOREIGN KEY (`server_id`) REFERENCES `servers`(`id`)
);

CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `amount` DECIMAL(15, 2) NOT NULL,
    `currency` CHAR(3) DEFAULT 'NGN',
    `status` ENUM('unpaid', 'paid', 'cancelled', 'refunded') DEFAULT 'unpaid',
    `due_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

CREATE TABLE IF NOT EXISTS `reseller_settings` (
    `user_id` INT PRIMARY KEY,
    `wholesale_discount` DECIMAL(5, 2) DEFAULT 0.00, -- Percentage
    `retail_markup` DECIMAL(5, 2) DEFAULT 0.00, -- Percentage
    `custom_domain` VARCHAR(255),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

CREATE TABLE IF NOT EXISTS `credit_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `amount` DECIMAL(15, 2) NOT NULL,
    `type` ENUM('add', 'deduct') NOT NULL,
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);
