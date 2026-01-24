<?php
require_once '../includes/header.php';

$conn = getDBConnection();

$id = intval($_GET['id'] ?? 0);

if ($id == 0) {
    header('Location: systems.php');
    exit;
}

// Check if system is used in incidents
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM incidents WHERE affected_system_id = ?");
$stmt->execute([$id]);
$incident_count = $stmt->fetch()['count'];

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        echo '<div class="alert alert-error">Invalid security token. Please try again.</div>';
    } else {
        if ($incident_count > 0) {
            echo '<div class="alert alert-error">Cannot delete system: It is associated with ' . escape($incident_count) . ' incident(s).</div>';
        } else {
            try {
                // Use prepared statement to prevent SQL injection
                $stmt = $conn->prepare("DELETE FROM systems WHERE id = ?");
                $stmt->execute([$id]);
                
                header('Location: systems.php?deleted=1');
                exit;
            } catch (PDOException $e) {
                echo '<div class="alert alert-error">Error deleting system: ' . escape($e->getMessage()) . '</div>';
            }
        }
    }
}

// Get system data for confirmation
$stmt = $conn->prepare("SELECT * FROM systems WHERE id = ?");
$stmt->execute([$id]);
$system = $stmt->fetch();

if (!$system) {
    header('Location: systems.php');
    exit;
}
?>

<h2 class="page-title">Delete System</h2>

<?php if ($incident_count > 0): ?>
    <div class="alert alert-error">
        <strong>Warning:</strong> This system is associated with <?php echo escape($incident_count); ?> incident(s) and cannot be deleted.
    </div>
<?php else: ?>
    <div class="alert alert-error">
        <strong>Warning:</strong> Are you sure you want to delete this system? This action cannot be undone.
    </div>
<?php endif; ?>

<div class="table-container" style="max-width: 600px;">
    <table>
        <tr>
            <th>ID</th>
            <td><?php echo escape($system['id']); ?></td>
        </tr>
        <tr>
            <th>Name</th>
            <td><?php echo escape($system['name']); ?></td>
        </tr>
        <tr>
            <th>Type</th>
            <td><?php echo escape($system['type']); ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?php echo escape($system['status']); ?></td>
        </tr>
    </table>
</div>

<?php if ($incident_count == 0): ?>
    <form method="POST" action="" style="margin-top: 20px;">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <button type="submit" class="btn btn-danger">Yes, Delete System</button>
        <a href="systems.php" class="btn btn-secondary">Cancel</a>
    </form>
<?php else: ?>
    <div style="margin-top: 20px;">
        <a href="systems.php" class="btn btn-secondary">Back to Systems</a>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
