<?php
require_once __DIR__ . '/../../classes/Admin.php';
require_once __DIR__ . '/../../classes/Logger.php';

header('Content-Type: application/json');

if(isset($_POST['status'])) {
    $admin = Admin::getInstance();
    $logger = AdminLogger::getInstance();
    
    $status = $_POST['status'];
    $valid_statuses = array('on', 'off', 'paused');
    
    if(!in_array($status, $valid_statuses)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid status'));
        exit();
    }
    
    try {
        // Get current election
        $current = $admin->getCurrentElection();
        if(!$current) {
            echo json_encode(array('success' => false, 'message' => 'No active election found'));
            exit();
        }
        
        // Validate status change
        if($status == 'on') {
            if(strtotime($current['start_time']) > time()) {
                echo json_encode(array('success' => false, 'message' => 'Cannot start election before scheduled start time'));
                exit();
            }
            if(strtotime($current['end_time']) < time()) {
                echo json_encode(array('success' => false, 'message' => 'Cannot start election after end time'));
                exit();
            }
        }
        
        // Update status
        if($admin->updateStatus($status)) {
            $logger->log('admin', 'Updated election status to: ' . $status);
            echo json_encode(array('success' => true));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Error updating election status'));
        }
        
    } catch(Exception $e) {
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }
    
} else {
    echo json_encode(array('success' => false, 'message' => 'Invalid request'));
} 