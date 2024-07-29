<?php
include 'includes/session.php';

if (isset($_POST['upload'])) {
    $id = $_POST['id'];
    $filename = $_FILES['photo']['name'];
    
    // Get candidate's full name
    $candidate_info = get_candidate_info($conn, $id);
    $fullname = $candidate_info['firstname'] . ' ' . $candidate_info['lastname'];
    
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
                // Log the successful action
                log_action($conn, $_SESSION['admin'], $_SESSION['role'], "Updated photo of candidate $fullname (ID: $id)");
            } else {
                $_SESSION['error'] = 'Failed to update photo';
                // Log the error action
                log_action($conn, $_SESSION['admin'], $_SESSION['role'], "Error updating photo of candidate $fullname (ID: $id)");
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

function log_action($conn, $username, $role, $details) {
    $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $role, $details);
    $stmt->execute();
    $stmt->close();
}

function get_candidate_info($conn, $id) {
    $sql = "SELECT firstname, lastname FROM candidates WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row;
}