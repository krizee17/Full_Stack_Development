<?php
require_once '../includes/header.php';

$conn = getDBConnection();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $incident_type = trim($_POST['incident_type'] ?? '');
    $date_time = trim($_POST['date_time'] ?? '');
    $affected_system_id = intval($_POST['affected_system_id'] ?? 0);
    $severity = $_POST['severity'] ?? '';
    $status = $_POST['status'] ?? 'Detected';
    $resolution_notes = trim($_POST['resolution_notes'] ?? '');
    
    // Validation
    if (empty($incident_type) || empty($date_time) || $affected_system_id == 0 || empty($severity)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO incidents (incident_type, date_time, affected_system_id, severity, status, resolution_notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$incident_type, $date_time, $affected_system_id, $severity, $status, $resolution_notes]);
            
            $success = 'Incident added successfully!';
            // Clear form
            $incident_type = $date_time = $severity = $status = $resolution_notes = '';
            $affected_system_id = 0;
        } catch (PDOException $e) {
            $error = 'Error adding incident: ' . $e->getMessage();
        }
    }
}

// Get all systems for dropdown
$stmt = $conn->prepare("SELECT id, name, type FROM systems WHERE status = 'Active' ORDER BY name");
$stmt->execute();
$systems = $stmt->fetchAll();
?>

<h2 class="page-title">Add New Incident</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo escape($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo escape($success); ?></div>
<?php endif; ?>

<form method="POST" action="">
    <div class="form-group">
        <label for="incident_type">Incident Type *</label>
        <input type="text" id="incident_type" name="incident_type" value="<?php echo escape($incident_type ?? ''); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="date_time">Date & Time *</label>
        <input type="datetime-local" id="date_time" name="date_time" value="<?php echo escape($date_time ?? ''); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="affected_system_id">Affected System *</label>
        <select id="affected_system_id" name="affected_system_id" required>
            <option value="">Select a system</option>
            <?php foreach ($systems as $system): ?>
                <option value="<?php echo escape($system['id']); ?>" <?php echo (isset($affected_system_id) && $affected_system_id == $system['id']) ? 'selected' : ''; ?>>
                    <?php echo escape($system['name']); ?> (<?php echo escape($system['type']); ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="severity">Severity *</label>
        <select id="severity" name="severity" required>
            <option value="">Select severity</option>
            <option value="Low" <?php echo (isset($severity) && $severity == 'Low') ? 'selected' : ''; ?>>Low</option>
            <option value="Medium" <?php echo (isset($severity) && $severity == 'Medium') ? 'selected' : ''; ?>>Medium</option>
            <option value="High" <?php echo (isset($severity) && $severity == 'High') ? 'selected' : ''; ?>>High</option>
            <option value="Critical" <?php echo (isset($severity) && $severity == 'Critical') ? 'selected' : ''; ?>>Critical</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="status">Status *</label>
        <select id="status" name="status" required>
            <option value="Detected" <?php echo (isset($status) && $status == 'Detected') ? 'selected' : ''; ?>>Detected</option>
            <option value="Investigating" <?php echo (isset($status) && $status == 'Investigating') ? 'selected' : ''; ?>>Investigating</option>
            <option value="Resolved" <?php echo (isset($status) && $status == 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="resolution_notes">Resolution Notes</label>
        <textarea id="resolution_notes" name="resolution_notes"><?php echo escape($resolution_notes ?? ''); ?></textarea>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-success">Add Incident</button>
        <a href="incidents.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once '../includes/footer.php'; ?>
