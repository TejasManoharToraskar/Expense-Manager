# 💰 Expense Manager (PHP + Bootstrap + Pandas)

A simple **Expense Management Web App** built with **PHP, MySQL, Bootstrap, JavaScript, and Python (Pandas)**.
It helps users **track, manage, and analyze expenses** with category-based reports.

## 🚀 Features
- Add, Edit, Delete Expenses
- Category-wise Expense Tracking
- View Expenses in a Responsive Table
- Python + Pandas for **Analytics & Graphs**
- Reports: Category-wise & Monthly

## 🛠 Tech Stack
- **Frontend:** HTML, CSS, Bootstrap, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Analytics:** Python, Pandas, Matplotlib
- **Tools:** XAMPP, Git, VS Code

## 📂 Project Structure
```
expense-manager/
│── README.md
│── db.sql
│── report.py
│── .gitignore
│
├── config/
│   └── db.php
│
├── public/
│   ├── add_expense.php
│   ├── budget.php
│   ├── clear_expenses.php
│   ├── delete_expenses.php
│   └── edit_expenses.php
│   └── footer.php
│   └── get_budget_status.php
│   └── header.php
│   └── index.php
│   └── profile.php
│   └── report.php
│   └── reset_budget.php
│   └── sidebar.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
│       └── budget.js
```

## 🚀 Quick setup
1️⃣ Download or Clone the Project (RUN THIS ON TERMINAL/CMD)
git clone https://github.com/TejasManoharToraskar/Expense-Manager

OR download the ZIP file from GitHub
 and extract it.

2️⃣ Move Project to XAMPP Folder

Copy the project folder into:

C:\xampp\htdocs\

Example:

C:\xampp\htdocs\Expense-Manager-v2

3️⃣ Start XAMPP

Open XAMPP
 and start:

Apache
MySQL

4️⃣ Create Database

Open browser:

http://localhost/phpmyadmin

Create a new database named:
expense_manager

Import the included:
db.sql

file into the database.

5️⃣ Run the Project

Open in browser:

http://localhost/Expense-Manager-v2/public

## Notes
- This is a simple starter project meant for learning and portfolio use.
- For production: add authentication, input validation, prepared statements everywhere, and secure password handling.
