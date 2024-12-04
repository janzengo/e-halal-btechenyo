<?php
include 'includes/session.php';
include 'db_operations.php';
include 'pdf_generation.php';

// Fetch the current election details from the database
$sql = "SELECT * FROM election_status ORDER BY id DESC LIMIT 1";
$query = $conn->query($sql);

if ($query->num_rows > 0) {
    $election = $query->fetch_assoc();
    $election_id = $election['id'];
    $election_name = $election['election_name'];
    $start_time = $election['start_time'];
    $end_time = $election['end_time'];

    // Define the folder path for saving the PDFs
    $folder_name = strtolower(str_replace(' ', '-', $election_name));
    $folder_path = "/$folder_name"; // Use absolute path
    if (!file_exists($folder_path)) {
        mkdir($folder_path, 0777, true);
    }

    // Generate details PDF
    $details_pdf_path = "$folder_path/details.pdf";
    generateDetailsPdfWithHeader($conn, $election_id, $details_pdf_path, $election_name);

    // Generate results PDF
    $results_pdf_path = "$folder_path/results.pdf";
    generateResultsPdfWithHeader($conn, $election_id, $results_pdf_path, $election_name);

    // Test saving election history without truncating the election_status table
    if (saveElectionToHistory($conn, $details_pdf_path, $results_pdf_path, false)) {
        echo "Election history saved successfully. Check the 'election_history/$folder_name' directory.";
    } else {
        echo "Failed to save election history.";
    }
} else {
    echo "No current election found in the database.";
}
?>