<?php
header('Content-Type: application/json');
include __DIR__ . '/../config/db.php';

$user_id = 1; // Hardcoded user
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$exclude_expense_id = isset($_GET['exclude_expense_id']) ? intval($_GET['exclude_expense_id']) : 0;

if (!$category_id) {
    echo json_encode(['error's => 'Category not specified']);
    exit;
}

// 1. Get user's total monthly income
$user_stmt = $conn->prepare("SELECT monthly_income FROM users WHERE id = ?");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$monthly_income = $user['monthly_income'] ?? 0;

// 2. Get the budget percentage for the category
$budget_stmt = $conn->prepare("SELECT percentage FROM category_budgets WHERE user_id = ? AND category_id = ?");
$budget_stmt->bind_param('ii', $user_id, $category_id);
$budget_stmt->execute();
$budget = $budget_stmt->get_result()->fetch_assoc();
$percentage = $budget['percentage'] ?? 0;
$budget_limit = ($monthly_income * $percentage) / 100;

// 3. Get current spending in this category for the current month
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');
$sql = "SELECT SUM(amount) as total FROM expenses WHERE user_id = ? AND category_id = ? AND date >= ? AND date <= ?";
if ($exclude_expense_id > 0) {
    $sql .= " AND id != ?"; // Exclude the current expense when editing
}
$spending_stmt = $conn->prepare($sql);
$exclude_expense_id > 0 ? $spending_stmt->bind_param('iissi', $user_id, $category_id, $current_month_start, $current_month_end, $exclude_expense_id) : $spending_stmt->bind_param('iiss', $user_id, $category_id, $current_month_start, $current_month_end);
$spending_stmt->execute();
$current_spending = $spending_stmt->get_result()->fetch_assoc()['total'] ?? 0;

echo json_encode(['budget_limit' => $budget_limit, 'current_spending' => $current_spending]);