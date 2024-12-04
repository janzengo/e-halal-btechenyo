<?php
function getLatestElectionRecord($conn) {
    $sql = "SELECT id, election_name, start_time, end_time FROM election_status ORDER BY id DESC LIMIT 1";
    return $conn->query($sql);
}

function insertElectionRecord($conn, $election_name, $formatted_end_time) {
    $sql = "INSERT INTO election_status (status, election_name, end_time, start_time) VALUES ('pending', ?, ?, NULL)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $election_name, $formatted_end_time);
    return $stmt->execute();
}

function updateElectionRecord($conn, $election_name, $formatted_end_time, $election_id) {
    $sql = "UPDATE election_status SET status = 'pending', election_name = ?, end_time = ?, start_time = NULL WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $election_name, $formatted_end_time, $election_id);
    return $stmt->execute();
}

function truncateTable($conn, $table) {
    $sql = "TRUNCATE TABLE $table";
    return $conn->query($sql);
}

function updateElectionStatus($conn, $status) {
    $sql = "UPDATE election_status SET status = ?, start_time = NOW() WHERE status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status);
    return $stmt->execute();
}

function insertElectionHistory($conn, $election_name, $start_time, $end_date, $details_pdf_path, $results_pdf_path) {
    $sql = "INSERT INTO election_history (election_name, start_date, end_date, details_pdf, results_pdf, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $election_name, $start_time, $end_date, $details_pdf_path, $results_pdf_path);
    return $stmt->execute();
}

function getPositions($conn) {
    $sql = "SELECT * FROM positions ORDER BY priority ASC";
    return $conn->query($sql);
}

function getCandidatesByPosition($conn, $position_id) {
    $sql = "SELECT * FROM candidates WHERE position_id = '$position_id' ORDER BY lastname ASC";
    return $conn->query($sql);
}

function getVotesByCandidate($conn, $candidate_id) {
    $sql = "SELECT * FROM votes WHERE candidate_id = '$candidate_id'";
    return $conn->query($sql);
}

function saveElectionToHistory($conn, $details_pdf_path, $results_pdf_path, $truncate = true) {
    // Get the latest election record
    $result = getLatestElectionRecord($conn);
    if ($result->num_rows > 0) {
        $election = $result->fetch_assoc();
        $election_name = $election['election_name'];
        $start_time = $election['start_time'];
        $end_time = $election['end_time'];

        // Insert the election history
        if (insertElectionHistory($conn, $election_name, $start_time, $end_time, $details_pdf_path, $results_pdf_path)) {
            // Optionally truncate the election_status table
            if ($truncate) {
                truncateTable($conn, 'election_status');
            }
            return true;
        }
    }
    return false;
}
?>