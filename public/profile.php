<?php
include __DIR__ . '/../config/db.php';

$user_id = 1; // Hardcoded user
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $role = $_POST['role'] === 'worker' ? 'worker' : 'student';
    $income = floatval($_POST['income']);

    $stmt = $conn->prepare('UPDATE users SET username = ?, role = ?, monthly_income = ? WHERE id = ?');
    $stmt->bind_param('ssdi', $username, $role, $income, $user_id);
    if ($stmt->execute()) {
        $success_message = "Profile updated successfully!";
    }
}

$stmt = $conn->prepare('SELECT username, role, monthly_income FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include("header.php");
?>

<h2 class="mb-4">Profile</h2>

<?php if (!empty($success_message)): ?>
    <div class="alert alert-success"><?php echo $success_message; ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-header">User Information</div>
  <div class="card-body">
    <form method="POST">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
      </div>
      <div class="mb-3">
        <label for="role" class="form-label">I am a...</label>
        <select id="role" name="role" class="form-select" required>
            <option value="student" <?php echo ($user['role'] ?? 'student') === 'student' ? 'selected' : ''; ?>>Student</option>
            <option value="worker" <?php echo ($user['role'] ?? '') === 'worker' ? 'selected' : ''; ?>>Worker</option>
        </select>
      </div>
      <div class="mb-3">
        <label for="income" class="form-label">
            <span id="income-label-text">Monthly Pocket Money</span> (₹)
        </label>
        <input id="income" type="number" step="0.01" name="income" class="form-control" value="<?php echo htmlspecialchars($user['monthly_income'] ?? 0); ?>" required>
      </div>
      <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const incomeLabel = document.getElementById('income-label-text');

        function updateLabel() {
            incomeLabel.textContent = (roleSelect.value === 'worker') ? 'Monthly Salary' : 'Monthly Pocket Money';
        }

        roleSelect.addEventListener('change', updateLabel);
        updateLabel(); // Set initial label on page load
    });
</script>

<?php include("footer.php"); ?>
