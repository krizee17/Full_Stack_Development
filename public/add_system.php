<?php
require_once '../includes/header.php';
require_once '../includes/validation.php';

$conn = getDBConnection();
$error = '';
$success = '';
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? '';
        $description = trim($_POST['description'] ?? '');
        
        // Validate all fields
        $validation = validateSystemForm([
            'name' => $name,
            'type' => $type,
            'description' => $description
        ]);
        
        if (!$validation['valid']) {
            $errors = $validation['errors'];
        } else {
            try {
                // Use prepared statement to prevent SQL injection
                $stmt = $conn->prepare("INSERT INTO systems (name, type, description) VALUES (?, ?, ?)");
                $stmt->execute([$name, $type, $description]);
                
                $success = 'System added successfully!';
                // Clear form
                $name = $type = $description = '';
            } catch (PDOException $e) {
                $error = 'Error adding system: ' . $e->getMessage();
            }
        }
    }
}
?>

<h2 class="page-title">Add New System</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo escape($error); ?></div>
<?php endif; ?>

<?php if (count($errors) > 0): ?>
    <div class="alert alert-error">
        <strong>Please fix the following errors:</strong>
        <ul style="margin: 10px 0 0 20px; padding: 0;">
            <?php foreach ($errors as $err): ?>
                <li><?php echo escape($err); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo escape($success); ?></div>
<?php endif; ?>

<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <div class="form-group">
        <label for="name">System Name *</label>
        <input type="text" id="name" name="name" value="<?php echo escape($name ?? ''); ?>" required maxlength="255" minlength="2">
    </div>
    
    <div class="form-group">
        <label for="type">System Type *</label>
        <select id="type" name="type" required>
            <option value="">Select type</option>
            <option value="Server" <?php echo (isset($type) && $type == 'Server') ? 'selected' : ''; ?>>Server</option>
            <option value="Application" <?php echo (isset($type) && $type == 'Application') ? 'selected' : ''; ?>>Application</option>
            <option value="Database" <?php echo (isset($type) && $type == 'Database') ? 'selected' : ''; ?>>Database</option>
            <option value="Firewall" <?php echo (isset($type) && $type == 'Firewall') ? 'selected' : ''; ?>>Firewall</option>
            <option value="Mail Gateway" <?php echo (isset($type) && $type == 'Mail Gateway') ? 'selected' : ''; ?>>Mail Gateway</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?php echo escape($description ?? ''); ?></textarea>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-success">Add System</button>
        <a href="systems.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once '../includes/footer.php'; ?>
