<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db.php';

$conn = getDBConnection();

$stats = [];

// Total incidents
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM incidents");
$stmt->execute();
$stats['total_incidents'] = $stmt->fetch()['total'];

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

echo json_encode([
    'success' => true,
    'total_incidents' => $stats['total_incidents'],
    'detected' => $stats['detected'],
    'investigating' => $stats['investigating'],
    'resolved' => $stats['resolved']
]);
?>
