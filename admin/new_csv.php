<?php
include 'includes/session.php';
include 'includes/conn.php';

if (isset($_POST['submit'])) {
    if ($_FILES['file']['name']) {
        $filename = $_FILES['file']['name'];
        $file_tmp_name = $_FILES['file']['tmp_name'];
        $file_size = $_FILES['file']['size'];
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_extensions = array('csv');

        if (in_array($file_extension, $allowed_extensions)) {
            if ($file_size < 1048576) { // Limit file size to 1MB
                $handle = fopen($file_tmp_name, "r");
                $defaultProfilePicture = 'profile.jpg';
                $errors = [];
                $success = 0;
                $duplicates = 0;

                // Skip the header row
                $header = fgetcsv($handle);

                // Prepare the SQL statement
                $sql = "INSERT INTO voters (voters_id, course_id, password, firstname, lastname, photo) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sissss", $voters_id, $course_id, $password, $firstname, $lastname, $photo);

                while ($data = fgetcsv($handle)) {
                    $firstname = $data[0];
                    $lastname = $data[1];
                    $voters_id = $data[2];
                    $course_id = $data[3];

                    // Check if voters_id already exists
                    $check_sql = "SELECT * FROM voters WHERE voters_id = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("s", $voters_id);
                    $check_stmt->execute();
                    $check_stmt->store_result();

                    if ($check_stmt->num_rows > 0) {
                        $duplicates++;
                    } else {
                        // Generate random password
                        $random_password = bin2hex(random_bytes(8));
                        $password = password_hash($random_password, PASSWORD_DEFAULT);
                        $photo = $defaultProfilePicture;

                        // Execute the prepared statement
                        if ($stmt->execute()) {
                            $success++;
                        } else {
                            $errors[] = "Error inserting voter ID $voters_id: " . $conn->error;
                        }
                    }
                }
                fclose($handle);

                if ($success > 0) {
                    $_SESSION['success'] = "$success voters added successfully.";
                }

                if ($duplicates > 0) {
                    $_SESSION['error'] = "$duplicates duplicate voters skipped.";
                }

                if (!empty($errors)) {
                    $_SESSION['error'] .= "<br>" . implode("<br>", $errors);
                }
            } else {
                $_SESSION['error'] = "File size exceeds the limit (1MB).";
            }
        } else {
            $_SESSION['error'] = "Please upload a valid CSV file.";
        }
    } else {
        $_SESSION['error'] = "Please select a file.";
    }
    header('location: voters.php');
}