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
│
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
│       └── budget.js
```

## 🚀 Quick setup
1. Copy the project folder to your XAMPP `htdocs/` (or equivalent web root).
2. Import `db.sql` into your MySQL server (phpMyAdmin or CLI).
3. Update `config/db.php` with your DB credentials if needed.
4. Start Apache and MySQL (XAMPP) and open `http://localhost/expense-manager/public/`.
5. To run analytics:
   ```bash
   pip install pandas matplotlib mysql-connector-python
   python3 report.py
   ```

## Notes
- This is a simple starter project meant for learning and portfolio use.
- For production: add authentication, input validation, prepared statements everywhere, and secure password handling.
