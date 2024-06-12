<?php
include 'includes/session.php';

if (isset($_POST['edit'])) {
    $username = $_POST['username'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $gender = $_POST['gender'];
    $password = $_POST['password'];
    $id = $_POST['id']; // Assuming you have the ID of the admin you want to edit

    // Fetch the current password
    $sql = "SELECT password FROM admin WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // Assuming 'id' is an integer
    $stmt->execute();
    $query = $stmt->get_result();
    $row = $query->fetch_assoc();

    if ($password == $row['password']) {
        $password = $row['password'];
    } else {
        $password = password_hash($password, PASSWORD_DEFAULT);
    }

    // Update the admin details
    $sql = "UPDATE admin SET firstname = ?, lastname = ?, password = ?, username = ?, gender = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $firstname, $lastname, $password, $username, $gender, $id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Officer updated successfully';
    } else {
        $_SESSION['error'] = $stmt->error;
    }

    $stmt->close();
} else {
    $_SESSION['error'] = 'Fill up edit form first';
}

header('location: officers.php');
?>
