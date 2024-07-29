<?php
session_start();
include 'includes/conn.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepared statement to prevent SQL injection
    $sql = "SELECT * FROM admin WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows < 1) {
        $_SESSION['error'] = 'Cannot find account with the username';
        log_action($conn, $username ?: 'undefined', 'unknown', 'Failed login attempt: username not found');
    } else {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin'] = $row['id'];
            $_SESSION['role'] = $row['role'];

            log_action($conn, $username, $row['role'], 'Successful login');

            if ($row['role'] == 'superadmin') {
                header('Location: home.php');
                exit();
            } else if ($row['role'] == 'officer') {
                $_SESSION['error'] = 'Officer does not have permission to access.';
                header('Location: /e-halal/officer/home.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Incorrect password';
            log_action($conn, $username, $row['role'], 'Failed login attempt: incorrect password');
        }
    }
} else {
    $_SESSION['error'] = 'Input admin credentials first';
    log_action($conn, 'undefined', 'unknown', 'Attempted login without credentials');
}

header('location: index.php');
exit();

function log_action($conn, $username, $role, $details) {
    $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $role, $details);
    $stmt->execute();
    $stmt->close();
}
