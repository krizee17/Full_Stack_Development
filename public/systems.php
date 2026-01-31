<?php
require_once '../includes/header.php';

$conn = getDBConnection();

// Show success message if system was deleted
if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    echo '<div class="alert alert-success">System deleted successfully!</div>';
}

// Get filter parameters
$name = trim($_GET['name'] ?? '');
$type = $_GET['type'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';

// Build query with filters
$query = "SELECT s.*, COUNT(i.id) as incident_count 
    FROM systems s 
    LEFT JOIN incidents i ON s.id = i.affected_system_id 
    WHERE 1=1";

$params = [];

if (!empty($name)) {
    $query .= " AND s.name LIKE ?";
    $params[] = "%$name%";
}

if (!empty($type)) {
    $query .= " AND s.type = ?";
    $params[] = $type;
}

if (!empty($status_filter)) {
    $query .= " AND s.status = ?";
    $params[] = $status_filter;
}

$query .= " GROUP BY s.id ORDER BY s.id ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$systems = $stmt->fetchAll();
?>

<h2 class="page-title">Systems & Assets</h2>

<div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
    <a href="add_system.php" class="btn btn-success">Add New System</a>
    <button onclick="openFilterModal()" class="btn btn-secondary">Filter</button>
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

<!-- Filter Modal -->
<div id="filterModal" class="modal-overlay">
    <div class="filter-modal">
        <div class="modal-header">
            <h3>Filter Systems</h3>
            <button class="modal-close" onclick="closeFilterModal()">&times;</button>
        </div>
        <form method="GET" action="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="name">System Name</label>
                    <input type="text" id="name" name="name" value="<?php echo escape($name); ?>" placeholder="Search by name">
                </div>
                
                <div class="form-group">
                    <label for="type">System Type</label>
                    <select id="type" name="type">
                        <option value="">All</option>
                        <option value="Server" <?php echo $type == 'Server' ? 'selected' : ''; ?>>Server</option>
                        <option value="Application" <?php echo $type == 'Application' ? 'selected' : ''; ?>>Application</option>
                        <option value="Database" <?php echo $type == 'Database' ? 'selected' : ''; ?>>Database</option>
                        <option value="Mail Gateway" <?php echo $type == 'Mail Gateway' ? 'selected' : ''; ?>>Mail Gateway</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status_filter">Status</label>
                    <select id="status_filter" name="status_filter">
                        <option value="">All</option>
                        <option value="Active" <?php echo $status_filter == 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $status_filter == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="Maintenance" <?php echo $status_filter == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <a href="systems.php" class="btn btn-secondary">Clear</a>
                <button type="submit" class="btn">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
