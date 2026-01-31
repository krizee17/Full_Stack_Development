<?php
require_once '../includes/header.php';
require_once '../includes/validation.php';

$conn = getDBConnection();
$error = '';
$success = '';
$errors = [];

$id = intval($_GET['id'] ?? 0);

if ($id == 0) {
    header('Location: incidents.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $incident_type = trim($_POST['incident_type'] ?? '');
        $date_time = trim($_POST['date_time'] ?? '');
        $affected_system_id = intval($_POST['affected_system_id'] ?? 0);
        $severity = $_POST['severity'] ?? '';
        $status = $_POST['status'] ?? 'Detected';
        $resolution_notes = trim($_POST['resolution_notes'] ?? '');
        
        // Validate all fields
        $validation = validateIncidentForm([
            'incident_type' => $incident_type,
            'date_time' => $date_time,
            'affected_system_id' => $affected_system_id,
            'severity' => $severity,
            'status' => $status,
            'resolution_notes' => $resolution_notes
        ], $conn);
        
        if (!$validation['valid']) {
            $errors = $validation['errors'];
        } else {
            try {
                // Use prepared statement to prevent SQL injection
                $stmt = $conn->prepare("UPDATE incidents SET incident_type = ?, date_time = ?, affected_system_id = ?, severity = ?, status = ?, resolution_notes = ? WHERE id = ?");
                $stmt->execute([$incident_type, $date_time, $affected_system_id, $severity, $status, $resolution_notes, $id]);
                
                // Redirect to incidents page after successful update
                header('Location: incidents.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Error updating incident: ' . $e->getMessage();
            }
        }
    }
}

// Get incident data
$stmt = $conn->prepare("SELECT * FROM incidents WHERE id = ?");
$stmt->execute([$id]);
$incident = $stmt->fetch();
if (!$incident) {
    header('Location: incidents.php');
    exit;
}

// Get all systems for dropdown
$stmt = $conn->prepare("SELECT id, name, type FROM systems ORDER BY name");
$stmt->execute();
$systems = $stmt->fetchAll();
?>

<h2 class="page-title">Edit Incident</h2>

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
        <label for="incident_type">Incident Type *</label>
        <input type="text" id="incident_type" name="incident_type" value="<?php echo escape($incident['incident_type']); ?>" required maxlength="255" minlength="3">
    </div>
    
    <div class="form-group">
        <label for="date_time">Date & Time *</label>
        <input type="datetime-local" id="date_time" name="date_time" value="<?php echo escape(date('Y-m-d\TH:i', strtotime($incident['date_time']))); ?>" required>
    </div>
    
    <div class="form-group">
        <label for="affected_system_id">Affected System *</label>
        <select id="affected_system_id" name="affected_system_id" required>
            <option value="">Select a system</option>
            <?php 
            foreach ($systems as $system): 
            ?>
                <option value="<?php echo escape($system['id']); ?>" <?php echo $incident['affected_system_id'] == $system['id'] ? 'selected' : ''; ?>>
                    <?php echo escape($system['name']); ?> (<?php echo escape($system['type']); ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="form-group">
        <label for="severity">Severity *</label>
        <select id="severity" name="severity" required>
            <option value="">Select severity</option>
            <option value="Low" <?php echo $incident['severity'] == 'Low' ? 'selected' : ''; ?>>Low</option>
            <option value="Medium" <?php echo $incident['severity'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
            <option value="High" <?php echo $incident['severity'] == 'High' ? 'selected' : ''; ?>>High</option>
            <option value="Critical" <?php echo $incident['severity'] == 'Critical' ? 'selected' : ''; ?>>Critical</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="status">Status *</label>
        <select id="status" name="status" required>
            <option value="Detected" <?php echo $incident['status'] == 'Detected' ? 'selected' : ''; ?>>Detected</option>
            <option value="Investigating" <?php echo $incident['status'] == 'Investigating' ? 'selected' : ''; ?>>Investigating</option>
            <option value="Resolved" <?php echo $incident['status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="resolution_notes">Resolution Notes</label>
        <textarea id="resolution_notes" name="resolution_notes" maxlength="2000"><?php echo escape($incident['resolution_notes']); ?></textarea>
        <small>Max 2000 characters</small>
    </div>

    <div class="form-group">
        <button type="submit" class="btn">Update Incident</button>
        <a href="incidents.php" class="btn btn-secondary">Cancel</a>
    </div>
    
</form>

<?php require_once '../includes/footer.php'; ?>
