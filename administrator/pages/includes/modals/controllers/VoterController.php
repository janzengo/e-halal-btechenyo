<?php
// Suppress all notices and warnings to prevent them from breaking JSON output
error_reporting(0);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../../../classes/Voter.php';
require_once __DIR__ . '/../../../../classes/Logger.php';
require_once __DIR__ . '/../../../../classes/Admin.php';
require_once __DIR__ . '/../../../../classes/Elections.php';

// Check if admin is logged in
$admin = Admin::getInstance();
if (!$admin->isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Unauthorized access']);
    exit();
}

// Initialize classes
$voter = Voter::getInstance();
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
    // Set the content type to JSON
    header('Content-Type: application/json');
    
    $response = ['error' => false, 'message' => ''];
    
    try {
        if (!isset($_POST['action'])) {
            throw new Exception('Action not specified');
        }

        switch ($_POST['action']) {
            case 'add':
                if (!isset($_POST['student_number'], $_POST['course_id'])) {
                    throw new Exception('Missing required fields');
                }

                $result = $voter->addVoter(
                    $_POST['student_number'],
                    (int)$_POST['course_id']
                );
                
                if ($result) {
                    $logger->logAdminAction(
                        $admin->getUsername(),
                        $admin->getRole(),
                        "Added voter: {$_POST['student_number']}"
                    );
                    $response['message'] = 'Voter added successfully.';
                } else {
                    throw new Exception('Student number already exists. Please use a different student number.');
                }
                break;

            case 'edit':
                if (!isset($_POST['id'], $_POST['student_number'], $_POST['course_id'])) {
                    throw new Exception('Missing required fields');
                }

                // Get old voter data for logging
                $oldVoter = $voter->getVoter($_POST['id']);
                if (!$oldVoter) {
                    throw new Exception('Voter not found');
                }
                
                $result = $voter->updateVoter(
                    (int)$_POST['id'],
                    $_POST['student_number'],
                    (int)$_POST['course_id']
                );
                
                if ($result) {
                    $logger->logAdminAction(
                        $admin->getUsername(),
                        $admin->getRole(),
                        "Updated voter from '{$oldVoter['student_number']}' to '{$_POST['student_number']}'"
                    );
                    $response['message'] = 'Voter updated successfully';
                } else {
                    throw new Exception('Student number already exists or no changes were made.');
                }
                break;

            case 'delete':
                if (!isset($_POST['id'])) {
                    throw new Exception('Voter ID not provided');
                }

                // Get voter data for logging before deletion
                $voterData = $voter->getVoter($_POST['id']);
                if (!$voterData) {
                    throw new Exception('Voter not found');
                }

                $result = $voter->deleteVoter($_POST['id']);
                
                if ($result) {
                    $logger->logAdminAction(
                        $admin->getUsername(),
                        $admin->getRole(),
                        "Deleted voter: {$voterData['student_number']}"
                    );
                    $response['message'] = 'Voter deleted successfully';
                } else {
                    throw new Exception('Failed to delete voter');
                }
                break;

            case 'get':
                if (!isset($_POST['id'])) {
                    throw new Exception('Voter ID not provided');
                }

                $voterData = $voter->getVoter($_POST['id']);
                if (!$voterData) {
                    throw new Exception('Voter not found');
                }

                $response['data'] = $voterData;
                break;

            case 'bulk_delete':
                if (!isset($_POST['ids']) || !is_array($_POST['ids']) || empty($_POST['ids'])) {
                    echo json_encode(['error' => true, 'message' => 'No voters selected for deletion']);
                    exit;
                }

                try {
                    // Get voter details for logging before deletion
                    $deletedVoters = [];
                    foreach ($_POST['ids'] as $id) {
                        $voterData = $voter->getVoter($id);
                        if ($voterData && !$voterData['has_voted']) {
                            $deletedVoters[] = $voterData;
                        }
                    }

                    if (empty($deletedVoters)) {
                        echo json_encode([
                            'error' => true,
                            'message' => 'No eligible voters found for deletion. Selected voters may have already voted.'
                        ]);
                        exit;
                    }

                    // Perform bulk delete
                    $deletedCount = $voter->bulkDeleteVoters($_POST['ids']);
                    
                    if ($deletedCount > 0) {
                        // Log the bulk deletion
                        foreach ($deletedVoters as $voterData) {
                            $logger->logAdminAction(
                                $admin->getUsername(),
                                $admin->getRole(),
                                "Deleted voter: {$voterData['student_number']} (Bulk Delete)"
                            );
                        }
                        
                        echo json_encode([
                            'error' => false,
                            'message' => "$deletedCount voter(s) deleted successfully"
                        ]);
                    } else {
                        echo json_encode([
                            'error' => true,
                            'message' => 'No voters were deleted. They may have already voted.'
                        ]);
                    }
                } catch (Exception $e) {
                    error_log("Error in bulk delete voters: " . $e->getMessage());
                    echo json_encode([
                        'error' => true,
                        'message' => 'An error occurred while deleting voters. Please try again.'
                    ]);
                }
                exit;
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
            "Error in voter management: {$e->getMessage()}"
        );
    }

    // Send JSON response
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