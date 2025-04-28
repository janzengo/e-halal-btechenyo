<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../classes/Admin.php';
require_once __DIR__ . '/../../../../classes/Logger.php';

// Check if admin is logged in
$admin = Admin::getInstance();
if (!$admin->isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Unauthorized access']);
    exit();
}

// Initialize logger
$logger = AdminLogger::getInstance();

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['error' => false, 'message' => ''];
    
    try {
        if (!isset($_POST['action'])) {
            throw new Exception('Action not specified');
        }

        switch ($_POST['action']) {
            case 'update_profile':
                if (!isset($_POST['username'], $_POST['firstname'], $_POST['lastname'])) {
                    throw new Exception('Missing required fields');
                }

                // Handle file upload if present
                $photo = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $allowed = ['jpg', 'jpeg', 'png'];
                    $filename = $_FILES['photo']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (!in_array($ext, $allowed)) {
                        throw new Exception('Invalid file type. Only JPG, JPEG, and PNG files are allowed.');
                    }

                    // Generate unique filename
                    $new_filename = uniqid() . '.' . $ext;
                    $upload_path = __DIR__ . '/../../../../../uploads/admin/' . $new_filename;
                    
                    // Create directory if it doesn't exist
                    if (!file_exists(dirname($upload_path))) {
                        mkdir(dirname($upload_path), 0777, true);
                    }

                    if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                        $photo = 'uploads/admin/' . $new_filename;
                    } else {
                        throw new Exception('Failed to upload photo');
                    }
                }

                // Update profile
                $result = $admin->updateProfile(
                    $_POST['username'],
                    $_POST['firstname'],
                    $_POST['lastname'],
                    $photo
                );

                if (!$result) {
                    throw new Exception('Failed to update profile');
                }

                // Log the action
                $logger->logAdminAction(
                    $admin->getUsername(),
                    $admin->getRole(),
                    'Updated profile information'
                );

                $response['message'] = 'Profile updated successfully';
                break;

            case 'update_password':
                if (!isset($_POST['current_password'], $_POST['new_password'])) {
                    throw new Exception('Missing required fields');
                }

                $result = $admin->updatePassword(
                    $_POST['current_password'],
                    $_POST['new_password']
                );

                if (!$result) {
                    throw new Exception('Failed to update password');
                }

                // Log the action
                $logger->logAdminAction(
                    $admin->getUsername(),
                    $admin->getRole(),
                    'Updated password'
                );

                $response['message'] = 'Password updated successfully';
                break;

            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        $response['error'] = true;
        $response['message'] = $e->getMessage();
        
        // Log the error
        $logger->logAdminAction(
            $admin->getUsername(),
            $admin->getRole(),
            "Error in profile management: {$e->getMessage()}"
        );
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
} else {
    // Handle non-POST requests
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Invalid request method'
    ]);
    exit();
} 