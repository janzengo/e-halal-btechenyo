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

// Validate end time based on status
if ($data['status'] !== Elections::STATUS_SETUP) {
    if (!isset($_POST['end_time']) || empty($_POST['end_time'])) {
        $errors[] = "End time is required for non-setup status";
    }
}

// Add end time to data array if it exists
if (isset($_POST['end_time']) && !empty($_POST['end_time'])) {
    try {
        $data['end_time'] = (new DateTime($_POST['end_time']))->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        $errors[] = "Invalid end time format";
    }
}

// If status is being set to completed, require and validate password
if ($data['status'] === Elections::STATUS_COMPLETED) {
    error_log('[Election Complete] Password received: ' . (isset($_POST['curr_password']) ? $_POST['curr_password'] : 'NOT SET'));
    if (!isset($_POST['curr_password']) || empty($_POST['curr_password'])) {
        $errors[] = "Password is required to complete the election.";
    } else {
        // Validate password for the current admin
        $adminInstance = Admin::getInstance();
        $adminData = $adminInstance->getAdminData();
        error_log('[Election Complete] Admin ID: ' . (isset($adminData['id']) ? $adminData['id'] : 'NOT SET'));
        $db = $adminInstance->getDbInstance();
        $sql = "SELECT password FROM admin WHERE id = ?";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->bind_param("i", $adminData['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $errors[] = "Admin account not found.";
        } else {
            $row = $result->fetch_assoc();
            error_log('[Election Complete] DB Hash: ' . $row['password']);
            $verifyResult = password_verify($_POST['curr_password'], $row['password']);
            error_log('[Election Complete] Verify result: ' . ($verifyResult ? 'MATCH' : 'NO MATCH'));
            if (!$verifyResult) {
                $errors[] = "Election Controller: Password did not match.";
            }
        }
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
        // If election is now completed, redirect to completed interface
        if ($data['status'] === Elections::STATUS_COMPLETED) {
            header("Location: " . BASE_URL . "administrator/completed");
            exit();
        }
    } else {
        $_SESSION['error'] = $result['message'];
    }

} catch (Exception $e) {
    $_SESSION['error'] = "Error configuring election: " . $e->getMessage();
}

header("Location: " . BASE_URL . "administrator/configure");
exit();