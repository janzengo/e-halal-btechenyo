<?php
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../classes/Database.php';
require_once __DIR__ . '/../../../../classes/CustomSessionHandler.php';
require_once __DIR__ . '/../../../classes/Admin.php';
require_once __DIR__ . '/../../../classes/Elections.php';
require_once __DIR__ . '/../../../classes/Logger.php';

// Set JSON content type
header('Content-Type: application/json');

$admin = Admin::getInstance();
$elections = Elections::getInstance();
$logger = AdminLogger::getInstance();
$db = Database::getInstance()->getConnection();

// Access control: Only Electoral Head can access
if (!$admin->isLoggedIn() || !$admin->isHead()) {
    http_response_code(403);
    echo json_encode([
        'error' => true,
        'message' => 'Access Denied. This action is restricted to Electoral Heads only.'
    ]);
    exit();
}

// Check current election status
$current_status = $elections->getCurrentStatus();
if ($current_status !== 'setup') {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => 'Setup can only be completed from setup phase.'
    ]);
    exit();
}

try {
    // Verify required settings are complete
    $sql = "SELECT election_name, end_time FROM election_status WHERE status = 'setup'";
    $result = $db->query($sql);
    $settings = $result ? $result->fetch_assoc() : null;

    if (empty($settings['election_name']) || empty($settings['end_time'])) {
        http_response_code(400);
        echo json_encode([
            'error' => true,
            'message' => 'Election name and end time must be set before completing setup.'
        ]);
        exit();
    }

    // Update election status to pending
    $sql = "UPDATE election_status SET 
            status = 'pending',
            last_status_change = NOW()
            WHERE status = 'setup'";
    
    if (!$db->query($sql)) {
        throw new Exception('Database error: ' . $db->error);
    }

    if ($db->affected_rows === 0) {
        throw new Exception('No changes were made to the election status.');
    }

    // Log the action
    $logger->logAdminAction(
        $admin->getUsername(),
        $admin->getRole(),
        'Completed election setup: ' . $settings['election_name']
    );

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Setup completed successfully. Election is now pending.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error completing setup: ' . $e->getMessage()
    ]);
}
?> 