<?php
require_once '../includes/header.php';

$conn = getDBConnection();

// Get filter parameters
$severity = $_GET['severity'] ?? '';
$status = $_GET['status'] ?? '';
$system_id = intval($_GET['system_id'] ?? 0);
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');

// Get dashboard statistics
$stats = [];

// Total incidents
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM incidents");
$stmt->execute();
$stats['total'] = $stmt->fetch()['total'];

// Detected incidents
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM incidents WHERE status = 'Detected'");
$stmt->execute();
$stats['detected'] = $stmt->fetch()['count'];

// Investigating incidents
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM incidents WHERE status = 'Investigating'");
$stmt->execute();
$stats['investigating'] = $stmt->fetch()['count'];

// Resolved incidents
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM incidents WHERE status = 'Resolved'");
$stmt->execute();
$stats['resolved'] = $stmt->fetch()['count'];

// Build query for recent incidents with filters
$query = "SELECT i.*, s.name as system_name 
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

$query .= " ORDER BY i.date_time DESC LIMIT 5";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$recent_incidents = $stmt->fetchAll();

// Get all systems for dropdown
$stmt = $conn->prepare("SELECT id, name, type FROM systems ORDER BY name");
$stmt->execute();
$all_systems = $stmt->fetchAll();
?>

<h2 class="page-title">Dashboard</h2>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Incidents</h3>
        <div class="number" id="total-incidents"><?php echo escape($stats['total']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Detected</h3>
        <div class="number" id="detected-count"><?php echo escape($stats['detected']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Investigating</h3>
        <div class="number" id="investigating-count"><?php echo escape($stats['investigating']); ?></div>
    </div>
    <div class="stat-card">
        <h3>Resolved</h3>
        <div class="number" id="resolved-count"><?php echo escape($stats['resolved']); ?></div>
    </div>
</div>

<div style="margin-top: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="margin: 0;">Recent Incidents</h3>
        <button onclick="openFilterModal()" class="btn btn-secondary">Filter</button>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Date & Time</th>
                    <th>Affected System</th>
                    <th>Severity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recent_incidents) > 0): ?>
                    <?php foreach ($recent_incidents as $incident): ?>
                        <tr>
                            <td><?php echo escape($incident['incident_type']); ?></td>
                            <td><?php echo escape(date('Y-m-d H:i', strtotime($incident['date_time']))); ?></td>
                            <td><?php echo escape($incident['system_name']); ?></td>
                            <td><span class="badge <?php echo getSeverityColor($incident['severity']); ?>"><?php echo escape($incident['severity']); ?></span></td>
                            <td><span class="badge <?php echo getStatusColor($incident['status']); ?>"><?php echo escape($incident['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="empty-state">No incidents found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div style="margin-top: 15px;">
        <a href="incidents.php" class="btn">View All Incidents</a>
    </div>
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
                <a href="index.php" class="btn btn-secondary">Clear</a>
                <button type="submit" class="btn">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
