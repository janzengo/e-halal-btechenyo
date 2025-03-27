<?php
require_once __DIR__ . '/../../classes/Admin.php';
require_once __DIR__ . '/../../classes/Logger.php';
require_once __DIR__ . '/../../classes/Elections.php';

header('Content-Type: application/json');

if(isset($_POST['status'])) {
    $admin = Admin::getInstance();
    $logger = AdminLogger::getInstance();
    $elections = Elections::getInstance();
    
    $status = $_POST['status'];
    $valid_statuses = [
        Elections::STATUS_SETUP,
        Elections::STATUS_PENDING,
        Elections::STATUS_ACTIVE,
        Elections::STATUS_PAUSED,
        Elections::STATUS_COMPLETED
    ];
    
    if(!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }
    
    try {
        // Get current election
        $current = $elections->getCurrentElection();
        if(!$current) {
            echo json_encode(['success' => false, 'message' => 'No active election found']);
            exit();
        }
        
        // Validate status change
        if($status === Elections::STATUS_ACTIVE) {
            // Check if end time is set and valid
            if(empty($current['end_time'])) {
                echo json_encode(['success' => false, 'message' => 'End time must be set before activating the election']);
                exit();
            }
            
            if(strtotime($current['end_time']) < time()) {
                echo json_encode(['success' => false, 'message' => 'Cannot activate election after end time']);
                exit();
            }
        }
        
        // Validate status transition
        $validation = $elections->validateStatusChange($status, $current);
        if (!$validation['valid']) {
            echo json_encode(['success' => false, 'message' => $validation['message']]);
            exit();
        }
        
        // Update status
        if($elections->updateStatus($status)) {
            $logger->logAdminAction(
                $admin->getUsername(),
                $admin->getRole(),
                'Updated election status to: ' . $status
            );
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating election status']);
        }
        
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
} 