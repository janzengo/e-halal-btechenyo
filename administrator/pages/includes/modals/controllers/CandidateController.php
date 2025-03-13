<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../classes/Candidate.php';
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
$candidate = Candidate::getInstance();
$logger = AdminLogger::getInstance();

// At the top of the file, after session_start()
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/assets/images/');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['error' => false, 'message' => ''];
    
    try {
        if (!isset($_POST['action'])) {
            throw new Exception('Action not specified');
        }

        switch ($_POST['action']) {
            case 'add':
                if (!isset($_POST['firstname'], $_POST['lastname'], $_POST['position_id'], $_POST['platform'])) {
                    throw new Exception('Missing required fields');
                }

                // Handle photo upload
                $photo = '';
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png'];
                    $filename = $_FILES['photo']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (!in_array($ext, $allowed)) {
                        throw new Exception('Invalid file format. Only JPG, JPEG & PNG files are allowed.');
                    }
                    
                    try {
                        if (!is_dir(UPLOAD_PATH)) {
                            if (!mkdir(UPLOAD_PATH, 0755, true)) {
                                throw new Exception('Failed to create upload directory');
                            }
                        }
                        
                        if (!is_writable(UPLOAD_PATH)) {
                            throw new Exception('Upload directory is not writable');
                        }
                        
                        $photo = time() . '_' . $filename;
                        if (!move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_PATH . $photo)) {
                            throw new Exception('Failed to move uploaded file');
                        }
                    } catch (Exception $e) {
                        error_log('File upload error: ' . $e->getMessage());
                        throw new Exception('Error handling file upload: ' . $e->getMessage());
                    }
                }

                $result = $candidate->addCandidate(
                    $_POST['firstname'],
                    $_POST['lastname'],
                    $_POST['position_id'],
                    $_POST['platform'],
                    $photo
                );
                
                $logger->logAdminAction(
                    $admin->getUsername(),
                    $admin->getRole(),
                    "Added candidate: {$_POST['firstname']} {$_POST['lastname']}"
                );
                
                $response['message'] = 'Candidate added successfully';
                break;

            case 'edit':
                if (!isset($_POST['id'], $_POST['firstname'], $_POST['lastname'], $_POST['position_id'], $_POST['platform'])) {
                    throw new Exception('Missing required fields');
                }

                // Get old candidate data for logging
                $oldCandidate = $candidate->getCandidate($_POST['id']);
                if (!$oldCandidate) {
                    throw new Exception('Candidate not found');
                }
                
                // Handle photo upload
                $photo = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png'];
                    $filename = $_FILES['photo']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (!in_array($ext, $allowed)) {
                        throw new Exception('Invalid file format. Only JPG, JPEG & PNG files are allowed.');
                    }
                    
                    try {
                        if (!is_dir(UPLOAD_PATH)) {
                            if (!mkdir(UPLOAD_PATH, 0755, true)) {
                                throw new Exception('Failed to create upload directory');
                            }
                        }
                        
                        if (!is_writable(UPLOAD_PATH)) {
                            throw new Exception('Upload directory is not writable');
                        }
                        
                        $photo = time() . '_' . $filename;
                        if (!move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_PATH . $photo)) {
                            throw new Exception('Failed to move uploaded file');
                        }
                    } catch (Exception $e) {
                        error_log('File upload error: ' . $e->getMessage());
                        throw new Exception('Error handling file upload: ' . $e->getMessage());
                    }
                    
                    // Delete old photo if it exists and is not the default
                    if ($oldCandidate && isset($oldCandidate['photo']) && 
                        $oldCandidate['photo'] && $oldCandidate['photo'] != 'profile.jpg') {
                        $photoPath = UPLOAD_PATH . $oldCandidate['photo'];
                        if (file_exists($photoPath)) {
                            @unlink($photoPath);
                        }
                    }
                }

                $result = $candidate->updateCandidate(
                    $_POST['id'],
                    $_POST['firstname'],
                    $_POST['lastname'],
                    $_POST['position_id'],
                    $_POST['platform'],
                    $photo
                );
                
                if ($result) {
                    $logger->logAdminAction(
                        $admin->getUsername(),
                        $admin->getRole(),
                        "Updated candidate: {$oldCandidate['firstname']} {$oldCandidate['lastname']}"
                    );
                    $response['message'] = 'Candidate updated successfully';
                } else {
                    throw new Exception('No changes made to candidate');
                }
                break;

            case 'delete':
                if (!isset($_POST['id'])) {
                    throw new Exception('Candidate ID not provided');
                }

                // Get candidate data for logging before deletion
                $candidateData = $candidate->getCandidate($_POST['id']);
                if (!$candidateData) {
                    throw new Exception('Candidate not found');
                }

                // Delete photo if it exists and is not the default
                if ($candidateData['photo'] && $candidateData['photo'] != 'profile.jpg') {
                    $photoPath = UPLOAD_PATH . $candidateData['photo'];
                    if (file_exists($photoPath)) {
                        @unlink($photoPath);
                    }
                }

                $result = $candidate->deleteCandidate($_POST['id']);
                
                if ($result) {
                    $logger->logAdminAction(
                        $admin->getUsername(),
                        $admin->getRole(),
                        "Deleted candidate: {$candidateData['firstname']} {$candidateData['lastname']}"
                    );
                    $response['message'] = 'Candidate deleted successfully';
                } else {
                    throw new Exception('Failed to delete candidate');
                }
                break;

            case 'get':
                if (!isset($_POST['id'])) {
                    throw new Exception('Candidate ID not provided');
                }

                $candidateData = $candidate->getCandidate($_POST['id']);
                if (!$candidateData) {
                    throw new Exception('Candidate not found');
                }

                $response['data'] = $candidateData;
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
            "Error in candidate management: {$e->getMessage()}"
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
