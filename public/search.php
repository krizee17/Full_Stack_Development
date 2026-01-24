<?php
require_once '../includes/header.php';

$conn = getDBConnection();
$results = [];
$has_search = false;

// Handle search
if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty(array_filter($_GET))) {
    $has_search = true;
    
    $incident_type = trim($_GET['incident_type'] ?? '');
    $severity = $_GET['severity'] ?? '';
    $status = $_GET['status'] ?? '';
    $system_id = intval($_GET['system_id'] ?? 0);
    $date_from = trim($_GET['date_from'] ?? '');
    $date_to = trim($_GET['date_to'] ?? '');
    
    // Build query with prepared statements
    $query = "SELECT i.*, s.name as system_name, s.type as system_type 
              FROM incidents i 
              JOIN systems s ON i.affected_system_id = s.id 
              WHERE 1=1";
    
    $params = [];
    
    if (!empty($incident_type)) {
        $query .= " AND i.incident_type LIKE ?";
        $params[] = "%$incident_type%";
    }
    
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
    
    $query .= " ORDER BY i.date_time DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}

// Get all systems for dropdown
$stmt = $conn->prepare("SELECT id, name, type FROM systems ORDER BY name");
$stmt->execute();
$systems = $stmt->fetchAll();
?>

<h2 class="page-title">Search Incidents</h2>

<div class="search-form">
    <form method="GET" action="">
        <div class="form-row">
            <div class="form-group">
                <label for="incident_type">Incident Type</label>
                <input type="text" id="incident_type" name="incident_type" value="<?php echo escape($_GET['incident_type'] ?? ''); ?>" placeholder="e.g., Malware, Phishing">
            </div>
            
            <div class="form-group">
                <label for="severity">Severity</label>
                <select id="severity" name="severity">
                    <option value="">All Severities</option>
                    <option value="Low" <?php echo (isset($_GET['severity']) && $_GET['severity'] == 'Low') ? 'selected' : ''; ?>>Low</option>
                    <option value="Medium" <?php echo (isset($_GET['severity']) && $_GET['severity'] == 'Medium') ? 'selected' : ''; ?>>Medium</option>
                    <option value="High" <?php echo (isset($_GET['severity']) && $_GET['severity'] == 'High') ? 'selected' : ''; ?>>High</option>
                    <option value="Critical" <?php echo (isset($_GET['severity']) && $_GET['severity'] == 'Critical') ? 'selected' : ''; ?>>Critical</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="Detected" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Detected') ? 'selected' : ''; ?>>Detected</option>
                    <option value="Investigating" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Investigating') ? 'selected' : ''; ?>>Investigating</option>
                    <option value="Resolved" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="system_id">Affected System</label>
                <select id="system_id" name="system_id">
                    <option value="">All Systems</option>
                    <?php foreach ($systems as $system): ?>
                        <option value="<?php echo escape($system['id']); ?>" <?php echo (isset($_GET['system_id']) && $_GET['system_id'] == $system['id']) ? 'selected' : ''; ?>>
                            <?php echo escape($system['name']); ?> (<?php echo escape($system['type']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date_from">Date From</label>
                <input type="date" id="date_from" name="date_from" value="<?php echo escape($_GET['date_from'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="date_to">Date To</label>
                <input type="date" id="date_to" name="date_to" value="<?php echo escape($_GET['date_to'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Search</button>
            <a href="search.php" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>

<?php if ($has_search): ?>
    <h3 style="margin-bottom: 15px;">Search Results</h3>
    
    <?php if (count($results) > 0): ?>
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
                    <?php foreach ($results as $incident): ?>
                        <tr>
                            <td><?php echo escape($incident['id']); ?></td>
                            <td><?php echo escape($incident['incident_type']); ?></td>
                            <td><?php echo escape(date('Y-m-d H:i', strtotime($incident['date_time']))); ?></td>
                            <td><?php echo escape($incident['system_name']); ?> <small>(<?php echo escape($incident['system_type']); ?>)</small></td>
                            <td><span class="badge <?php echo getSeverityColor($incident['severity']); ?>"><?php echo escape($incident['severity']); ?></span></td>
                            <td><span class="badge <?php echo getStatusColor($incident['status']); ?>"><?php echo escape($incident['status']); ?></span></td>
                            <td class="action-buttons">
                                <a href="view_incident.php?id=<?php echo escape($incident['id']); ?>" class="btn btn-secondary" style="padding: 5px 12px; font-size: 14px;">View</a>
                                <a href="edit_incident.php?id=<?php echo escape($incident['id']); ?>" class="btn" style="padding: 5px 12px; font-size: 14px;">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>No incidents found matching your search criteria.</p>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
