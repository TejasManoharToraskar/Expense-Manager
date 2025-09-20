-- Database schema and sample data for Expense Manager
CREATE DATABASE IF NOT EXISTS expense_manager;
USE expense_manager;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role ENUM('student', 'worker') DEFAULT 'student',
    monthly_income DECIMAL(10,2) DEFAULT 0.00
);

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE
);

CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT 1,
    category_id INT,
    amount DECIMAL(10,2),
    description TEXT,
    date DATE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS category_budgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category_id INT,
    percentage INT,
    UNIQUE KEY `user_category_unique` (`user_id`, `category_id`)
);

-- sample categories
INSERT IGNORE INTO categories (name) VALUES ('Food'), ('Travel'), ('Bills'), ('Shopping'), ('Health');

-- sample user (note: password is plain text for demo; replace with hashed in real apps)
INSERT IGNORE INTO users (id, username, password, monthly_income) VALUES (1, 'demo', 'demo123', 25000.00);

-- sample expenses
INSERT INTO expenses (user_id, category_id, amount, description, date) VALUES
(1, 1, 250.00, 'Lunch at cafe', '2025-08-01'),
(1, 2, 1200.00, 'Train tickets', '2025-08-03'),
(1, 3, 3000.00, 'Electricity bill', '2025-08-05'),
(1, 1, 180.50, 'Groceries', '2025-09-01');
