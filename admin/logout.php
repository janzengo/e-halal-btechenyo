<?php
session_start();
include 'includes/conn.php';

if (isset($_SESSION['admin'])) {
    $admin_id = $_SESSION['admin'];
    // Retrieve username and role from admin table
    $sql = "SELECT username, role FROM admin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($username, $role);
    $stmt->fetch();
    $stmt->close();

    // Log the logout action
    log_action($conn, $username, $role, 'Logged out');
}

session_destroy();
header('location: index.php');

function log_action($conn, $username, $role, $details) {
    $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $role, $details);
    $stmt->execute();
    $stmt->close();
}
?>
