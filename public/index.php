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

<h2 class="mb-4">Dashboard</h2>

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

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>5 Most Recent Expenses</span>
    <a href="clear_expenses.php" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete ALL expenses? This action cannot be undone.');">Clear All Expenses</a>
  </div>
  <div class="card-body">
    <table class="table table-striped">
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
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>₹<?php echo number_format($row['amount'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" class="text-center">No recent expenses found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="card-header">Expense Breakdown</div>
  <div class="card-body">
    <canvas id="expenseChart" style="max-height: 300px;"></canvas>
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
      backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14']
    }]
  }
});
</script>

</div>

<?php include("footer.php"); ?>
