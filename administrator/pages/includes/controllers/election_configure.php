<?php
session_start();
require_once __DIR__ . '/../../../classes/Elections.php';
require_once __DIR__ . '/../../../classes/Admin.php';

// Check if admin is logged in
$admin = Admin::getInstance();
if (!$admin->isLoggedIn()) {
    header("Location: ../../../login.php");
    exit();
}

// Initialize data array
$data = [];
$errors = [];

// Always require election name and status
if (!isset($_POST['election_name']) || empty($_POST['election_name'])) {
    $errors[] = "Election name is required";
}
if (!isset($_POST['status']) || empty($_POST['status'])) {
    $errors[] = "Status is required";
}

// Get the values we have
$data['election_name'] = $_POST['election_name'] ?? '';
$data['status'] = $_POST['status'] ?? '';

// If status is not pending, validate dates
if ($data['status'] !== 'pending') {
    if (!isset($_POST['start_time']) || empty($_POST['start_time'])) {
        $errors[] = "Start time is required for non-pending status";
    }
    if (!isset($_POST['end_time']) || empty($_POST['end_time'])) {
        $errors[] = "End time is required for non-pending status";
    }
}

// Add dates to data array if they exist
if (isset($_POST['start_time']) && !empty($_POST['start_time'])) {
    try {
        $data['start_time'] = (new DateTime($_POST['start_time']))->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        $errors[] = "Invalid start time format";
    }
}

if (isset($_POST['end_time']) && !empty($_POST['end_time'])) {
    try {
        $data['end_time'] = (new DateTime($_POST['end_time']))->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        $errors[] = "Invalid end time format";
    }
}

// If there are validation errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['error'] = implode(", ", $errors);
    header("Location: " . BASE_URL . "administrator/configure");
    exit();
}

try {
    // Add ID for update/insert
    $data['id'] = 1; // We always work with ID 1 for the current election

    // Configure election using singleton instance
    $elections = Elections::getInstance();
    $result = $elections->configureElection($data);

    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Error configuring election: " . $e->getMessage();
}

header("Location: " . BASE_URL . "administrator/configure");
exit(); 