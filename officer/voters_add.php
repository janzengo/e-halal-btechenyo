<?php
include 'includes/session.php';

if (isset($_POST['add'])) {
    $defaultProfilePicture = 'profile.jpg';
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $course = $_POST['course'];
    $voters_id = $_POST['studentNumber'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $filename = $defaultProfilePicture;

    // Check if voters_id already exists using prepared statement
    $check_sql = "SELECT * FROM voters WHERE voters_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $voters_id);
    $check_stmt->execute();
    $check_query = $check_stmt->get_result();

    if ($check_query->num_rows > 0) {
        $_SESSION['error'] = 'Voter ID already exists';
    } else {
        // Use prepared statements for insertion
        $sql = "INSERT INTO voters (voters_id, course_id, password, firstname, lastname, photo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissss", $voters_id, $course, $password, $firstname, $lastname, $filename);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Voter added successfully';
        } else {
            $_SESSION['error'] = $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
} else {
    $_SESSION['error'] = 'Fill up add form first';
}

header('location: voters.php');
