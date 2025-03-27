<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../../classes/Candidate.php';
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
$candidate = Candidate::getInstance();
$logger = AdminLogger::getInstance();
$election = Elections::getInstance();

// Check if election is active
if ($election->isModificationLocked()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Modifications are not allowed while election is active']);
    exit();
}

// Define upload path
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/assets/images/candidates/');
define('DB_PATH', 'assets/images/candidates/');
define('DEFAULT_PHOTO', 'assets/images/profile.jpg');

// Function to process and save image
function processImage($file, $lastname, $firstname, $oldPhoto = null) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return $oldPhoto ?: DEFAULT_PHOTO;
    }

    $allowed = ['jpg', 'jpeg', 'png'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        throw new Exception('Invalid file format. Only JPG, JPEG & PNG files are allowed.');
    }

    // Create upload directory if it doesn't exist
    if (!is_dir(UPLOAD_PATH)) {
        if (!mkdir(UPLOAD_PATH, 0755, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }

    if (!is_writable(UPLOAD_PATH)) {
        throw new Exception('Upload directory is not writable');
    }

    // Generate filename using lastname_firstname format
    $newFilename = 'candidate_' . strtolower($lastname) . '_' . strtolower($firstname) . '.' . $ext;
    $targetPath = UPLOAD_PATH . $newFilename;
    $dbPath = DB_PATH . $newFilename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Delete old photo if it exists and is not the default
    if ($oldPhoto && $oldPhoto !== DEFAULT_PHOTO) {
        $oldPath = UPLOAD_PATH . basename($oldPhoto);
        if (file_exists($oldPath)) {
            @unlink($oldPath);
        }
    }

    return $dbPath;
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
                if (!isset($_POST['firstname'], $_POST['lastname'], $_POST['position_id'], $_POST['platform'])) {
                    throw new Exception('Missing required fields');
                }

                // Process photo upload
                $photo = DEFAULT_PHOTO;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                    $photo = processImage($_FILES['photo'], $_POST['lastname'], $_POST['firstname']);
                }

                // Get partylist_id if set, otherwise null
                $partylist_id = isset($_POST['partylist_id']) && !empty($_POST['partylist_id']) ? $_POST['partylist_id'] : null;

                $result = $candidate->addCandidate(
                    $_POST['firstname'],
                    $_POST['lastname'],
                    $_POST['position_id'],
                    $_POST['platform'],
                    $photo,
                    $partylist_id
                );
                
                if (!$result) {
                    throw new Exception('Failed to add candidate');
                }
                
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
                
                // Process photo upload
                $photo = $oldCandidate['photo'] ?: DEFAULT_PHOTO;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                    $photo = processImage($_FILES['photo'], $_POST['lastname'], $_POST['firstname'], $oldCandidate['photo']);
                }

                // Get partylist_id if set, otherwise null
                $partylist_id = isset($_POST['partylist_id']) && !empty($_POST['partylist_id']) ? $_POST['partylist_id'] : null;

                $result = $candidate->updateCandidate(
                    $_POST['id'],
                    $_POST['firstname'],
                    $_POST['lastname'],
                    $_POST['position_id'],
                    $_POST['platform'],
                    $photo,
                    $partylist_id
                );
                
                if (!$result) {
                    throw new Exception('Failed to update candidate');
                }
                
                $logger->logAdminAction(
                    $admin->getUsername(),
                    $admin->getRole(),
                    "Updated candidate: {$oldCandidate['firstname']} {$oldCandidate['lastname']}"
                );
                $response['message'] = 'Candidate updated successfully';
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
                if ($candidateData['photo'] && $candidateData['photo'] !== DEFAULT_PHOTO) {
                    $photoPath = UPLOAD_PATH . basename($candidateData['photo']);
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

    // Send response
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    } else {
        // For form submissions, redirect with message
        if ($response['error']) {
            $_SESSION['error'] = $response['message'];
        } else {
            $_SESSION['success'] = $response['message'];
        }
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
} else {
    // Handle non-POST requests
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Invalid request method'
    ]);
    exit();
}
