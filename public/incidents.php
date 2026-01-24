<?php
require_once '../includes/header.php';

$conn = getDBConnection();

// Show success message if incident was deleted
if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    echo '<div class="alert alert-success">Incident deleted successfully!</div>';
}

// Get all incidents with system names
$stmt = $conn->prepare("
    SELECT i.*, s.name as system_name, s.type as system_type 
    FROM incidents i 
    JOIN systems s ON i.affected_system_id = s.id 
    ORDER BY i.date_time DESC
");
$stmt->execute();
$incidents = $stmt->fetchAll();

?>

<h2 class="page-title">Incidents</h2>

<div style="margin-bottom: 20px;">
    <a href="add_incident.php" class="btn btn-success">Add New Incident</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>Date & Time</th>
                <th>Affected System</th>
                <th>Severity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($incidents) > 0): ?>
                <?php foreach ($incidents as $incident): ?>
                    <tr data-incident-id="<?php echo escape($incident['id']); ?>">
                        <td><?php echo escape($incident['id']); ?></td>
                        <td><?php echo escape($incident['incident_type']); ?></td>
                        <td><?php echo escape(date('Y-m-d H:i', strtotime($incident['date_time']))); ?></td>
                        <td><?php echo escape($incident['system_name']); ?> <small>(<?php echo escape($incident['system_type']); ?>)</small></td>
                        <td><span class="badge <?php echo getSeverityColor($incident['severity']); ?>"><?php echo escape($incident['severity']); ?></span></td>
                        <td>
                            <span class="badge status-badge <?php echo getStatusColor($incident['status']); ?>" style="cursor: pointer;" onclick="cycleIncidentStatus(<?php echo escape($incident['id']); ?>, '<?php echo escape($incident['status']); ?>')"><?php echo escape($incident['status']); ?></span>
                        </td>
                        <td class="action-buttons">
                            <a href="view_incident.php?id=<?php echo escape($incident['id']); ?>" class="btn btn-secondary" style="padding: 5px 12px; font-size: 14px;">View</a>
                            <a href="edit_incident.php?id=<?php echo escape($incident['id']); ?>" class="btn" style="padding: 5px 12px; font-size: 14px;">Edit</a>
                            <a href="delete_incident.php?id=<?php echo escape($incident['id']); ?>" class="btn btn-danger" style="padding: 5px 12px; font-size: 14px;" onclick="return confirmDelete('Are you sure you want to delete this incident?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="empty-state">No incidents found. <a href="add_incident.php">Add your first incident</a></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
