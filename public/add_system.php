<?php
require_once '../includes/header.php';

$conn = getDBConnection();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'Active';
    
    // Validation
    if (empty($name) || empty($type)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO systems (name, type, description, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $type, $description, $status]);
            
            $success = 'System added successfully!';
            // Clear form
            $name = $type = $description = '';
            $status = 'Active';
        } catch (PDOException $e) {
            $error = 'Error adding system: ' . $e->getMessage();
        }
    }
}
?>

<h2 class="page-title">Add New System</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo escape($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo escape($success); ?></div>
<?php endif; ?>

<form method="POST" action="">
    <div class="form-group">
        <label for="name">System Name *</label>
        <input type="text" id="name" name="name" value="<?php echo escape($name ?? ''); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="type">System Type *</label>
        <select id="type" name="type" required>
            <option value="">Select type</option>
            <option value="Server" <?php echo (isset($type) && $type == 'Server') ? 'selected' : ''; ?>>Server</option>
            <option value="Application" <?php echo (isset($type) && $type == 'Application') ? 'selected' : ''; ?>>Application</option>
            <option value="Database" <?php echo (isset($type) && $type == 'Database') ? 'selected' : ''; ?>>Database</option>
            <option value="Mail Gateway" <?php echo (isset($type) && $type == 'Mail Gateway') ? 'selected' : ''; ?>>Mail Gateway</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?php echo escape($description ?? ''); ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="status">Status *</label>
        <select id="status" name="status" required>
            <option value="Active" <?php echo (isset($status) && $status == 'Active') ? 'selected' : ''; ?>>Active</option>
            <option value="Inactive" <?php echo (isset($status) && $status == 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
            <option value="Maintenance" <?php echo (isset($status) && $status == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
        </select>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-success">Add System</button>
        <a href="systems.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once '../includes/footer.php'; ?>
