<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../../init.php';
require_once __DIR__ . '/../../../../classes/Database.php';
require_once __DIR__ . '/../../../../classes/CustomSessionHandler.php';
require_once __DIR__ . '/../../../classes/View.php';
require_once __DIR__ . '/../../../classes/Admin.php';
require_once __DIR__ . '/../../../classes/Ballot.php';
require_once __DIR__ . '/../slugify.php';

try {
    // Initialize classes
    $admin = Admin::getInstance();
    $ballot = AdminBallot::getInstance();
    
    // Check if admin is logged in
    if (!$admin->isLoggedIn()) {
        echo json_encode(['error' => true, 'message' => 'Unauthorized access']);
        exit();
    }

    // Get all positions
    $positions = $ballot->getAllPositions();

    // Check if positions exist
    if (empty($positions)) {
        echo json_encode(['error' => true, 'message' => 'No positions found']);
        exit();
    }

    // If positions exist, render the ballot
    $output = $ballot->renderAdminBallot();
    $ballot->reorderPositions();

    echo json_encode($output);

} catch (Exception $e) {
    // Log error
    error_log("Error in ballot_fetch.php: " . $e->getMessage());
    
    // Return error as JSON
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 