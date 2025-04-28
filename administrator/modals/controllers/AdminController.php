<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

// Define upload path
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/assets/images/administrators/');
define('DB_PATH', 'assets/images/administrators/');
define('DEFAULT_PHOTO', 'assets/images/profile.jpg');

// Function to process and save image
function processImage($file, $role, $lastname, $firstname, $oldPhoto = null) {
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

    // Generate filename using role_lastname_firstname format
    $newFilename = strtolower($role) . '_' . strtolower($lastname) . '_' . strtolower($firstname) . '.' . $ext;
    $targetPath = UPLOAD_PATH . $newFilename;
    $dbPath = DB_PATH . $newFilename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Delete old photo if it exists and is not the default
    if ($oldPhoto && $oldPhoto !== DEFAULT_PHOTO) {
        $oldPath = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/' . $oldPhoto;
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
        // Verify current password
        if (!isset($_POST['curr_password'])) {
            throw new Exception('Current password is required');
        }

        $adminData = $admin->getAdminData();
        
        // Get the current password from the database directly
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

        // Validate email for head admin
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        if ($adminData['role'] === 'head' && empty($email)) {
            throw new Exception('Email is required for electoral head');
        }

        // Validate email format if provided
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

        // Handle password change if requested
        $newPassword = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
        if (!empty($newPassword)) {
            // Update password
            $passwordResult = $admin->updatePassword($_POST['curr_password'], $newPassword);
            if (!$passwordResult) {
                throw new Exception('Failed to update password');
            }
        }

        // Update admin data
        $result = $admin->updateProfile(
            $_POST['username'],
            $_POST['firstname'],
            $_POST['lastname'],
            $email,
            $photo
        );
        
        if (!$result) {
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
        
        // Log the error
        $logger->logAdminAction(
            $admin->getUsername(),
            $admin->getRole(),
            "Error in profile update: {$e->getMessage()}"
        );
    }

    // Redirect back to the referring page
    if (isset($_GET['return']) && !empty($_GET['return'])) {
        // Use the return parameter from the URL
        $returnPage = $_GET['return'];
        // Ensure we're redirecting to a page within the admin section
        if (strpos($returnPage, '../') === false && strpos($returnPage, 'http') === false) {
            header('Location: ../../pages/' . $returnPage);
            exit();
        }
    }
    
    // Fallback: Use HTTP_REFERER if available
    if (isset($_SERVER['HTTP_REFERER'])) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
    // Final fallback: Redirect to home
    header('Location: ../../pages/home.php');
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
