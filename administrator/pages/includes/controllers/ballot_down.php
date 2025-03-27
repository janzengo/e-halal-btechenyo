<?php
require_once __DIR__ . '/../../../classes/View.php';
require_once __DIR__ . '/../../../classes/Admin.php';
require_once __DIR__ . '/../../../classes/Ballot.php';

try {
    $ballot = AdminBallot::getInstance();
    
    if(isset($_POST['id'])) {
        $result = $ballot->movePositionDown($_POST['id']);
        if($result) {
            echo json_encode(['error' => false, 'message' => 'Position moved down successfully']);
        } else {
            echo json_encode(['error' => true, 'message' => 'Failed to move position down']);
        }
    } else {
        echo json_encode(['error' => true, 'message' => 'No position ID provided']);
    }
} catch (Exception $e) {
    error_log("Error in ballot_down.php: " . $e->getMessage());
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
} 