-- WHMCS Replica Schema for WHMBiller

CREATE TABLE IF NOT EXISTS `tblclients` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `firstname` VARCHAR(100),
    `lastname` VARCHAR(100),
    `companyname` VARCHAR(100),
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `username` VARCHAR(50) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'staff', 'client', 'reseller') DEFAULT 'client',
    `status` ENUM('Active', 'Inactive', 'Closed') DEFAULT 'Active',
    `currency` INT DEFAULT 1,
    `credit` DECIMAL(15, 2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `tblproducts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(50), -- hosting, domain, vps, other
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `paytype` ENUM('Free', 'One Time', 'Recurring') DEFAULT 'Recurring',
    `server_id` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `tblpricing` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(50), -- product, addon, domain
    `relid` INT NOT NULL, -- product id
    `currency` INT NOT NULL,
    `mmonthly` DECIMAL(15, 2) DEFAULT -1.00,
    `annually` DECIMAL(15, 2) DEFAULT -1.00
);

CREATE TABLE IF NOT EXISTS `tblhosting` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `userid` INT NOT NULL,
    `packageid` INT NOT NULL,
    `serverid` INT,
    `domain` VARCHAR(255),
    `username` VARCHAR(100),
    `password` TEXT,
    `amount` DECIMAL(15, 2),
    `billingcycle` VARCHAR(50),
    `nextduedate` DATE,
    `domainstatus` ENUM('Pending', 'Active', 'Suspended', 'Terminated', 'Cancelled') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`userid`) REFERENCES `tblclients`(`id`),
    FOREIGN KEY (`packageid`) REFERENCES `tblproducts`(`id`)
);

CREATE TABLE IF NOT EXISTS `tblinvoices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `userid` INT NOT NULL,
    `date` DATE,
    `duedate` DATE,
    `subtotal` DECIMAL(15, 2),
    `total` DECIMAL(15, 2),
    `status` ENUM('Unpaid', 'Paid', 'Cancelled', 'Refunded') DEFAULT 'Unpaid',
    `paymentmethod` VARCHAR(100),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`userid`) REFERENCES `tblclients`(`id`)
);

CREATE TABLE IF NOT EXISTS `tblinvoiceitems` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoiceid` INT NOT NULL,
    `userid` INT NOT NULL,
    `relid` INT,
    `description` TEXT,
    `amount` DECIMAL(15, 2),
    FOREIGN KEY (`invoiceid`) REFERENCES `tblinvoices`(`id`)
);

CREATE TABLE IF NOT EXISTS `tblservers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100),
    `ipaddress` VARCHAR(50),
    `hostname` VARCHAR(255),
    `username` TEXT,
    `password` TEXT,
    `accesshash` TEXT,
    `type` VARCHAR(50) DEFAULT 'whm',
    `active` TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS `tblpaymentgateways` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `gateway` VARCHAR(100) NOT NULL,
    `setting` VARCHAR(100) NOT NULL,
    `value` TEXT
);

CREATE TABLE IF NOT EXISTS `tblgatewaylog` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `gateway` VARCHAR(100),
    `data` TEXT,
    `status` VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS `tbltickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `userid` INT NOT NULL,
    `did` INT, -- department id
    `subject` VARCHAR(255),
    `message` TEXT,
    `status` ENUM('Open', 'Answered', 'Customer-Reply', 'Closed') DEFAULT 'Open',
    `urgency` ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    `lastreply` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `tblticketdepartments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT
);

CREATE TABLE IF NOT EXISTS `tblconfiguration` (
    `setting` VARCHAR(100) PRIMARY KEY,
    `value` TEXT
);

-- Default Settings
INSERT INTO `tblconfiguration` (`setting`, `value`) VALUES
('CompanyName', 'WHMBiller'),
('SystemURL', 'http://localhost'),
('DefaultCurrency', 'NGN'),
('ExchangeRate', '1500'),
('ResellerUpgradeFee', '5000');

-- Security Tables
CREATE TABLE IF NOT EXISTS `tblipblocks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) UNIQUE NOT NULL,
    `status` ENUM('whitelist', 'blacklist', 'none') DEFAULT 'none',
    `failed_attempts` INT DEFAULT 0,
    `successful_sessions` INT DEFAULT 0,
    `block_until` TIMESTAMP NULL,
    `last_attempt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
