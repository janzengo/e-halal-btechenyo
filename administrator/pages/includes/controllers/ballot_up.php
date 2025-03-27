<?php
// Prevent any output before JSON
ob_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display_errors to prevent HTML errors in output

// Set JSON content type
header('Content-Type: application/json');

require_once __DIR__ . '/../../../classes/View.php';
require_once __DIR__ . '/../../../classes/Admin.php';
require_once __DIR__ . '/../../../classes/Ballot.php';

try {
    // Clean any previous output
    ob_clean();
    
    include_once __DIR__ . '/../../includes/session.php';
    
    if (!class_exists('AdminBallot')) {
        throw new Exception("AdminBallot class not found");
    }
    
    $ballot = AdminBallot::getInstance();
    
    if(isset($_POST['id'])) {
        $result = $ballot->movePositionUp($_POST['id']);
        if($result) {
            echo json_encode(['error' => false, 'message' => 'Position moved up successfully']);
        } else {
            echo json_encode(['error' => true, 'message' => 'Failed to move position up']);
        }
    } else {
        echo json_encode(['error' => true, 'message' => 'No position ID provided']);
    }
} catch (Exception $e) {
    // Clean any output buffer contents
    ob_clean();
    error_log("Error in ballot_up.php: " . $e->getMessage());
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}

// End and flush the output buffer
ob_end_flush(); 