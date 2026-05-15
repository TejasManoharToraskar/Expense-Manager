<?php
include __DIR__ . '/../config/db.php';
include("header.php");
include("sidebar.php");

$user_id = 1; // Hardcoded user

// 1. Fetch User's Budget
$user_stmt = $conn->prepare("SELECT monthly_income FROM users WHERE id = ?");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$monthly_budget = $user_stmt->get_result()->fetch_assoc()['monthly_income'] ?? 0;

// 2. Calculate Total Expenses for the current month
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');
$month_stmt = $conn->prepare("SELECT SUM(amount) as total FROM expenses WHERE user_id = ? AND date >= ? AND date <= ?");
$month_stmt->bind_param('iss', $user_id, $current_month_start, $current_month_end);
$month_stmt->execute();
$total_expenses = $month_stmt->get_result()->fetch_assoc()['total'] ?? 0;

$remaining_budget = $monthly_budget - $total_expenses;
// Fetch categories
$cats_query = $conn->query("SELECT id, name FROM categories ORDER BY name");

$cats = [];

while($cat = $cats_query->fetch_assoc()) {
    $cats[] = $cat;
}

// 3. Fetch Recent Expenses
$recent_expenses_stmt = $conn->prepare("SELECT e.date, c.name as category, e.amount, e.description FROM expenses e JOIN categories c ON e.category_id = c.id WHERE e.user_id = ? ORDER BY e.date DESC, e.id DESC LIMIT 5");
$recent_expenses_stmt->bind_param('i', $user_id);
$recent_expenses_stmt->execute();
$recent_expenses = $recent_expenses_stmt->get_result();

// 4. Fetch data for the Pie Chart (current month's expenses by category)
$chart_data_stmt = $conn->prepare("SELECT c.name as category, SUM(e.amount) as total FROM expenses e JOIN categories c ON e.category_id = c.id WHERE e.user_id = ? AND e.date >= ? AND e.date <= ? GROUP BY c.name HAVING total > 0");
$chart_data_stmt->bind_param('iss', $user_id, $current_month_start, $current_month_end);
$chart_data_stmt->execute();
$chart_res = $chart_data_stmt->get_result();

$chart_labels = [];
$chart_values = [];
while($row = $chart_res->fetch_assoc()) {
    $chart_labels[] = $row['category'];
    $chart_values[] = $row['total'];
}
?>

<div class="main-content">

<div class="d-flex justify-content-between align-items-center mb-4">

    <h2>Dashboard</h2>

    <button class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addExpenseModal">

        + Add Expense

    </button>

</div>

<div class="row g-4 mb-4">

    <div class="col-md-4">
        <div class="card stats-card expense-card">
            <div class="card-body">

                <h5>This Month's Expenses</h5>

                <div class="amount">
                    ₹<?php echo number_format($total_expenses, 2); ?>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stats-card budget-card">
            <div class="card-body">

                <h5>Your Monthly Budget</h5>

                <div class="amount">
                    ₹<?php echo number_format($monthly_budget, 2); ?>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stats-card <?php echo ($remaining_budget < 0) ? 'danger-card' : 'remaining-card'; ?>">
            <div class="card-body">

                <h5>Remaining Budget</h5>

                <div class="amount">
                    ₹<?php echo number_format($remaining_budget, 2); ?>
                </div>

            </div>
        </div>
    </div>

</div>

    <div class="card expense-table-card mb-4">

    <div class="expense-table-header d-flex justify-content-between align-items-center">

        <h5>Recent Expenses</h5>

        <a href="clear_expenses.php"
           class="btn btn-sm btn-danger"
           onclick="return confirm('Are you sure you want to delete ALL expenses? This action cannot be undone.');">

            Clear All

        </a>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table expense-table align-middle"
                   id="expenseTable">

                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($recent_expenses->num_rows > 0): ?>

                        <?php while($row = $recent_expenses->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($row['date']); ?>
                                </td>

                                <td>
                                    <span class="category-badge">
                                        <?php echo htmlspecialchars($row['category']); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </td>

                                <td class="amount-text">
                                    ₹<?php echo number_format($row['amount'], 2); ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4" class="text-center py-4">
                                No recent expenses found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<div class="card analytics-card">

    <div class="analytics-header">
        <h5>Expense Analytics</h5>
    </div>

    <div class="analytics-body">

        <canvas id="expenseChart" style="max-height: 320px;"></canvas>

        <div class="insight-grid">

            <div class="insight-box">
                <div class="insight-title">
                    Total Expenses
                </div>

                <div class="insight-value">
                    ₹<?php echo number_format($total_expenses, 0); ?>
                </div>
            </div>

            <div class="insight-box">
                <div class="insight-title">
                    Monthly Budget
                </div>

                <div class="insight-value">
                    ₹<?php echo number_format($monthly_budget, 0); ?>
                </div>
            </div>

            <div class="insight-box">
                <div class="insight-title">
                    Remaining Budget
                </div>

                <div class="insight-value">
                    ₹<?php echo number_format($remaining_budget, 0); ?>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
const ctx = document.getElementById('expenseChart').getContext('2d');
new Chart(ctx, {
  type: 'pie',
  data: {
    labels: <?php echo json_encode($chart_labels); ?>,
    datasets: [{
      data: <?php echo json_encode($chart_values); ?>,
      backgroundColor: [
    '#6366f1',
    '#10b981',
    '#f59e0b',
    '#ef4444',
    '#8b5cf6',
    '#0ea5e9'
],
borderWidth: 2,
borderColor: '#ffffff'
    }]
  }
});
</script>

</div>

<!-- Add Expense Modal -->

<div class="modal fade"
     id="addExpenseModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">
                    Add New Expense
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body">

            <div id="expenseAlert"></div>

                <form id="expenseForm">

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label class="form-label">
                                Amount
                            </label>

                            <input type="number"
                                   step="0.01"
                                   name="amount"
                                   class="form-control"
                                   required>

                        </div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Date
                            </label>

                            <input type="date"
                                   name="date"
                                   class="form-control"
                                   required>

                        </div>

                        <div class="col-md-6">

    <label class="form-label">
        Category
    </label>

    <select name="category_id"
            class="form-select"
            required>

        <option value="">
            Select Category
        </option>

        <?php foreach($cats as $c): ?>

            <option value="<?php echo $c['id']; ?>">

                <?php echo ucfirst($c['name']); ?>

            </option>

        <?php endforeach; ?>

    </select>

</div>

                        <div class="col-md-6">

                            <label class="form-label">
                                Payment Method
                            </label>

                            <select name="payment_method"
                                    class="form-select">

                                <option>Cash</option>
                                <option>UPI</option>
                                <option>Card</option>
                                <option>Net Banking</option>

                            </select>

                        </div>

                        <div class="col-12">

                            <label class="form-label">
                                Description
                            </label>

                            <textarea name="description"
                                      class="form-control"
                                      rows="3">
                            </textarea>

                        </div>

                    </div>

                    <div class="mt-4 text-end">

                        <button type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">

                            Cancel

                        </button>

                        <button type="submit"
                                class="btn btn-primary">

                            Save Expense

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<?php include("footer.php"); ?>
