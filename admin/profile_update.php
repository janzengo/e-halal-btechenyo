<?php
include 'includes/session.php';

if (isset($_GET['return'])) {
    $return = $_GET['return'];
} else {
    $return = 'home.php';
}

if (isset($_POST['save'])) {
    $curr_password = $_POST['curr_password'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $photo = $_FILES['photo']['name'];

    // Prepare and execute a SELECT query to retrieve the user's data
    $sql = "SELECT * FROM admin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['admin']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Store old credentials for logging
    $old_username = $user['username'];
    $old_firstname = $user['firstname'];
    $old_lastname = $user['lastname'];
    $old_photo = $user['photo'];

    // Verify the current password
    if (password_verify($curr_password, $user['password'])) {
        // Check if a new photo is uploaded
        if (!empty($photo)) {
            move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $photo);
            $filename = $photo;
        } else {
            $filename = $user['photo'];
        }

        // Check if the new password matches the old one
        if ($password == $user['password']) {
            $password = $user['password'];
        } else {
            $password = password_hash($password, PASSWORD_DEFAULT);
        }

        // Prepare and execute the UPDATE query
        $sql = "UPDATE admin SET username = ?, password = ?, firstname = ?, lastname = ?, photo = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $username, $password, $firstname, $lastname, $filename, $user['id']);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Admin profile updated successfully';
            // Log the successful profile update with old and new credentials
            log_action($conn, $username, $user['role'], 
                "Updated profile from: Username: $old_username, Firstname: $old_firstname, Lastname: $old_lastname, Photo: $old_photo to: Username: $username, Firstname: $firstname, Lastname: $lastname, Photo: $filename"
            );
        } else {
            $_SESSION['error'] = $conn->error;
            // Log the error
            log_action($conn, $username, $user['role'], "Error updating profile: " . $conn->error);
        }
    } else {
        $_SESSION['error'] = 'Incorrect password';
        // Log the incorrect password attempt
        log_action($conn, $username, $user['role'], "Incorrect password attempt for profile update");
    }
} else {
    $_SESSION['error'] = 'Fill up required details first';
    // Log the attempt to update profile without filling the form
    log_action($conn, $_SESSION['admin'], $_SESSION['role'], "Attempted to update profile without filling up required details");
}

header('location:' . $return);

function log_action($conn, $username, $role, $details) {
    $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $role, $details);
    $stmt->execute();
    $stmt->close();
}
