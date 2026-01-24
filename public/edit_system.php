<?php
require_once '../includes/header.php';

$conn = getDBConnection();
$error = '';
$success = '';

$id = intval($_GET['id'] ?? 0);

if ($id == 0) {
    header('Location: systems.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? '';
        $description = trim($_POST['description'] ?? '');
        
        // Validation
        if (empty($name) || empty($type)) {
            $error = 'Please fill in all required fields.';
        } else {
            try {
                // Use prepared statement to prevent SQL injection
                $stmt = $conn->prepare("UPDATE systems SET name = ?, type = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $type, $description, $id]);
                
                $success = 'System updated successfully!';
            } catch (PDOException $e) {
                $error = 'Error updating system: ' . $e->getMessage();
            }
        }
    }
}

// Get system data
$stmt = $conn->prepare("SELECT * FROM systems WHERE id = ?");
$stmt->execute([$id]);
$system = $stmt->fetch();

if (!$system) {
    header('Location: systems.php');
    exit;
}
?>

<h2 class="page-title">Edit System</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo escape($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo escape($success); ?></div>
<?php endif; ?>

<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    
    <div class="form-group">
        <label for="name">System Name *</label>
        <input type="text" id="name" name="name" value="<?php echo escape($system['name']); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="type">System Type *</label>
        <select id="type" name="type" required>
            <option value="">Select type</option>
            <option value="Server" <?php echo $system['type'] == 'Server' ? 'selected' : ''; ?>>Server</option>
            <option value="Application" <?php echo $system['type'] == 'Application' ? 'selected' : ''; ?>>Application</option>
            <option value="Database" <?php echo $system['type'] == 'Database' ? 'selected' : ''; ?>>Database</option>
            <option value="Firewall" <?php echo $system['type'] == 'Firewall' ? 'selected' : ''; ?>>Firewall</option>
            <option value="Mail Gateway" <?php echo $system['type'] == 'Mail Gateway' ? 'selected' : ''; ?>>Mail Gateway</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description"><?php echo escape($system['description']); ?></textarea>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn">Update System</button>
        <a href="systems.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once '../includes/footer.php'; ?>
