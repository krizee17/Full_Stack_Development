<?php
require_once '../includes/header.php';

$conn = getDBConnection();

// Show success message if system was deleted
if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    echo '<div class="alert alert-success">System deleted successfully!</div>';
}

// Get all systems with incident counts
$stmt = $conn->prepare("
    SELECT s.*, COUNT(i.id) as incident_count 
    FROM systems s 
    LEFT JOIN incidents i ON s.id = i.affected_system_id 
    GROUP BY s.id 
    ORDER BY s.id ASC
");
$stmt->execute();
$systems = $stmt->fetchAll();
?>

<h2 class="page-title">Systems & Assets</h2>

<div style="margin-bottom: 20px;">
    <a href="add_system.php" class="btn btn-success">Add New System</a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Description</th>
                <th>Incident Count</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($systems) > 0): ?>
                <?php foreach ($systems as $system): ?>
                    <tr>
                        <td><?php echo escape($system['id']); ?></td>
                        <td><?php echo escape($system['name']); ?></td>
                        <td><?php echo escape($system['type']); ?></td>
                        <td><?php echo escape($system['description'] ?: 'N/A'); ?></td>
                        <td><?php echo escape($system['incident_count']); ?></td>
                        <td class="action-buttons">
                            <a href="edit_system.php?id=<?php echo escape($system['id']); ?>" class="btn" style="padding: 5px 12px; font-size: 14px;">Edit</a>
                            <a href="delete_system.php?id=<?php echo escape($system['id']); ?>" class="btn btn-danger" style="padding: 5px 12px; font-size: 14px;" onclick="return confirmDelete('Are you sure you want to delete this system?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="empty-state">No systems found. <a href="add_system.php">Add your first system</a></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
