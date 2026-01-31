<?php
require_once '../includes/header.php';

$conn = getDBConnection();

// Show success message if incident was deleted
if (isset($_GET['deleted']) && $_GET['deleted'] == '1') {
    echo '<div class="alert alert-success">Incident deleted successfully!</div>';
}

// Get filter parameters
$severity = $_GET['severity'] ?? '';
$status = $_GET['status'] ?? '';
$system_id = intval($_GET['system_id'] ?? 0);
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');

// Build query with filters
$query = "SELECT i.*, s.name as system_name, s.type as system_type 
    FROM incidents i 
    JOIN systems s ON i.affected_system_id = s.id 
    WHERE 1=1";

$params = [];

if (!empty($severity)) {
    $query .= " AND i.severity = ?";
    $params[] = $severity;
}

if (!empty($status)) {
    $query .= " AND i.status = ?";
    $params[] = $status;
}

if ($system_id > 0) {
    $query .= " AND i.affected_system_id = ?";
    $params[] = $system_id;
}

if (!empty($date_from)) {
    $query .= " AND DATE(i.date_time) >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $query .= " AND DATE(i.date_time) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY i.affected_system_id ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$incidents = $stmt->fetchAll();

// Get all systems for dropdown
$stmt = $conn->prepare("SELECT id, name, type FROM systems ORDER BY name");
$stmt->execute();
$all_systems = $stmt->fetchAll();
?>

<h2 class="page-title">Incidents</h2>

<div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
    <a href="add_incident.php" class="btn btn-success">Add New Incident</a>
    <button onclick="openFilterModal()" class="btn btn-secondary">Filter</button>
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

<!-- Filter Modal -->
<div id="filterModal" class="modal-overlay">
    <div class="filter-modal">
        <div class="modal-header">
            <h3>Filter Incidents</h3>
            <button class="modal-close" onclick="closeFilterModal()">&times;</button>
        </div>
        <form method="GET" action="">
            <div class="modal-body">
                <div class="form-group">
                    <label for="severity">Severity</label>
                    <select id="severity" name="severity">
                        <option value="">All</option>
                        <option value="Low" <?php echo $severity == 'Low' ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo $severity == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="High" <?php echo $severity == 'High' ? 'selected' : ''; ?>>High</option>
                        <option value="Critical" <?php echo $severity == 'Critical' ? 'selected' : ''; ?>>Critical</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All</option>
                        <option value="Detected" <?php echo $status == 'Detected' ? 'selected' : ''; ?>>Detected</option>
                        <option value="Investigating" <?php echo $status == 'Investigating' ? 'selected' : ''; ?>>Investigating</option>
                        <option value="Resolved" <?php echo $status == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="system_id">Affected System</label>
                    <select id="system_id" name="system_id">
                        <option value="">All Systems</option>
                        <?php foreach ($all_systems as $system): ?>
                            <option value="<?php echo escape($system['id']); ?>" <?php echo $system_id == $system['id'] ? 'selected' : ''; ?>>
                                <?php echo escape($system['name']); ?> (<?php echo escape($system['type']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_from">Date From</label>
                    <input type="date" id="date_from" name="date_from" value="<?php echo escape($date_from); ?>" placeholder="mm/dd/yyyy">
                </div>
                
                <div class="form-group">
                    <label for="date_to">Date To</label>
                    <input type="date" id="date_to" name="date_to" value="<?php echo escape($date_to); ?>" placeholder="mm/dd/yyyy">
                </div>
            </div>
            <div class="modal-footer">
                <a href="incidents.php" class="btn btn-secondary">Clear</a>
                <button type="submit" class="btn">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
