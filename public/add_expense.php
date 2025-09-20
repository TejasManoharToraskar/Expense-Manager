<?php
include __DIR__ . '/../config/db.php';

$error_message = '';
$user_id = 1; // Hardcoded user

// Store POST data to repopulate form if validation fails
$old_category = $_POST['category'] ?? '';
$old_amount = $_POST['amount'] ?? '';
$old_description = $_POST['description'] ?? '';
$old_date = $_POST['date'] ?? date('Y-m-d'); // Default to current date if not set

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = intval($_POST['category']);
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
                $error_message = "This expense of ₹" . number_format($amount, 2) . " exceeds the remaining budget of ₹" . number_format($remaining_budget, 2) . " for this category.";
            }
        }
    }

    // If no error message OR force_save is true, proceed to save
    if (empty($error_message) || $force_save) {
        $stmt = $conn->prepare('INSERT INTO expenses (user_id, category_id, amount, description, date) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iidss', $user_id, $category, $amount, $description, $date);
        $stmt->execute();
        header('Location: index.php');
        exit;
    }
}
$cats = $conn->query('SELECT id,name FROM categories ORDER BY name');

include 'header.php';
?>
    <h2 class="mb-4">Add New Expense</h2>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <strong>Budget Exceeded!</strong> <?php echo $error_message; ?>
                    <button type="button" class="btn btn-sm btn-danger ms-3" id="proceed-anyway-btn">Proceed Anyway</button>
                </div>
            <?php endif; ?>
            <form method="POST" id="add-expense-form">
                <input type="hidden" name="force_save" id="force-save-input" value="false">
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select id="category" name="category" class="form-select" required>
                        <?php while($c = $cats->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo (isset($old_category) && $old_category == $c['id']) ? 'selected' : ''; ?>><?php echo $c['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount (₹)</label>
                    <input id="amount" type="number" step="0.01" name="amount" class="form-control" value="<?php echo htmlspecialchars($old_amount); ?>" required>
                    <div id="budget-warning" class="alert alert-warning mt-2 d-none"></div>
                </div>
                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input id="date" type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($old_date); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="2"><?php echo htmlspecialchars($old_description); ?></textarea>
                </div>
                <button class="btn btn-primary">Save</button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category');
    const amountInput = document.getElementById('amount');
    const warningDiv = document.getElementById('budget-warning');
    const proceedAnywayBtn = document.getElementById('proceed-anyway-btn'); // New
    const forceSaveInput = document.getElementById('force-save-input'); // New
    const addExpenseForm = document.getElementById('add-expense-form'); // New

    if (proceedAnywayBtn) { // Only if the button exists (i.e., there's a server-side error)
        proceedAnywayBtn.addEventListener('click', function() {
            forceSaveInput.value = 'true'; // Set the flag
            addExpenseForm.submit(); // Resubmit the form
        });
    }

    async function checkBudget() {
        const categoryId = categorySelect.value;
        const amount = parseFloat(amountInput.value) || 0;

        if (!categoryId || amount <= 0) {
            warningDiv.classList.add('d-none');
            return;
        }

        try {
            const response = await fetch(`get_budget_status.php?category_id=${categoryId}`);
            const data = await response.json();

            if (data.budget_limit > 0) {
                const remaining = data.budget_limit - data.current_spending;

                if (amount > remaining) {
                    warningDiv.innerHTML = `<strong>Warning:</strong> You have ₹${remaining.toFixed(2)} left in this category's budget. This expense will exceed it.`;
                    warningDiv.classList.remove('d-none');
                } else {
                    warningDiv.classList.add('d-none');
                }
            } else {
                warningDiv.classList.add('d-none'); // No budget set for this category
            }
        } catch (error) { console.error('Error fetching budget:', error); }
    }

    categorySelect.addEventListener('change', checkBudget);
    amountInput.addEventListener('input', checkBudget);
});
</script>
<?php
include 'footer.php';
?>
