<?php
/**
 * Form Validation Helper Functions
 */

/**
 * Validate incident type
 * @param string $type The incident type to validate
 * @return array ['valid' => bool, 'error' => string]
 */
function validateIncidentType($type) {
    $type = trim($type ?? '');
    
    if (empty($type)) {
        return ['valid' => false, 'error' => 'Incident Type is required.'];
    }
    
    if (strlen($type) < 3) {
        return ['valid' => false, 'error' => 'Incident Type must be at least 3 characters.'];
    }
    
    if (strlen($type) > 255) {
        return ['valid' => false, 'error' => 'Incident Type must not exceed 255 characters.'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Validate date and time
 * @param string $dateTime The datetime to validate
 * @return array ['valid' => bool, 'error' => string]
 */
function validateDateTime($dateTime) {
    $dateTime = trim($dateTime ?? '');
    
    if (empty($dateTime)) {
        return ['valid' => false, 'error' => 'Date & Time is required.'];
    }
    
    // Check valid datetime format
    $dateObj = DateTime::createFromFormat('Y-m-d\TH:i', $dateTime);
    if (!$dateObj || $dateObj->format('Y-m-d\TH:i') !== $dateTime) {
        return ['valid' => false, 'error' => 'Invalid Date & Time format.'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Validate affected system ID
 * @param int $systemId The system ID to validate
 * @param PDO $conn Database connection
 * @return array ['valid' => bool, 'error' => string]
 */
function validateAffectedSystemId($systemId, $conn) {
    $systemId = intval($systemId ?? 0);
    
    if ($systemId <= 0) {
        return ['valid' => false, 'error' => 'Please select a valid Affected System.'];
    }
    
    // Verify system exists
    $stmt = $conn->prepare("SELECT id FROM systems WHERE id = ?");
    $stmt->execute([$systemId]);
    if (!$stmt->fetch()) {
        return ['valid' => false, 'error' => 'Selected system does not exist.'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Validate severity level
 * @param string $severity The severity to validate
 * @return array ['valid' => bool, 'error' => string]
 */
function validateSeverity($severity) {
    $severity = trim($severity ?? '');
    $validLevels = ['Low', 'Medium', 'High', 'Critical'];
    
    if (empty($severity)) {
        return ['valid' => false, 'error' => 'Severity is required.'];
    }
    
    if (!in_array($severity, $validLevels)) {
        return ['valid' => false, 'error' => 'Invalid Severity value.'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Validate incident status
 * @param string $status The status to validate
 * @return array ['valid' => bool, 'error' => string]
 */
function validateIncidentStatus($status) {
    $status = trim($status ?? '');
    $validStatuses = ['Detected', 'Investigating', 'Resolved'];
    
    if (empty($status)) {
        return ['valid' => false, 'error' => 'Status is required.'];
    }
    
    if (!in_array($status, $validStatuses)) {
        return ['valid' => false, 'error' => 'Invalid Status value.'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Validate resolution notes (optional)
 * @param string $notes The notes to validate
 * @return array ['valid' => bool, 'error' => string]
 */
function validateResolutionNotes($notes) {
    $notes = trim($notes ?? '');
    
    if (strlen($notes) > 2000) {
        return ['valid' => false, 'error' => 'Resolution Notes must not exceed 2000 characters.'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Validate system name
 * @param string $name The system name to validate
 * @return array ['valid' => bool, 'error' => string]
 */
function validateSystemName($name) {
    $name = trim($name ?? '');
    
    if (empty($name)) {
        return ['valid' => false, 'error' => 'System Name is required.'];
    }
    
    if (strlen($name) < 2) {
        return ['valid' => false, 'error' => 'System Name must be at least 2 characters.'];
    }
    
    if (strlen($name) > 255) {
        return ['valid' => false, 'error' => 'System Name must not exceed 255 characters.'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Validate system type
 * @param string $type The system type to validate
 * @return array ['valid' => bool, 'error' => string]
 */
function validateSystemType($type) {
    $type = trim($type ?? '');
    $validTypes = ['Server', 'Application', 'Database', 'Firewall', 'Mail Gateway'];
    
    if (empty($type)) {
        return ['valid' => false, 'error' => 'System Type is required.'];
    }
    
    if (!in_array($type, $validTypes)) {
        return ['valid' => false, 'error' => 'Invalid System Type value.'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Validate system description (optional)
 * @param string $description The description to validate
 * @return array ['valid' => bool, 'error' => string]
 */
function validateSystemDescription($description) {
    $description = trim($description ?? '');
    
    if (strlen($description) > 1000) {
        return ['valid' => false, 'error' => 'Description must not exceed 1000 characters.'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Validate all incident form data at once
 * @param array $data The form data
 * @param PDO $conn Database connection
 * @return array ['valid' => bool, 'errors' => array of error strings]
 */
function validateIncidentForm($data, $conn) {
    $errors = [];
    
    $typeResult = validateIncidentType($data['incident_type'] ?? '');
    if (!$typeResult['valid']) $errors[] = $typeResult['error'];
    
    $dateResult = validateDateTime($data['date_time'] ?? '');
    if (!$dateResult['valid']) $errors[] = $dateResult['error'];
    
    $systemResult = validateAffectedSystemId($data['affected_system_id'] ?? 0, $conn);
    if (!$systemResult['valid']) $errors[] = $systemResult['error'];
    
    $severityResult = validateSeverity($data['severity'] ?? '');
    if (!$severityResult['valid']) $errors[] = $severityResult['error'];
    
    $statusResult = validateIncidentStatus($data['status'] ?? '');
    if (!$statusResult['valid']) $errors[] = $statusResult['error'];
    
    $notesResult = validateResolutionNotes($data['resolution_notes'] ?? '');
    if (!$notesResult['valid']) $errors[] = $notesResult['error'];
    
    return [
        'valid' => count($errors) === 0,
        'errors' => $errors
    ];
}

/**
 * Validate all system form data at once
 * @param array $data The form data
 * @return array ['valid' => bool, 'errors' => array of error strings]
 */
function validateSystemForm($data) {
    $errors = [];
    
    $nameResult = validateSystemName($data['name'] ?? '');
    if (!$nameResult['valid']) $errors[] = $nameResult['error'];
    
    $typeResult = validateSystemType($data['type'] ?? '');
    if (!$typeResult['valid']) $errors[] = $typeResult['error'];
    
    $descResult = validateSystemDescription($data['description'] ?? '');
    if (!$descResult['valid']) $errors[] = $descResult['error'];
    
    return [
        'valid' => count($errors) === 0,
        'errors' => $errors
    ];
}
?>
