<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../classes/Course.php';
require_once __DIR__ . '/../../../../classes/Logger.php';
require_once __DIR__ . '/../../../../classes/Admin.php';
require_once __DIR__ . '/../../../../classes/Elections.php';

// Set JSON header early to ensure proper content type
header('Content-Type: application/json');

// Check if admin is logged in
$admin = Admin::getInstance();
if (!$admin->isLoggedIn()) {
    echo json_encode(['error' => true, 'message' => 'Unauthorized access']);
    exit();
}

// Initialize classes
$course = Course::getInstance();
$logger = AdminLogger::getInstance();
$election = Elections::getInstance();

// Check if election is active
if ($election->isModificationLocked()) {
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
                if (!isset($_POST['description']) || empty(trim($_POST['description']))) {
                    throw new Exception('Course description is required');
                }

                $result = $course->addCourse(trim($_POST['description']));
                
                if (!$result) {
                    throw new Exception('Failed to add course. The description might already be taken.');
                }
                
                $logger->logAdminAction(
                    $admin->getUsername(),
                    $admin->getRole(),
                    "Added course: {$_POST['description']}"
                );
                
                $response['message'] = 'Course added successfully';
                break;

            case 'edit':
                if (!isset($_POST['id'], $_POST['description']) || empty(trim($_POST['description']))) {
                    throw new Exception('Missing required fields');
                }

                // Get old course data for logging
                $oldCourse = $course->getCourse($_POST['id']);
                if (!$oldCourse) {
                    throw new Exception('Course not found');
                }

                $result = $course->updateCourse($_POST['id'], trim($_POST['description']));
                
                if (!$result) {
                    throw new Exception('Failed to update course. The description might already be taken.');
                }
                
                $logger->logAdminAction(
                    $admin->getUsername(),
                    $admin->getRole(),
                    "Updated course: {$oldCourse['description']} to {$_POST['description']}"
                );
                
                $response['message'] = 'Course updated successfully';
                break;

            case 'delete':
                if (!isset($_POST['id'])) {
                    throw new Exception('Course ID not provided');
                }

                // Get course data for logging before deletion
                $courseData = $course->getCourse($_POST['id']);
                if (!$courseData) {
                    throw new Exception('Course not found');
                }

                $result = $course->deleteCourse($_POST['id']);
                
                if ($result) {
                    $logger->logAdminAction(
                        $admin->getUsername(),
                        $admin->getRole(),
                        "Deleted course: {$courseData['description']}"
                    );
                    $response['message'] = 'Course deleted successfully';
                } else {
                    throw new Exception('Cannot delete course with existing voters');
                }
                break;

            case 'get':
                if (!isset($_POST['id'])) {
                    throw new Exception('Course ID not provided');
                }

                $courseData = $course->getCourse($_POST['id']);
                if (!$courseData) {
                    throw new Exception('Course not found');
                }

                $response['data'] = $courseData;
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
            "Error in course management: {$e->getMessage()}"
        );
    }

    // Send JSON response
    echo json_encode($response);
    exit();
} else {
    // Handle non-POST requests
    echo json_encode([
        'error' => true,
        'message' => 'Invalid request method'
    ]);
    exit();
} 