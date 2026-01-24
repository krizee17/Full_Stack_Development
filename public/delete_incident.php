<?php
require_once '../includes/header.php';

$conn = getDBConnection();

$id = intval($_GET['id'] ?? 0);

if ($id == 0) {
    header('Location: incidents.php');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        echo '<div class="alert alert-error">Invalid security token. Please try again.</div>';
    } else {
        try {
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("DELETE FROM incidents WHERE id = ?");
            $stmt->execute([$id]);
            
            header('Location: incidents.php?deleted=1');
            exit;
        } catch (PDOException $e) {
            echo '<div class="alert alert-error">Error deleting incident: ' . escape($e->getMessage()) . '</div>';
        }
    }
}

// Get incident data for confirmation
$stmt = $conn->prepare("SELECT * FROM incidents WHERE id = ?");
$stmt->execute([$id]);
$incident = $stmt->fetch();

if (!$incident) {
    header('Location: incidents.php');
    exit;
}
?>

<h2 class="page-title">Delete Incident</h2>

<div class="alert alert-error">
    <strong>Warning:</strong> Are you sure you want to delete this incident? This action cannot be undone.
</div>

<div class="table-container" style="max-width: 600px;">
    <table>
        <tr>
            <th>ID</th>
            <td><?php echo escape($incident['id']); ?></td>
        </tr>
        <tr>
            <th>Incident Type</th>
            <td><?php echo escape($incident['incident_type']); ?></td>
        </tr>
        <tr>
            <th>Date & Time</th>
            <td><?php echo escape(date('Y-m-d H:i', strtotime($incident['date_time']))); ?></td>
        </tr>
        <tr>
            <th>Severity</th>
            <td><span class="badge <?php echo getSeverityColor($incident['severity']); ?>"><?php echo escape($incident['severity']); ?></span></td>
        </tr>
    </table>
</div>

<form method="POST" action="" style="margin-top: 20px;">
    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
    <button type="submit" class="btn btn-danger">Yes, Delete Incident</button>
    <a href="incidents.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once '../includes/footer.php'; ?>
