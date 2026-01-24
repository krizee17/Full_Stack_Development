<?php
require_once '../includes/header.php';

$conn = getDBConnection();

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

// Get recent incidents
$stmt = $conn->prepare("
    SELECT i.*, s.name as system_name 
    FROM incidents i 
    JOIN systems s ON i.affected_system_id = s.id 
    ORDER BY i.date_time DESC 
    LIMIT 5
");
$stmt->execute();
$recent_incidents = $stmt->fetchAll();
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

<!-- Quick Search -->
<div class="search-form" style="margin: 30px 0;">
    <h3 style="margin-bottom: 15px;">Quick Search</h3>
    <form method="GET" action="incidents.php">
        <div class="form-row">
            <div class="form-group">
                <label for="incident_type">Incident Type</label>
                <input type="text" id="incident_type" name="incident_type" placeholder="e.g., Malware, Phishing">
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="Detected">Detected</option>
                    <option value="Investigating">Investigating</option>
                    <option value="Resolved">Resolved</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="severity">Severity</label>
                <select id="severity" name="severity">
                    <option value="">All Severities</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                    <option value="Critical">Critical</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Search Incidents</button>
            <a href="incidents.php" class="btn btn-secondary">View All</a>
        </div>
    </form>
</div>

<div style="margin-top: 30px;">
    <h3 style="margin-bottom: 15px;">Recent Incidents</h3>
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

<?php require_once '../includes/footer.php'; ?>
