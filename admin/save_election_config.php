<?php
include 'includes/session.php';
include 'db_operations.php';
include 'pdf_generation.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = isset($_POST['status']) ? htmlspecialchars(strip_tags($_POST['status'])) : null;
    $config_status = isset($_POST['origin-status']) ? htmlspecialchars(strip_tags($_POST['origin-status'])) : null;

    if ($status == 'general') {
        handleGeneralConfig($conn);
    } elseif ($status == 'positions') {
        handlePositionsConfig($conn);
    } elseif ($status == 'candidates') {
        handleCandidatesConfig($conn);
    } elseif ($config_status == "config") {
        handleConfigStatus($conn);
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

function handleGeneralConfig($conn) {
    $election_name = htmlspecialchars(strip_tags($_POST['election_name']));
    $end_time = htmlspecialchars(strip_tags($_POST['end_time']));
    $formatted_end_time = date('Y-m-d H:i:s', strtotime($end_time));

    if (empty($election_name) || empty($formatted_end_time)) {
        $_SESSION['error'] = 'Election name and end time cannot be empty.';
        $_SESSION['general_config_complete'] = false;
        header('location: pre_election_positions.php');
        exit();
    }

    $query = getLatestElectionRecord($conn);

    if ($query->num_rows == 0) {
        if (insertElectionRecord($conn, $election_name, $formatted_end_time)) {
            updateConfigFiles($election_name);
            $_SESSION['general_config_complete'] = true;
            header('location: pre_election_positions.php');
            exit();
        } else {
            $_SESSION['error'] = 'Failed to save the election configuration. Please try again.';
        }
    } else {
        $row = $query->fetch_assoc();
        $existing_name = $row['election_name'];
        $existing_end_time = $row['end_time'];
        $election_id = $row['id'];
        $formatted_existing_end_time = date('Y-m-d H:i:s', strtotime($existing_end_time));

        if ($election_name != $existing_name || $formatted_end_time != $formatted_existing_end_time) {
            if (updateElectionRecord($conn, $election_name, $formatted_end_time, $election_id)) {
                updateConfigFiles($election_name);
                truncateTable($conn, 'positions');
                $_SESSION['general_config_complete'] = true;
                $_SESSION['success'] = 'General election configuration saved successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update the election configuration. Please try again.';
            }
        } else {
            $_SESSION['general_config_complete'] = true;
        }
        header('location: pre_election_positions.php');
        exit();
    }
}

function handlePositionsConfig($conn) {
    $query = $conn->query("SELECT id FROM positions");

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
}

function handleCandidatesConfig($conn) {
    $query = $conn->query("SELECT id FROM candidates");

    if ($query === false) {
        error_log("Database query failed: " . $conn->error);
        $_SESSION['error'] = 'Database query failed.';
        $_SESSION['candidates_config_complete'] = false;
    } elseif ($query->num_rows > 0) {
        if (updateElectionStatus($conn, 'paused')) {
            // Generate PDFs and save election history when status is set to "paused"
            $election_name = htmlspecialchars(strip_tags($_POST['election_name']));
            $end_time = htmlspecialchars(strip_tags($_POST['end_time']));
            $formatted_end_time = date('Y-m-d H:i:s', strtotime($end_time));

            $query = $conn->query("SELECT id, start_time FROM election_status ORDER BY id DESC LIMIT 1");

            if ($query->num_rows > 0) {
                $row = $query->fetch_assoc();
                $election_id = $row['id'];
                $start_time = $row['start_time'];

                $folder_name = strtolower(str_replace(' ', '-', $election_name));
                $folder_path = __DIR__ . "/election_history/$folder_name";
                if (!file_exists($folder_path)) {
                    mkdir($folder_path, 0777, true);
                }

                $details_pdf_path = "$folder_path/details.pdf";
                generateDetailsPdfWithHeader($conn, $election_id, $details_pdf_path, $election_name);

                $results_pdf_path = "$folder_path/results.pdf";
                generateResultsPdfWithHeader($conn, $election_id, $results_pdf_path, $election_name);

                $end_date = date('Y-m-d H:i:s');
                insertElectionHistory($conn, $election_name, $start_time, $end_date, $details_pdf_path, $results_pdf_path);

                // For testing purposes, do not truncate the tables
                // truncateTable($conn, 'election_status');
                // truncateTable($conn, 'positions');
                // truncateTable($conn, 'voters');
                // truncateTable($conn, 'votes');
                // truncateTable($conn, 'candidates');

                $_SESSION['success'] = $_POST['election_name'] . ' was successfully created!';
                header('location: home.php');
                exit();
            } else {
                $_SESSION['error'] = 'No election record found.';
            }
        } else {
            $_SESSION['error'] = 'Failed to update the election status.';
        }
    } else {
        $_SESSION['candidates_config_complete'] = false;
    }

    header('location: pre_election_candidates.php');
    exit();
}

function handleConfigStatus($conn) {
    $election_name = htmlspecialchars(strip_tags($_POST['election_name']));
    $end_time = htmlspecialchars(strip_tags($_POST['end_time']));
    $election_status = htmlspecialchars(strip_tags($_POST['e_status']));
    $formatted_end_time = date('Y-m-d H:i:s', strtotime($end_time));

    $query = $conn->query("SELECT id, start_time FROM election_status ORDER BY id DESC LIMIT 1");

    if ($query->num_rows > 0) {
        $row = $query->fetch_assoc();
        $election_id = $row['id'];
        $start_time = $row['start_time'];

        $sql = "UPDATE election_status SET election_name = ?, end_time = ?, status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $election_name, $formatted_end_time, $election_status, $election_id);

        if ($stmt->execute()) {
            if ($election_status == 'off') {
                $folder_name = strtolower(str_replace(' ', '-', $election_name));
                $folder_path = "/$folder_name";
                if (!file_exists($folder_path)) {
                    mkdir($folder_path, 0777, true);
                }

                $details_pdf_path = "$folder_path/details.pdf";
                generateDetailsPdfWithHeader($conn, $election_id, $details_pdf_path, $election_name);

                $results_pdf_path = "$folder_path/results.pdf";
                generateResultsPdfWithHeader($conn, $election_id, $results_pdf_path, $election_name);

                $end_date = date('Y-m-d H:i:s');
                insertElectionHistory($conn, $election_name, $start_time, $end_date, $details_pdf_path, $results_pdf_path);

                // For testing purposes, comment these to not truncate the tables
                truncateTable($conn, 'election_status');
                truncateTable($conn, 'positions');
                truncateTable($conn, 'voters');
                truncateTable($conn, 'votes');
                truncateTable($conn, 'candidates');

                header('location: post_election.php');
                exit();
            }

            updateConfigFiles($election_name);
            $_SESSION['success'] = 'Election configuration updated successfully.';
        } else {
            error_log("Failed to execute query: " . $stmt->error);
            $_SESSION['error'] = 'Failed to update the election configuration. Please try again.';
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = 'No election record found.';
    }

    header('location: configure_election.php');
    exit();
}

function updateConfigFiles($election_name) {
    file_put_contents('config.ini', "election_name = \"$election_name\"\n");
}
