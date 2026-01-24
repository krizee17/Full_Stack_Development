<?php
require_once '../includes/header.php';

$conn = getDBConnection();

$id = intval($_GET['id'] ?? 0);

if ($id == 0) {
    header('Location: incidents.php');
    exit;
}

// Get incident data with system information
$stmt = $conn->prepare("
    SELECT i.*, s.name as system_name, s.type as system_type, s.description as system_description 
    FROM incidents i 
    JOIN systems s ON i.affected_system_id = s.id 
    WHERE i.id = ?
");
$stmt->execute([$id]);
$incident = $stmt->fetch();

if (!$incident) {
    header('Location: incidents.php');
    exit;
}
?>

<h2 class="page-title">View Incident Details</h2>

<div class="table-container" style="max-width: 800px;">
    <table>
        <tr>
            <th style="width: 200px;">ID</th>
            <td><?php echo escape($incident['id']); ?></td>
        </tr>
        <tr>
            <th>Incident Type</th>
            <td><?php echo escape($incident['incident_type']); ?></td>
        </tr>
        <tr>
            <th>Date & Time</th>
            <td><?php echo escape(date('Y-m-d H:i:s', strtotime($incident['date_time']))); ?></td>
        </tr>
        <tr>
            <th>Affected System</th>
            <td><?php echo escape($incident['system_name']); ?> <small>(<?php echo escape($incident['system_type']); ?>)</small></td>
        </tr>
        <tr>
            <th>Severity</th>
            <td><span class="badge <?php echo getSeverityColor($incident['severity']); ?>"><?php echo escape($incident['severity']); ?></span></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><span class="badge <?php echo getStatusColor($incident['status']); ?>"><?php echo escape($incident['status']); ?></span></td>
        </tr>
        <tr>
            <th>Resolution Notes</th>
            <td><?php echo escape($incident['resolution_notes'] ?: 'No notes available'); ?></td>
        </tr>
        <tr>
            <th>Created At</th>
            <td><?php echo escape(date('Y-m-d H:i:s', strtotime($incident['created_at']))); ?></td>
        </tr>
        <tr>
            <th>Last Updated</th>
            <td><?php echo escape(date('Y-m-d H:i:s', strtotime($incident['updated_at']))); ?></td>
        </tr>
    </table>
</div>

<div style="margin-top: 20px;">
    <a href="edit_incident.php?id=<?php echo escape($incident['id']); ?>" class="btn">Edit Incident</a>
    <a href="incidents.php" class="btn btn-secondary">Back to List</a>
</div>

<?php require_once '../includes/footer.php'; ?>
