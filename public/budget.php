<?php
include __DIR__ . '/../config/db.php';

// For this simple app, we'll hardcode the user ID to 1
$user_id = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $budgets = $_POST['budgets'] ?? [];

    // Use a transaction for atomicity
    $conn->begin_transaction();
    try {
        // First, clear old budgets for the user
        $stmt_delete = $conn->prepare("DELETE FROM category_budgets WHERE user_id = ?");
        $stmt_delete->bind_param('i', $user_id);
        $stmt_delete->execute();

        // Then, insert the new ones
        $stmt_insert = $conn->prepare("INSERT INTO category_budgets (user_id, category_id, percentage) VALUES (?, ?, ?)");
        foreach ($budgets as $category_id => $percentage) {
            $percentage = intval($percentage);
            if ($percentage > 0) { // Only save categories with a set budget
                $stmt_insert->bind_param('iii', $user_id, $category_id, $percentage);
                $stmt_insert->execute();
            }
        }
        $conn->commit();
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        throw $exception;
    }

    header('Location: index.php');
    exit;
}

// Fetch all categories and the user's current budget settings
$sql = "SELECT c.id, c.name, cb.percentage 
        FROM categories c 
        LEFT JOIN category_budgets cb ON c.id = cb.category_id AND cb.user_id = ?
        ORDER BY c.name";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'header.php';
?>
    <h2 class="mb-4">Set Category Budgets</h2>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST">
                <p class="text-muted">Allocate a percentage of your monthly income to each category.</p>
                <?php foreach ($categories as $category): ?>
                <div class="row g-2 align-items-center mb-2">
                    <div class="col-4">
                        <label for="cat-<?php echo $category['id']; ?>" class="col-form-label"><?php echo htmlspecialchars($category['name']); ?></label>
                    </div>
                    <div class="col-8">
                        <div class="input-group">
                            <input type="number" min="0" max="100" id="cat-<?php echo $category['id']; ?>" name="budgets[<?php echo $category['id']; ?>]" class="form-control budget-input" value="<?php echo htmlspecialchars($category['percentage'] ?? 0); ?>">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between align-items-center fw-bold fs-5">
                    <span>Total Allocated:</span>
                    <span id="total-percentage">0%</span>
                </div>
                <div class="alert alert-warning mt-2 d-none" id="total-warning">Warning: Total allocation exceeds 100%.</div>
                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-primary">Save Budget</button>
                    <a href="reset_budget.php" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to reset all budget allocations?');">Reset Budget</a>
                </div>
            </form>
        </div>
    </div>
<script src="../assets/js/budget.js"></script>
<?php
include 'footer.php';
?>