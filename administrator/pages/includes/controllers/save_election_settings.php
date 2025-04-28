<?php
require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../classes/Admin.php';
require_once __DIR__ . '/../../../classes/Elections.php';
require_once __DIR__ . '/../../../classes/Logger.php';

// Set JSON content type
header('Content-Type: application/json');

$admin = Admin::getInstance();
$elections = Elections::getInstance();
$logger = AdminLogger::getInstance();

// Access control: Only Electoral Head can access
if (!$admin->isLoggedIn() || !$admin->isHead()) {
    http_response_code(403);
    die(json_encode([
        'error' => 'Access Denied. This action is restricted to Electoral Heads only.'
    ]));
}

// Check current election status
$current_status = $elections->getCurrentStatus();
if ($current_status !== 'setup') {
    http_response_code(400);
    die(json_encode([
        'error' => 'Settings can only be saved during setup phase.'
    ]));
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate input
    $electionName = trim($_POST['election_name'] ?? '');
    $endTime = trim($_POST['end_time'] ?? '');

    if (empty($electionName) || empty($endTime)) {
        throw new Exception('Missing required fields');
        }

        // Validate end time is in the future
    $end_time = new DateTime($endTime);
        $now = new DateTime();
        if ($end_time <= $now) {
        http_response_code(400);
        die(json_encode([
            'error' => 'End time must be in the future.'
        ]));
        }

        // Update election settings
    if (!$elections->updateElectionSettings($electionName, $endTime)) {
        throw new Exception('Failed to update election settings');
        }

        // Log the action
        $logger->logAdminAction(
            $admin->getUsername(),
            $admin->getRole(),
        'Updated election settings: ' . $electionName
        );

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Election settings updated successfully'
    ]);
        
    } catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    }
?> 