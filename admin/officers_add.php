<?php
include 'includes/session.php';

if (isset($_POST['add'])) {
    $defaultProfilePicture = 'profile.jpg';
    $username = $_POST['username'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $date = date('Y-m-d');
    $gender = $_POST['gender'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'officer';
    $filename = $defaultProfilePicture;
    $origin = $_POST['origin'];  // Capture the origin value

    // Check if username already exists
    $check_sql = "SELECT * FROM admin WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_query = $check_stmt->get_result();

    if ($check_query->num_rows > 0) {
        $_SESSION['error'] = 'Officer already exists';
    } else {
        // Use prepared statements for insertion
        $sql = "INSERT INTO admin (username, password, firstname, lastname, photo, created_on, role, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $username, $password, $firstname, $lastname, $filename, $date, $role, $gender);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Officer added successfully';
        } else {
            $_SESSION['error'] = $stmt->error;
        }
    }
} else {
    $_SESSION['error'] = 'Fill up add form first';
}

// Redirect based on the origin value
if (isset($origin) && $origin === 'pre_election') {
    header('location: pre_election.php');
} else {
    header('location: officers.php');
}
