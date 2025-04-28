<?php
// archive_process.php: Handles archiving, PDF generation, and progress reporting (AJAX)

// Debug function to write to a file for step-by-step debugging
function debug_log($message) {
    $logfile = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/logs/archive_debug.log';
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Start logging
debug_log("Script started");

// Suppress display of PHP errors to prevent them breaking JSON output
ini_set('display_errors', 0);
error_reporting(0);

debug_log("Error reporting configured");

// Enable error logging to file
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/e-halal/logs/php-errors.log');
error_log("Archive process starting: " . date('Y-m-d H:i:s'));

debug_log("Error logging configured");

// Check if session is already started before calling session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

debug_log("Session started");

// Basic includes first to ensure they work
debug_log("Loading basic includes");
require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/init.php';
debug_log("init.php loaded");

require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/classes/Database.php';
debug_log("Database.php loaded");

// Now load admin classes
debug_log("Loading admin classes");
require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Admin.php';
debug_log("Admin.php loaded");

require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Elections.php';
debug_log("Elections.php loaded");

// Set JSON content type
header('Content-Type: application/json');
debug_log("Headers set");

// Get admin instance
debug_log("Getting Admin instance");
$admin = Admin::getInstance();
debug_log("Admin instance created");

// Check authentication
if (!$admin->isLoggedIn() || !$admin->isHead()) {
    debug_log("Authentication failed");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

debug_log("Authentication passed");

// Get elections instance
debug_log("Getting Elections instance");
$elections = Elections::getInstance();
debug_log("Elections instance created");

// Check current election
debug_log("Getting current election");
$current = $elections->getCurrentElection();
debug_log("Current election data received: " . json_encode($current));

if (!$current || $current['status'] !== Elections::STATUS_COMPLETED) {
    debug_log("No completed election to archive");
    echo json_encode(['success' => false, 'message' => 'No completed election to archive.']);
    exit();
}

debug_log("Election status valid");

// Set up archive directory
$controlNumber = $current['control_number'];
debug_log("Control number: " . $controlNumber);

// Verify directory paths
$parentDir = $_SERVER['DOCUMENT_ROOT'] . "/e-halal/administrator/archives";
debug_log("Parent directory path: " . $parentDir);

$archiveDir = $parentDir . "/{$controlNumber}";
debug_log("Archive directory path: " . $archiveDir);

// Create parent directory if it doesn't exist
if (!is_dir($parentDir)) {
    debug_log("Creating parent directory");
    if (!mkdir($parentDir, 0777, true)) {
        debug_log("Failed to create parent directory");
        echo json_encode([
            'success' => false, 
            'message' => "Failed to create parent archive directory: $parentDir"
        ]);
        exit();
    }
    debug_log("Parent directory created");
}

// Check permissions
if (!is_writable($parentDir)) {
    debug_log("Parent directory not writable");
    echo json_encode([
        'success' => false, 
        'message' => "Archive parent directory is not writable: $parentDir"
    ]);
    exit();
}

debug_log("Parent directory is writable");

// Create archive directory if needed
if (!is_dir($archiveDir)) {
    debug_log("Creating archive directory");
    if (!mkdir($archiveDir, 0777, true)) {
        debug_log("Failed to create archive directory");
        echo json_encode([
            'success' => false, 
            'message' => "Failed to create archive directory: $archiveDir"
        ]);
        exit();
    }
    debug_log("Archive directory created");
}

// Check archive directory permissions
if (!is_writable($archiveDir)) {
    debug_log("Archive directory not writable");
    echo json_encode([
        'success' => false, 
        'message' => "Archive directory is not writable: $archiveDir"
    ]);
    exit();
}

debug_log("Archive directory is writable");

// Right after creating archive directory and checking permissions
debug_log("Checking required image files for PDF generation");

// Check and create placeholder logo files if needed
$leftLogoPath = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/assets/images/btech.png';
$rightLogoPath = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/assets/images/ehalal.jpg';

debug_log("Left logo path: " . $leftLogoPath);
debug_log("Right logo path: " . $rightLogoPath);

// Create placeholder images directory if it doesn't exist
$imagesDir = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/assets/images';
if (!is_dir($imagesDir)) {
    debug_log("Creating images directory");
    if (!mkdir($imagesDir, 0777, true)) {
        debug_log("Failed to create images directory");
        echo json_encode([
            'success' => false, 
            'message' => "Failed to create images directory: $imagesDir"
        ]);
        exit();
    }
    debug_log("Images directory created");
}

// Create placeholder images if they don't exist
if (!file_exists($leftLogoPath)) {
    debug_log("Creating placeholder left logo");
    // Generate a simple 100x100 colored rectangle
    $image = imagecreatetruecolor(100, 100);
    $backgroundColor = imagecolorallocate($image, 0, 100, 255); // Blue background
    imagefill($image, 0, 0, $backgroundColor);
    // Add text to the image
    $textColor = imagecolorallocate($image, 255, 255, 255); // White text
    imagestring($image, 5, 20, 40, 'LOGO', $textColor);
    // Save the image
    imagepng($image, $leftLogoPath);
    imagedestroy($image);
    debug_log("Left logo placeholder created");
}

if (!file_exists($rightLogoPath)) {
    debug_log("Creating placeholder right logo");
    // Generate a simple 100x100 colored rectangle
    $image = imagecreatetruecolor(100, 100);
    $backgroundColor = imagecolorallocate($image, 0, 150, 50); // Green background
    imagefill($image, 0, 0, $backgroundColor);
    // Add text to the image
    $textColor = imagecolorallocate($image, 255, 255, 255); // White text
    imagestring($image, 5, 20, 40, 'LOGO', $textColor);
    // Save the image
    imagejpeg($image, $rightLogoPath);
    imagedestroy($image);
    debug_log("Right logo placeholder created");
}

debug_log("Image checks complete");

// Step 1: Generate Results PDF
debug_log("Starting Results PDF generation");
$resultsPath = $archiveDir . '/results.pdf';
debug_log("Results path: " . $resultsPath);

$_GET['save'] = 1;
$_GET['path'] = $resultsPath;
unset($GLOBALS['pdf_error']);

debug_log("Including export_results.php");
try {
    ob_start();
    include __DIR__ . '/export_results.php';
    ob_end_clean();
    debug_log("export_results.php included successfully");
} catch (Exception $e) {
    debug_log("Error in export_results.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => "Error generating results PDF: " . $e->getMessage()
    ]);
    exit();
}

// Check if PDF was created
if (!file_exists($resultsPath)) {
    debug_log("Results PDF file not created");
    echo json_encode([
        'success' => false, 
        'message' => "Results PDF file was not created"
    ]);
    exit();
}

debug_log("Results PDF created successfully");

// Step 2: Generate Summary PDF
debug_log("Starting Summary PDF generation");
$summaryPath = $archiveDir . '/summary.pdf';
debug_log("Summary path: " . $summaryPath);

$_GET['save'] = 1;
$_GET['path'] = $summaryPath;
unset($GLOBALS['pdf_error']);

debug_log("Including export_summary.php");
try {
    ob_start();
    include __DIR__ . '/export_summary.php';
    ob_end_clean();
    debug_log("export_summary.php included successfully");
} catch (Exception $e) {
    debug_log("Error in export_summary.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => "Error generating summary PDF: " . $e->getMessage()
    ]);
    exit();
}

// Check if PDF was created
if (!file_exists($summaryPath)) {
    debug_log("Summary PDF file not created");
    echo json_encode([
        'success' => false, 
        'message' => "Summary PDF file was not created"
    ]);
    exit();
}

debug_log("Summary PDF created successfully");

// Step 3: Call archiveElection to update database
debug_log("Archiving election in database");
try {
    $result = $elections->archiveElection([
        'results_pdf' => $resultsPath,
        'summary_pdf' => $summaryPath
    ]);
    debug_log("archiveElection result: " . json_encode($result));
} catch (Exception $e) {
    debug_log("Error in archiveElection: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => "Database error during archiving: " . $e->getMessage()
    ]);
    exit();
}

// Check result and return response
if ($result['success']) {
    debug_log("Election archived successfully");
    echo json_encode([
        'success' => true,
        'message' => 'Election archived successfully.',
        'results_pdf' => "/e-halal/administrator/archives/{$controlNumber}/results.pdf",
        'summary_pdf' => "/e-halal/administrator/archives/{$controlNumber}/summary.pdf"
    ]);
} else {
    debug_log("Archiving failed: " . $result['message']);
    echo json_encode([
        'success' => false, 
        'message' => $result['message']
    ]);
}

debug_log("Script completed");
?>
