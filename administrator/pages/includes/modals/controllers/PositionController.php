<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../classes/Position.php';
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
$position = Position::getInstance();
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
        if (!isset($_POST['action'])) {
            throw new Exception('Action not specified');
        }

        switch ($_POST['action']) {
            case 'add':
                if (empty($_POST['description']) || empty($_POST['max_vote'])) {
                    throw new Exception('Description and maximum vote are required');
                }
                
                if (!is_numeric($_POST['max_vote'])) {
                    throw new Exception('Maximum vote must be a number');
                }
                
                $description = $_POST['description'];
                $max_vote = (int)$_POST['max_vote'];
                
                $result = $position->addPosition($description, $max_vote);
                
                $logger->logAdminAction(
                    $admin->getUsername(),
                    $admin->getRole(),
                    "Added new position: $description with auto-assigned priority {$result['priority']}"
                );
                
                $response['message'] = 'Position added successfully';
                $response['data'] = $result;
                break;

            case 'edit':
                if (empty($_POST['id']) || empty($_POST['description']) || empty($_POST['max_vote'])) {
                    throw new Exception('All required fields must be filled');
                }
                
                if (!is_numeric($_POST['id']) || !is_numeric($_POST['max_vote'])) {
                    throw new Exception('ID and maximum vote must be numbers');
                }
                
                $id = (int)$_POST['id'];
                $description = $_POST['description'];
                $max_vote = (int)$_POST['max_vote'];
                
                // Get existing position data
                $oldPosition = $position->getPosition($id);
                if (!$oldPosition) {
                    throw new Exception('Position not found');
                }
                
                // Keep the existing priority
                $priority = $oldPosition['priority'];
                
                // Update position
                $result = $position->updatePosition($id, $description, $max_vote, $priority);
                
                $logger->logAdminAction(
                    $admin->getUsername(),
                    $admin->getRole(),
                    "Updated position: {$oldPosition['description']} to $description (priority unchanged)"
                );
                
                $response['message'] = 'Position updated successfully';
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
                    $logger->logAdminAction(
                        $admin->getUsername(),
                        $admin->getRole(),
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
                    $logger->logAdminAction(
                        $admin->getUsername(),
                        $admin->getRole(),
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
        $logger->logAdminAction(
            $admin->getUsername(),
            $admin->getRole(),
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