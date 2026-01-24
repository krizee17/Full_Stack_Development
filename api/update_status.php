<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $incident_id = intval($_POST['incident_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';
    
    // Validate status
    $valid_statuses = ['Detected', 'Investigating', 'Resolved'];
    if (!in_array($new_status, $valid_statuses)) {
        $response['message'] = 'Invalid status';
        echo json_encode($response);
        exit;
    }
    
    if ($incident_id > 0) {
        $conn = getDBConnection();
        
        try {
            // Use prepared statement to prevent SQL injection
            $stmt = $conn->prepare("UPDATE incidents SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $incident_id]);
            
            $response['success'] = true;
            $response['message'] = 'Status updated successfully';
        } catch (PDOException $e) {
            $response['message'] = 'Error updating status: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Invalid incident ID';
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
