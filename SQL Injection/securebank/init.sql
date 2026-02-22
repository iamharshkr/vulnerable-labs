CREATE DATABASE IF NOT EXISTS bank;
USE bank;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user',
    balance DECIMAL(10,2) DEFAULT 0.00
);

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    from_account INT NULL,
    to_account INT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('transfer','deposit','withdrawal') NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_account) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (to_account) REFERENCES users(id) ON DELETE SET NULL
);

-- Sample users (password = username + '123' for simplicity)
INSERT INTO users (username, password, role, balance) VALUES
('alice', 'alice123', 'user', 1000.00),
('bob', 'bob123', 'user', 500.00),
('admin', 'admin123', 'admin', 10000.00);

-- Sample transactions
INSERT INTO transactions (from_account, to_account, amount, type) VALUES
(1, 2, 50.00, 'transfer'),
(NULL, 1, 200.00, 'deposit'),
(2, NULL, 20.00, 'withdrawal');