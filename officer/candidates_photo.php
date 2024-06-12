<?php
include 'includes/session.php';

if (isset($_POST['upload'])) {
    $id = $_POST['id'];
    $filename = $_FILES['photo']['name'];
    
    // Check if file is uploaded
    if (!empty($filename)) {
        $target_dir = '../images/';
        $target_file = $target_dir . basename($_FILES["photo"]["name"]);
        
        // Move uploaded file to the target directory
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $sql = "UPDATE candidates SET photo = ? WHERE id = ?";
            
            // Prepare and execute the statement
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $filename, $id);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Photo updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update photo';
            }
        } else {
            $_SESSION['error'] = 'Failed to upload file';
        }
    } else {
        $_SESSION['error'] = 'No file selected';
    }
} else {
    $_SESSION['error'] = 'Select candidate to update photo first';
}

header('location: candidates.php');