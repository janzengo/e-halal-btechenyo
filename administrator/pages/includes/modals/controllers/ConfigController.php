<?php
require_once __DIR__ . '/../../../../classes/Admin.php';
require_once __DIR__ . '/../../../../classes/Elections.php';
require_once __DIR__ . '/../../../../classes/CustomSessionHandler.php';

// Initialize classes
$admin = Admin::getInstance();
$elections = Elections::getInstance();
$session = CustomSessionHandler::getInstance();

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the action
$action = $_POST['action'] ?? '';

// Handle complete election action
if ($action === 'complete_election') {
    try {
        // Verify admin is logged in
        if (!$admin->isLoggedIn()) {
            throw new Exception('You must be logged in to perform this action');
        }

        // Get the password from POST data
        $password = $_POST['password'] ?? '';
        if (empty($password)) {
            throw new Exception('Password is required');
        }

        // Verify the password
        if (!$admin->verifyPassword($password)) {
            throw new Exception('Invalid password');
        }

        // Complete the election
        if (!$elections->completeElection()) {
            throw new Exception('Failed to complete the election');
        }

        // Log the action
        error_log("Election completed by admin ID: " . $admin->getAdminId());

        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Election has been completed successfully'
        ]);

    } catch (Exception $e) {
        // Log the error
        error_log("Error completing election: " . $e->getMessage());
        
        // Return error response
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action specified'
    ]);
}
