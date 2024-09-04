<?php
include 'includes/session.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];

    // Ensure status is properly sanitized
    $status = htmlspecialchars(strip_tags($status));

    if ($status == 'general') {
        $election_name = htmlspecialchars(strip_tags($_POST['election_name']));
        $end_time = htmlspecialchars(strip_tags($_POST['end_time']));

        // Normalize end_time to the format 'YYYY-MM-DD HH:MM:SS'
        $formatted_end_time = date('Y-m-d H:i:s', strtotime($end_time));

        // Check if election_name and end_time are not empty
        if (empty($election_name) || empty($formatted_end_time)) {
            $_SESSION['error'] = 'Election name and end time cannot be empty.';
            $_SESSION['general_config_complete'] = false;
            header('location: pre_election_positions.php');
            exit();
        }

        // Retrieve the latest election record
        $sql = "SELECT id, election_name, end_time FROM election_status ORDER BY id DESC LIMIT 1";
        $query = $conn->query($sql);

        if ($query->num_rows == 0) {
            // Insert a new record if table is empty
            $sql = "INSERT INTO election_status (status, election_name, end_time) VALUES ('pending', ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $election_name, $formatted_end_time);
            if ($stmt->execute()) {
                // Update config.ini file with new election name
                file_put_contents('config.ini', "election_name = \"$election_name\"\n");

                $_SESSION['general_config_complete'] = true;
                header('location: pre_election_positions.php');
                exit();
            } else {
                $_SESSION['error'] = 'Failed to save the election configuration. Please try again.';
            }
            $stmt->close();
        } else {
            $row = $query->fetch_assoc();
            $existing_name = $row['election_name'];
            $existing_end_time = $row['end_time'];
            $election_id = $row['id'];

            // Normalize existing_end_time to 'YYYY-MM-DD HH:MM:SS'
            $formatted_existing_end_time = date('Y-m-d H:i:s', strtotime($existing_end_time));

            if ($election_name != $existing_name || $formatted_end_time != $formatted_existing_end_time) {
                // Update existing record if values have changed
                $sql = "UPDATE election_status SET status = 'pending', election_name = ?, end_time = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $election_name, $formatted_end_time, $election_id);
                if ($stmt->execute()) {
                    // Update config.ini file with new election name
                    file_put_contents('config.ini', "election_name = \"$election_name\"\n");
                    $conn->query("TRUNCATE TABLE positions");

                    $_SESSION['general_config_complete'] = true;
                    $_SESSION['success'] = 'General election configuration saved successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to update the election configuration. Please try again.';
                }
                $stmt->close();
            } else {
                $_SESSION['general_config_complete'] = true;
            }
            header('location: pre_election_positions.php');
            exit();
        }
    } elseif ($status == 'positions') {
        $sql = "SELECT id FROM positions";
        $query = $conn->query($sql);

        if ($query === false) {
            error_log("Database query failed: " . $conn->error);
            $_SESSION['error'] = 'Database query failed.';
            $_SESSION['positions_config_complete'] = false;
        } elseif ($query->num_rows > 0) {
            $_SESSION['positions_config_complete'] = true;
        } else {
            $_SESSION['positions_config_complete'] = false;
        }

        header('location: pre_election_candidates.php');
        exit();
    } elseif ($status == 'candidates') {
        $sql = "SELECT id FROM candidates";
        $query = $conn->query($sql);

        if ($query === false) {
            error_log("Database query failed: " . $conn->error);
            $_SESSION['error'] = 'Database query failed.';
            $_SESSION['candidates_config_complete'] = false;
        } elseif ($query->num_rows > 0) {
            $sql = "UPDATE election_status SET status = 'paused' WHERE status = 'pending'";
            if ($conn->query($sql)) {
                $_SESSION['success'] = $election_name . ' was successfully created!';
                header('location: home.php');
                exit();
            } else {
                $_SESSION['error'] = 'Failed to update the election status.';
            }
        } else {
            $_SESSION['candidates_config_complete'] = false;
        }

        header('location: pre_election_candidates.php');
        exit();
    } else {
        $_SESSION['error'] = 'Invalid form submission';
        header('location: pre_election.php');
        exit();
    }
} else {
    $_SESSION['error'] = 'Fill up the form first';
    header('location: pre_election.php');
    exit();
}
