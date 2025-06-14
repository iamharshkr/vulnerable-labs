-- Create database if not exists
CREATE DATABASE IF NOT EXISTS securebank_lab;
USE securebank_lab;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS security_logs;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS accounts;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'premium', 'regular') DEFAULT 'regular',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create accounts table
CREATE TABLE accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    account_number VARCHAR(20) UNIQUE,
    balance DECIMAL(10,2) DEFAULT 0.00,
    account_type ENUM('checking', 'savings', 'premium') DEFAULT 'checking',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create transactions table
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    from_account INT,
    to_account INT,
    amount DECIMAL(10,2),
    memo VARCHAR(255),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_account) REFERENCES accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (to_account) REFERENCES accounts(id) ON DELETE CASCADE
);

-- Create security logs table
CREATE TABLE security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event VARCHAR(255),
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert test users (password is 'password' for all)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@securebank.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('john_doe', 'john@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'premium'),
('jane_smith', 'jane@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'regular'),
('bob_wilson', 'bob@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'regular');

-- Insert test accounts
INSERT INTO accounts (user_id, account_number, balance, account_type, notes) VALUES
(1, 'ACC-0001', 100000.00, 'premium', 'Administrator account with unlimited access'),
(2, 'ACC-1001', 15000.00, 'premium', 'Premium account with high balance'),
(3, 'ACC-1002', 5000.00, 'checking', 'FLAG{h0r1z0nt4l_pr1v_3sc4l4t10n_f0und} - Hidden in notes'),
(4, 'ACC-1003', 2500.00, 'savings', 'Regular savings account');

-- Insert test transactions (with hidden flags)
INSERT INTO transactions (id, from_account, to_account, amount, memo) VALUES
(12338, 1, 2, 1000.00, 'Regular transfer from admin'),
(12339, 2, 3, 500.00, 'Payment for services'),
(12340, 1, 3, 750.00, 'FLAG{1d0r_tr4ns4ct10n_l34k4g3} - Secret transaction'),
(12341, 3, 4, 200.00, 'Regular transfer between users'),
(12342, 2, 4, 300.00, 'Another regular transaction');

-- Insert some security log entries
INSERT INTO security_logs (user_id, event, ip_address) VALUES
(1, 'Admin login', '127.0.0.1'),
(2, 'User login', '127.0.0.1'),
(3, 'Failed login attempt', '192.168.1.100'),
(1, 'Admin panel access', '127.0.0.1');

-- Display setup completion message
SELECT 'SecureBank Lab Database Setup Complete!' as Status;
SELECT 'Total Users Created:' as Info, COUNT(*) as Count FROM users;
SELECT 'Total Accounts Created:' as Info, COUNT(*) as Count FROM accounts;
SELECT 'Total Transactions Created:' as Info, COUNT(*) as Count FROM transactions;
