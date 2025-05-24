<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error logging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_log("AdminController.php started execution");

require_once __DIR__ . '/../../classes/Admin.php';
require_once __DIR__ . '/../../classes/Logger.php';
require_once __DIR__ . '/../../../classes/Database.php';

// Check if admin is logged in
$admin = Admin::getInstance();
if (!$admin->isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Unauthorized access']);
    exit();
}

// Initialize database and logger
$db = Database::getInstance();
$logger = AdminLogger::getInstance();

// Define paths
$base_path = dirname(dirname(dirname(__DIR__))); // Get to root e-halal directory
define('UPLOAD_DIR', 'assets/images/administrators'); // Remove administrator prefix
define('UPLOAD_PATH', $base_path . '/administrator/' . UPLOAD_DIR);
define('DEFAULT_PHOTO', 'assets/images/profile.jpg');

error_log("Document Root: " . $base_path);
error_log("Relative Path: " . UPLOAD_DIR);
error_log("Upload Path: " . UPLOAD_PATH);

// Create upload directory if it doesn't exist
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0777, true);
}

// Function to process and save image
function processImage($file, $role, $lastname, $firstname, $oldPhoto = null) {
    global $base_path;
    
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return $oldPhoto ?: DEFAULT_PHOTO;
    }

    // Validate file upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed');
    }

    // Validate file type
    $allowed = ['jpg', 'jpeg', 'png'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        throw new Exception('Invalid file format. Only JPG, JPEG & PNG files are allowed.');
    }

    // Generate new filename
    $newFilename = strtolower($role) . '_' . strtolower($lastname) . '_' . strtolower($firstname) . '.' . $ext;
    
    // Set paths
    $targetPath = UPLOAD_PATH . '/' . $newFilename;
    $dbPath = UPLOAD_DIR . '/' . $newFilename; // This will be stored in DB without administrator prefix

    // Copy file directly
    if (!copy($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to save file');
    }

    // Set permissions
    chmod($targetPath, 0644);

    // Delete old photo if exists
    if ($oldPhoto && $oldPhoto !== DEFAULT_PHOTO) {
        // Remove any duplicate administrator prefix from old photo path
        $oldPhoto = preg_replace('#^administrator/#', '', $oldPhoto);
        $oldPath = $base_path . '/administrator/' . $oldPhoto;
        if (file_exists($oldPath)) {
            @unlink($oldPath);
        }
    }

    return $dbPath;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['error' => false, 'message' => ''];
    
    try {
        // Verify current password
        if (!isset($_POST['curr_password'])) {
            throw new Exception('Current password is required');
        }

        $adminData = $admin->getAdminData();
        
        // Get the current password from the database
        $sql = "SELECT password FROM admin WHERE id = ?";
        $stmt = $db->getConnection()->prepare($sql);
        $stmt->bind_param("i", $adminData['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Admin account not found');
        }
        
        $row = $result->fetch_assoc();
        
        if (!password_verify($_POST['curr_password'], $row['password'])) {
            throw new Exception('Current password is incorrect');
        }

        // Process required fields
        if (!isset($_POST['username'], $_POST['firstname'], $_POST['lastname'])) {
            throw new Exception('Missing required fields');
        }

        // Validate email
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        if ($adminData['role'] === 'head' && empty($email)) {
            throw new Exception('Email is required for electoral head');
        }
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Process photo upload
        $photo = $adminData['photo'] ?: DEFAULT_PHOTO;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $photo = processImage(
                $_FILES['photo'],
                $adminData['role'],
                $_POST['lastname'],
                $_POST['firstname'],
                $adminData['photo']
            );
        }

        // Handle password change
        $newPassword = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
        if (!empty($newPassword)) {
            if (!$admin->updatePassword($_POST['curr_password'], $newPassword)) {
                throw new Exception('Failed to update password');
            }
        }

        // Update admin data
        if (!$admin->updateProfile(
            $_POST['username'],
            $_POST['firstname'],
            $_POST['lastname'],
            $email,
            $photo
        )) {
            throw new Exception('Failed to update profile');
        }
        
        $logger->logAdminAction(
            $admin->getUsername(),
            $admin->getRole(),
            "Updated admin profile"
        );
        
        $_SESSION['success'] = 'Profile updated successfully';

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        $logger->logAdminAction(
            $admin->getUsername(),
            $admin->getRole(),
            "Error in profile update: {$e->getMessage()}"
        );
    }

    // Handle redirect
    if (isset($_GET['return']) && !empty($_GET['return'])) {
        $returnPage = $_GET['return'];
        if (strpos($returnPage, '../') === false && strpos($returnPage, 'http') === false) {
            header('Location: ../../pages/' . $returnPage);
            exit();
        }
    }
    
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
    header('Location: ../../pages/home.php');
    exit();
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Invalid request method'
    ]);
    exit();
}
