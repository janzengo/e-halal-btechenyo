<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../classes/Admin.php';
require_once __DIR__ . '/../../../../classes/Logger.php';
require_once __DIR__ . '/../../../../classes/Elections.php';

// Check if admin is logged in
$admin = Admin::getInstance();
if (!$admin->isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Unauthorized access']);
    exit();
}

// Initialize classes
$logger = AdminLogger::getInstance();
$election = Elections::getInstance();

// Check if election is active
if ($election->isModificationLocked()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Modifications are not allowed while election is active']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['error' => false, 'message' => ''];
    
    try {
        // Determine if this is an AJAX request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (!isset($_POST['action'])) {
            throw new Exception('Action not specified');
        }

        switch ($_POST['action']) {
            case 'add':
                if (!isset($_POST['firstname'], $_POST['lastname'], $_POST['username'], $_POST['gender'])) {
                    throw new Exception('Missing required fields');
                }

                // Check if username already exists
                $check = $admin->checkUsername($_POST['username']);
                if ($check) {
                    throw new Exception('Username already exists');
                }

                // Validate email if provided
                $email = isset($_POST['email']) ? trim($_POST['email']) : null;
                if ($email !== null && $email !== '') {
                    if (!$admin->validateEmail($email)) {
                        throw new Exception('Invalid email format');
                    }
                    if ($admin->checkEmail($email)) {
                        throw new Exception('Email already exists');
                    }
                }

                // Generate random password if not provided
                $password = isset($_POST['password']) && $_POST['password'] !== '' ? $_POST['password'] : bin2hex(random_bytes(4));

                $result = $admin->addOfficer(
                    $_POST['firstname'],
                    $_POST['lastname'],
                    $_POST['username'],
                    $password,
                    $_POST['gender'],
                    $email
                );
                
                if (!$result) {
                    throw new Exception('Failed to add officer');
                }
                
                $logger->logAdminAction(
                    $admin->getUsername(),
                    $admin->getRole(),
                    "Added officer: {$_POST['firstname']} {$_POST['lastname']}" . 
                    ($email ? " with email: {$email}" : "")
                );

                // Send password email if email is provided
                if ($email) {
                    require_once __DIR__ . '/../../../../classes/OfficerMailer.php';
                    $officerMailer = new OfficerMailer();
                    $sendResult = $officerMailer->sendPasswordEmail($email, $_POST['firstname'] . ' ' . $_POST['lastname'], $_POST['username'], $password);
                    if ($sendResult['success']) {
                        $response['message'] = 'Officer added successfully. Credentials sent to officer email.';
                    } else {
                        $response['message'] = 'Officer added, but failed to send credentials email: ' . $sendResult['message'];
                    }
                } else {
                    $response['message'] = 'Officer added successfully.';
                }
                break;

            case 'edit':
                if (!isset($_POST['id'], $_POST['firstname'], $_POST['lastname'], $_POST['username'], $_POST['gender'])) {
                    throw new Exception('Missing required fields');
                }

                // Get old officer data for logging
                $oldOfficer = $admin->getOfficer($_POST['id']);
                if (!$oldOfficer) {
                    throw new Exception('Officer not found');
                }

                // Check if username exists for other officers
                $check = $admin->checkUsername($_POST['username'], $_POST['id']);
                if ($check) {
                    throw new Exception('Username already exists');
                }

                // Validate email if provided
                $email = isset($_POST['email']) ? trim($_POST['email']) : null;
                if ($email !== null && $email !== '') {
                    if (!$admin->validateEmail($email)) {
                        throw new Exception('Invalid email format');
                    }
                    if ($admin->checkEmail($email, $_POST['id'])) {
                        throw new Exception('Email already exists');
                    }
                }

                $result = $admin->updateOfficer(
                    $_POST['id'],
                    $_POST['firstname'],
                    $_POST['lastname'],
                    $_POST['username'],
                    $_POST['password'],
                    $_POST['gender'],
                    $email
                );
                
                if (!$result) {
                    throw new Exception('Failed to update officer');
                }
                
                $logger->logAdminAction(
                    $admin->getUsername(),
                    $admin->getRole(),
                    "Updated officer: {$oldOfficer['firstname']} {$oldOfficer['lastname']}" .
                    ($email !== $oldOfficer['email'] ? " (email changed to: " . ($email ?: "none") . ")" : "")
                );
                $response['message'] = 'Officer updated successfully';
                break;

            case 'delete':
                if (!isset($_POST['id'])) {
                    throw new Exception('Officer ID not provided');
                }

                // Get officer data for logging before deletion
                $officerData = $admin->getOfficer($_POST['id']);
                if (!$officerData) {
                    throw new Exception('Officer not found');
                }

                $result = $admin->deleteOfficer($_POST['id']);
                
                if ($result) {
                    $logger->logAdminAction(
                        $admin->getUsername(),
                        $admin->getRole(),
                        "Deleted officer: {$officerData['firstname']} {$officerData['lastname']}" .
                        ($officerData['email'] ? " (email: {$officerData['email']})" : "")
                    );
                    $response['message'] = 'Officer deleted successfully';
                } else {
                    throw new Exception('Failed to delete officer');
                }
                break;

            case 'get':
                if (!isset($_POST['id'])) {
                    throw new Exception('Officer ID not provided');
                }

                $officerData = $admin->getOfficer($_POST['id']);
                if (!$officerData) {
                    throw new Exception('Officer not found');
                }

                $response['data'] = $officerData;
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
            "Error in officer management: {$e->getMessage()}"
        );
    }

    // Send response
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