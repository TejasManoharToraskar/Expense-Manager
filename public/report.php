<?php
include __DIR__ . '/../config/db.php';
include("header.php");

$user_id = 1; // Hardcoded user

// 1. Fetch data for the Bar Chart (current month's expenses by category)
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-t');
$chart_data_stmt = $conn->prepare("SELECT c.name as category, SUM(e.amount) as total FROM expenses e JOIN categories c ON e.category_id = c.id WHERE e.user_id = ? AND e.date >= ? AND e.date <= ? GROUP BY c.name HAVING total > 0 ORDER BY total DESC");
$chart_data_stmt->bind_param('iss', $user_id, $current_month_start, $current_month_end);
$chart_data_stmt->execute();
$chart_res = $chart_data_stmt->get_result();

$chart_labels = [];
$chart_values = [];
while($row = $chart_res->fetch_assoc()) {
    $chart_labels[] = $row['category'];
    $chart_values[] = $row['total'];
}

// 2. Fetch all expenses for the detailed table
$all_expenses_stmt = $conn->prepare("SELECT e.date, c.name as category, e.amount, e.description FROM expenses e JOIN categories c ON e.category_id = c.id WHERE e.user_id = ? ORDER BY e.date DESC, e.id DESC");
$all_expenses_stmt->bind_param('i', $user_id);
$all_expenses_stmt->execute();
$all_expenses = $all_expenses_stmt->get_result();
?>

<h2 class="mb-4">Expense Report</h2>

<div class="card mb-4">
  <div class="card-header">This Month's Expenses by Category</div>
  <div class="card-body">
    <canvas id="reportChart" style="max-height: 300px;"></canvas>
  </div>
</div>

<div class="card">
  <div class="card-header">Detailed Report</div>
  <div class="card-body">
    <table class="table table-striped table-hover">
      <thead class="table-dark">
        <tr>
          <th>Date</th>
          <th>Category</th>
          <th>Description</th>
          <th>Amount</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($all_expenses->num_rows > 0): ?>
            <?php while($row = $all_expenses->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>₹<?php echo number_format($row['amount'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4" class="text-center">No expenses recorded yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
new Chart(document.getElementById('reportChart'), {
  type: 'bar',
  data: {
    labels: <?php echo json_encode($chart_labels); ?>,
    datasets: [{
      label: 'Expenses (₹)',
      data: <?php echo json_encode($chart_values); ?>,
      backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14']
    }]
  },
  options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>

<?php include("footer.php"); ?>
