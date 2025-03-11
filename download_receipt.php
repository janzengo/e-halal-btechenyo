<?php
session_start();
require_once 'init.php';  // Add this to ensure all configurations are loaded
require_once 'classes/Database.php';
require_once 'classes/Receipt.php';

// Debug session data
error_log("Session data: " . print_r($_SESSION, true));
error_log("GET data: " . print_r($_GET, true));

// Clear any existing output
ob_clean();

// Check for proper voter session and vote reference
if (!isset($_SESSION['voter']) || !isset($_GET['ref'])) {
    error_log("Session validation failed: voter or ref missing");
    
    // Instead of silently exiting, send a JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Invalid session or missing vote reference',
        'debug' => [
            'has_voter' => isset($_SESSION['voter']),
            'has_ref' => isset($_GET['ref']),
            'session_data' => $_SESSION
        ]
    ]);
    exit;
}

try {
    $vote_ref = $_GET['ref'];
    $db = Database::getInstance();
    
    // Validate that this vote belongs to the current voter
    $sql = "SELECT v.*, e.election_name, vt.student_number 
            FROM votes v 
            JOIN voters vt ON vt.id = ?
            JOIN election_status e ON v.election_id = e.id 
            WHERE v.vote_ref = ?";
    
    $stmt = $db->prepare($sql);
    $stmt->bind_param("is", $_SESSION['voter'], $vote_ref);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Unauthorized access attempt to vote receipt: " . $vote_ref);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => 'Unauthorized access to vote receipt'
        ]);
        exit;
    }

    // Initialize Receipt class
    $receipt = new Receipt();
    
    // Generate PDF
    $pdf_content = $receipt->generatePDF($vote_ref);
    
    // Set appropriate headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="Vote_Receipt_' . $vote_ref . '.pdf"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Output the PDF
    echo $pdf_content;
    exit;
    
} catch (Exception $e) {
    error_log("Error generating PDF: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Failed to generate PDF: ' . $e->getMessage()
    ]);
    exit;
} 