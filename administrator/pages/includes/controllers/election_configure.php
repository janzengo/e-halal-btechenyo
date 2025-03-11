<?php
require_once __DIR__ . '/../../classes/Admin.php';
require_once __DIR__ . '/../../classes/Elections.php';
require_once __DIR__ . '/../../classes/Logger.php';

session_start();

if(isset($_POST['save'])) {
    $admin = Admin::getInstance();
    $election = Elections::getInstance();
    $logger = AdminLogger::getInstance();
    
    // Validate input
    $election_name = trim($_POST['election_name']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $status = $_POST['status'];
    
    if(empty($election_name)) {
        $_SESSION['error'] = 'Election title is required';
        header('Location: ../configure.php');
        exit();
    }
    
    if(strtotime($start_time) >= strtotime($end_time)) {
        $_SESSION['error'] = 'End time must be after start time';
        header('Location: ../configure.php');
        exit();
    }
    
    if(strtotime($start_time) < time() && $status == 'pending') {
        $_SESSION['error'] = 'Start time cannot be in the past for pending elections';
        header('Location: ../configure.php');
        exit();
    }
    
    try {
        // Check if there's an active election
        $current = $admin->getCurrentElection();
        if($current && $current['status'] != 'off') {
            $_SESSION['error'] = 'Cannot configure new election while another is active';
            header('Location: ../configure.php');
            exit();
        }
        
        // Configure the election
        $data = array(
            'election_name' => $election_name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'status' => $status
        );
        
        if($admin->configureElection($data)) {
            $logger->log('admin', 'Configured new election: ' . $election_name);
            $_SESSION['success'] = 'Election configured successfully';
        } else {
            $_SESSION['error'] = 'Error configuring election';
        }
        
    } catch(Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
} else {
    $_SESSION['error'] = 'Invalid request';
}

header('Location: ../configure.php');
exit(); 