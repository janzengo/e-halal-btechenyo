<?php
    include 'includes/session.php';

    if(isset($_POST['add'])){
        $description = $_POST['description'];
        $max_vote = $_POST['max_vote'];

        $sql = "SELECT priority FROM positions ORDER BY priority DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Retrieve username and role from admin table
        $admin_sql = "SELECT username, role FROM admin WHERE id = ?";
        $stmt = $conn->prepare($admin_sql);
        $stmt->bind_param("i", $_SESSION['admin']);
        $stmt->execute();
        $stmt->bind_result($username, $role);
        $stmt->fetch();
        $stmt->close();

        $priority = $row['priority'] + 1;

        $sql = "INSERT INTO positions (description, max_vote, priority) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $description, $max_vote, $priority);
        if($stmt->execute()){
            $_SESSION['success'] = 'Position added successfully';
            if ($max_vote == 1) {
                log_action($conn, $username, $_SESSION['role'], "Added new $description position with $max_vote maximum vote");
            } else {
                log_action($conn, $username, $_SESSION['role'], "Added new $description position with $max_vote maximum votes");
            }
        }
        else{
            $_SESSION['error'] = $conn->error;
            // Log the error action
            log_action($conn, $username, $_SESSION['role'], "Error adding position: $conn->error");
        }
    }
    else{
        $_SESSION['error'] = 'Fill up add form first';
        
        // Log the error action
        log_action($conn, $username, $_SESSION['role'], "Attempted to add position without filling up the form");
    }

    header('location: positions.php');

    function log_action($conn, $username, $role, $details) {
        $sql = "INSERT INTO logs (timestamp, username, role, details) VALUES (NOW(), ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $role, $details);
        $stmt->execute();
        $stmt->close();
    }