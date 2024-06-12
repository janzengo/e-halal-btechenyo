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
    } else {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin'] = $row['id'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'superadmin') {
                $_SESSION['info'] = 'Officer has elevated privileges.';
                header('Location: /e-halal/admin/home.php');
				exit();
            } else if ($row['role'] == 'officer') {
                header('Location: /e-halal/officer/home.php');
            }
            exit();
        } else {
            $_SESSION['error'] = 'Incorrect password';
        }
    }
} else {
    $_SESSION['error'] = 'Input admin credentials first';
}

header('Location: index.php');
exit();
?>
