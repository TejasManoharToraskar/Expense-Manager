<?php

header('Content-Type: application/json');

include __DIR__ . '/../config/db.php';

$user_id = 1; // Hardcoded user

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = intval($_POST['category_id']);
    $amount = floatval($_POST['amount']);
    $description = $conn->real_escape_string($_POST['description']);
    $date = $conn->real_escape_string($_POST['date']);
    $force_save = isset($_POST['force_save']) && $_POST['force_save'] === 'true'; // New flag

    // --- Server-side budget validation ---
    // Only perform validation if not forcing save
    if (!$force_save) {
        // 1. Get user's income
        $user_stmt = $conn->prepare("SELECT monthly_income FROM users WHERE id = ?");
        $user_stmt->bind_param('i', $user_id);
        $user_stmt->execute();
        $monthly_income = $user_stmt->get_result()->fetch_assoc()['monthly_income'] ?? 0;

        // 2. Get budget percentage for the category
        $budget_stmt = $conn->prepare("SELECT percentage FROM category_budgets WHERE user_id = ? AND category_id = ?");
        $budget_stmt->bind_param('ii', $user_id, $category);
        $budget_stmt->execute();
        $percentage = $budget_stmt->get_result()->fetch_assoc()['percentage'] ?? 0;

        if ($percentage > 0 && $monthly_income > 0) {
            $budget_limit = ($monthly_income * $percentage) / 100;

            // 3. Get current spending for the category this month
            $current_month_start = date('Y-m-01');
            $current_month_end = date('Y-m-t');
            $spending_stmt = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE user_id = ? AND category_id = ? AND date >= ? AND date <= ?");
            $spending_stmt->bind_param('iiss', $user_id, $category, $current_month_start, $current_month_end);
            $spending_stmt->execute();
            $current_spending = $spending_stmt->get_result()->fetch_assoc()['total'] ?? 0;

            $remaining_budget = $budget_limit - $current_spending;

            if ($amount > $remaining_budget) {

            echo json_encode([
                "status" => "warning",
                "message" => "This expense exceeds remaining category budget.",
                "remaining_budget" => number_format($remaining_budget, 2)
            ]);

    exit;
}
        }
    }

    // If no error message OR force_save is true, proceed to save
    if (empty($error_message) || $force_save) {
        $stmt = $conn->prepare('INSERT INTO expenses (user_id, category_id, amount, description, date) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iidss', $user_id, $category, $amount, $description, $date);
        $stmt->execute();
        echo json_encode([
        "status" => "success",
        "message" => "Expense added successfully"
        ]);

        exit;
    }
}

