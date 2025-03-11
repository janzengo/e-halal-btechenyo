<?php
session_start();
require_once __DIR__ . '/../../../../classes/Position.php';
require_once __DIR__ . '/../../../../classes/Logger.php';
require_once __DIR__ . '/../../../../classes/Admin.php';

// Check if admin is logged in
$admin = Admin::getInstance();
if (!$admin->isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Unauthorized access']);
    exit();
}

// Initialize classes
$position = Position::getInstance();
$logger = Logger::getInstance();

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['error' => false, 'message' => ''];
    
    try {
        if (!isset($_POST['action'])) {
            throw new Exception('Action not specified');
        }

        switch ($_POST['action']) {
            case 'add':
                if (!isset($_POST['description'], $_POST['max_vote'])) {
                    throw new Exception('Missing required fields');
                }

                $result = $position->addPosition(
                    $_POST['description'],
                    (int)$_POST['max_vote']
                );
                
                $logger->generateLog(
                    'superadmin', 
                    date('Y-m-d H:i:s'),
                    $admin->getUsername(), 
                    "Added position: {$_POST['description']} with max vote: {$_POST['max_vote']}"
                );
                
                $response['message'] = 'Position added successfully';
                $response['data'] = $result;
                break;

            case 'edit':
                if (!isset($_POST['id'], $_POST['description'], $_POST['max_vote'])) {
                    throw new Exception('Missing required fields');
                }

                // Get old position data for logging
                $oldPosition = $position->getPosition($_POST['id']);
                
                $result = $position->updatePosition(
                    (int)$_POST['id'],
                    $_POST['description'],
                    (int)$_POST['max_vote']
                );
                
                if ($result) {
                    $logger->generateLog(
                        'superadmin',
                        date('Y-m-d H:i:s'),
                        $admin->getUsername(),
                        "Updated position from '{$oldPosition['description']}' to '{$_POST['description']}'"
                    );
                    $response['message'] = 'Position updated successfully';
                } else {
                    throw new Exception('No changes made to position');
                }
                break;

            case 'delete':
                if (!isset($_POST['id'])) {
                    throw new Exception('Position ID not provided');
                }

                // Get position data for logging before deletion
                $positionData = $position->getPosition($_POST['id']);
                if (!$positionData) {
                    throw new Exception('Position not found');
                }

                $result = $position->deletePosition($_POST['id']);
                
                if ($result) {
                    $logger->generateLog(
                        'superadmin',
                        date('Y-m-d H:i:s'),
                        $admin->getUsername(),
                        "Deleted position: {$positionData['description']}"
                    );
                    $response['message'] = 'Position deleted successfully';
                } else {
                    throw new Exception('Failed to delete position');
                }
                break;

            case 'reorder':
                if (!isset($_POST['positions']) || !is_array($_POST['positions'])) {
                    throw new Exception('Invalid position order data');
                }

                $result = $position->reorderPositions($_POST['positions']);
                
                if ($result) {
                    $logger->generateLog(
                        'superadmin',
                        date('Y-m-d H:i:s'),
                        $admin->getUsername(),
                        'Updated position priorities'
                    );
                    $response['message'] = 'Positions reordered successfully';
                } else {
                    throw new Exception('Failed to reorder positions');
                }
                break;

            case 'get':
                if (!isset($_POST['id'])) {
                    throw new Exception('Position ID not provided');
                }

                $positionData = $position->getPosition($_POST['id']);
                if (!$positionData) {
                    throw new Exception('Position not found');
                }

                $response['data'] = $positionData;
                break;

            default:
                throw new Exception('Invalid action');
        }
    } catch (Exception $e) {
        $response['error'] = true;
        $response['message'] = $e->getMessage();
        
        // Log the error
        $logger->generateLog(
            'superadmin',
            date('Y-m-d H:i:s'),
            $admin->getUsername(),
            "Error in position management: {$e->getMessage()}"
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