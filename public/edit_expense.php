<?php
include __DIR__ . '/../config/db.php';
if (empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = intval($_GET['id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = intval($_POST['category']);
    $amount = floatval($_POST['amount']);
    $description = $conn->real_escape_string($_POST['description']);
    $date = $conn->real_escape_string($_POST['date']);

    $stmt = $conn->prepare('UPDATE expenses SET category_id=?, amount=?, description=?, date=? WHERE id=?');
    $stmt->bind_param('idssi', $category, $amount, $description, $date, $id);
    $stmt->execute();
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare('SELECT * FROM expenses WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$expense = $res->fetch_assoc();
$cats = $conn->query('SELECT id,name FROM categories ORDER BY name');

include 'header.php';
?>
    <h2 class="mb-4">Edit Expense</h2>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-select" required>
                        <?php while($c = $cats->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($expense['category_id']==$c['id'])?'selected':''; ?>>
                                <?php echo $c['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount (₹)</label>
                    <input id="amount" type="number" step="0.01" name="amount" class="form-control" value="<?php echo $expense['amount']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input id="date" type="date" name="date" class="form-control" value="<?php echo $expense['date']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="2"><?php echo htmlspecialchars($expense['description']); ?></textarea>
                </div>
                <button class="btn btn-primary">Update</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category');
    const amountInput = document.getElementById('amount');
    const warningDiv = document.getElementById('budget-warning'); // We will add this div
    const expenseId = <?php echo $id; ?>;

    // Create and inject the warning div
    const warningElement = document.createElement('div');
    warningElement.id = 'budget-warning';
    warningElement.className = 'alert alert-warning mt-2 d-none';
    amountInput.parentNode.appendChild(warningElement);

    async function checkBudget() {
        const categoryId = categorySelect.value;
        const amount = parseFloat(amountInput.value) || 0;

        if (!categoryId || amount <= 0) {
            warningElement.classList.add('d-none');
            return;
        }

        try {
            const response = await fetch(`get_budget_status.php?category_id=${categoryId}&exclude_expense_id=${expenseId}`);
            const data = await response.json();

            if (data.budget_limit > 0) {
                const remaining = data.budget_limit - data.current_spending;
                if (amount > remaining) {
                    warningElement.innerHTML = `<strong>Warning:</strong> You have ₹${remaining.toFixed(2)} left in this category's budget. This update will exceed it.`;
                    warningElement.classList.remove('d-none');
                } else {
                    warningElement.classList.add('d-none');
                }
            } else { warningElement.classList.add('d-none'); }
        } catch (error) { console.error('Error fetching budget:', error); }
    }

    categorySelect.addEventListener('change', checkBudget);
    amountInput.addEventListener('input', checkBudget);
    checkBudget(); // Initial check on page load
});
</script>
<?php
include 'footer.php';
?>
