<?php
session_start();
require_once __DIR__ . '/../../../../classes/Candidate.php';
require_once __DIR__ . '/../../../../classes/Admin.php';
require_once __DIR__ . '/../../../../classes/Logger.php';

// Check if admin is logged in
$admin = Admin::getInstance();
if (!$admin->isLoggedIn()) {
    echo json_encode([
        'error' => true,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

$candidate = Candidate::getInstance();
$logger = Logger::getInstance();

// Set the upload directory
$uploadDir = __DIR__ . '/../../../../assets/images/';
$defaultPhoto = 'profile.jpg'; // Default profile image

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'add':
            addCandidate();
            break;
        case 'edit':
            editCandidate();
            break;
        case 'delete':
            deleteCandidate();
            break;
        case 'get':
            getCandidate();
            break;
        default:
            echo json_encode([
                'error' => true,
                'message' => 'Invalid action'
            ]);
            break;
    }
}

/**
 * Add a new candidate
 */
function addCandidate() {
    global $candidate, $logger, $uploadDir, $admin, $defaultPhoto;
    
    // Check if required fields are set
    if (!isset($_POST['firstname']) || !isset($_POST['lastname']) || !isset($_POST['position_id']) || !isset($_POST['platform'])) {
        echo json_encode([
            'error' => true,
            'message' => 'All fields are required'
        ]);
        return;
    }
    
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $position_id = $_POST['position_id'];
    $platform = $_POST['platform'];
    $photo = '';
    
    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK && !empty($_FILES['photo']['name'])) {
        $photoName = time() . '_' . basename($_FILES['photo']['name']);
        $targetFile = $uploadDir . $photoName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // Check if image file is a valid image
        $check = getimagesize($_FILES['photo']['tmp_name']);
        if ($check === false) {
            echo json_encode([
                'error' => true,
                'message' => 'File is not an image'
            ]);
            return;
        }
        
        // Check file size (max 5MB)
        if ($_FILES['photo']['size'] > 5000000) {
            echo json_encode([
                'error' => true,
                'message' => 'File is too large. Maximum size is 5MB'
            ]);
            return;
        }
        
        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo json_encode([
                'error' => true,
                'message' => 'Only JPG, JPEG, PNG & GIF files are allowed'
            ]);
            return;
        }
        
        // Upload the file
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $photo = $photoName;
        } else {
            echo json_encode([
                'error' => true,
                'message' => 'Failed to upload image'
            ]);
            return;
        }
    }
    
    // Add candidate to database
    $result = $candidate->addCandidate($firstname, $lastname, $position_id, $platform, $photo);
    
    if ($result) {
        // Log the action
        $logger->generateLog(
            'superadmin',
            date('Y-m-d H:i:s'),
            $admin->getAdminId(),
            'Added candidate: ' . $firstname . ' ' . $lastname
        );
        
        echo json_encode([
            'error' => false,
            'message' => 'Candidate added successfully'
        ]);
    } else {
        echo json_encode([
            'error' => true,
            'message' => 'Failed to add candidate'
        ]);
    }
}

/**
 * Edit an existing candidate
 */
function editCandidate() {
    global $candidate, $logger, $uploadDir, $admin, $defaultPhoto;
    
    // Check if required fields are set
    if (!isset($_POST['id']) || !isset($_POST['firstname']) || !isset($_POST['lastname']) || !isset($_POST['position_id']) || !isset($_POST['platform'])) {
        echo json_encode([
            'error' => true,
            'message' => 'All fields are required'
        ]);
        return;
    }
    
    $id = $_POST['id'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $position_id = $_POST['position_id'];
    $platform = $_POST['platform'];
    $photo = null;
    
    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK && !empty($_FILES['photo']['name'])) {
        $photoName = time() . '_' . basename($_FILES['photo']['name']);
        $targetFile = $uploadDir . $photoName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // Check if image file is a valid image
        $check = getimagesize($_FILES['photo']['tmp_name']);
        if ($check === false) {
            echo json_encode([
                'error' => true,
                'message' => 'File is not an image'
            ]);
            return;
        }
        
        // Check file size (max 5MB)
        if ($_FILES['photo']['size'] > 5000000) {
            echo json_encode([
                'error' => true,
                'message' => 'File is too large. Maximum size is 5MB'
            ]);
            return;
        }
        
        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo json_encode([
                'error' => true,
                'message' => 'Only JPG, JPEG, PNG & GIF files are allowed'
            ]);
            return;
        }
        
        // Upload the file
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $photo = $photoName;
            
            // Get the old photo to delete it
            $candidateData = $candidate->getCandidate($id);
            if ($candidateData && !empty($candidateData['photo'])) {
                $oldPhoto = $uploadDir . $candidateData['photo'];
                if (file_exists($oldPhoto) && basename($oldPhoto) !== $defaultPhoto) {
                    unlink($oldPhoto);
                }
            }
        } else {
            echo json_encode([
                'error' => true,
                'message' => 'Failed to upload image'
            ]);
            return;
        }
    }
    
    // Update candidate in database
    $result = $candidate->updateCandidate($id, $firstname, $lastname, $position_id, $platform, $photo);
    
    if ($result) {
        // Log the action
        $logger->generateLog(
            'superadmin',
            date('Y-m-d H:i:s'),
            $admin->getAdminId(),
            'Updated candidate: ' . $firstname . ' ' . $lastname
        );
        
        echo json_encode([
            'error' => false,
            'message' => 'Candidate updated successfully'
        ]);
    } else {
        echo json_encode([
            'error' => true,
            'message' => 'Failed to update candidate'
        ]);
    }
}

/**
 * Delete a candidate
 */
function deleteCandidate() {
    global $candidate, $logger, $uploadDir, $admin, $defaultPhoto;
    
    // Check if ID is set
    if (!isset($_POST['id'])) {
        echo json_encode([
            'error' => true,
            'message' => 'Candidate ID is required'
        ]);
        return;
    }
    
    $id = $_POST['id'];
    
    // Get candidate data to delete photo if exists
    $candidateData = $candidate->getCandidate($id);
    
    // Delete candidate from database
    $result = $candidate->deleteCandidate($id);
    
    if ($result) {
        // Delete photo if exists
        if ($candidateData && !empty($candidateData['photo'])) {
            $photo = $uploadDir . $candidateData['photo'];
            if (file_exists($photo) && basename($photo) !== $defaultPhoto) {
                unlink($photo);
            }
        }
        
        // Log the action
        $logger->generateLog(
            'superadmin',
            date('Y-m-d H:i:s'),
            $admin->getAdminId(),
            'Deleted candidate: ' . $candidateData['firstname'] . ' ' . $candidateData['lastname']
        );
        
        echo json_encode([
            'error' => false,
            'message' => 'Candidate deleted successfully'
        ]);
    } else {
        echo json_encode([
            'error' => true,
            'message' => 'Failed to delete candidate'
        ]);
    }
}

/**
 * Get candidate data
 */
function getCandidate() {
    global $candidate;
    
    // Check if ID is set
    if (!isset($_POST['id'])) {
        echo json_encode([
            'error' => true,
            'message' => 'Candidate ID is required'
        ]);
        return;
    }
    
    $id = $_POST['id'];
    
    // Get candidate data
    $candidateData = $candidate->getCandidate($id);
    
    if ($candidateData) {
        echo json_encode([
            'error' => false,
            'data' => $candidateData
        ]);
    } else {
        echo json_encode([
            'error' => true,
            'message' => 'Candidate not found'
        ]);
    }
}
?>