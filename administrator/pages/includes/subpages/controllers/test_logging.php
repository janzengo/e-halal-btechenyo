<?php
// Simple test script to verify error logging functionality

// Debug function to write to a file
function debug_log($message) {
    $logfile = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/logs/test_debug.log';
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Enable error logging to file
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/e-halal/logs/php-errors.log');

// Start the test
debug_log("Test script started");
error_log("Test error logging");

// Check document root
debug_log("Document root: " . $_SERVER['DOCUMENT_ROOT']);

// Check directory existence and permissions
$testDir = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/logs';
debug_log("Testing directory: $testDir");
debug_log("Directory exists: " . (is_dir($testDir) ? 'Yes' : 'No'));
debug_log("Directory writable: " . (is_writable($testDir) ? 'Yes' : 'No'));

// Try creating a test file
$testFile = $testDir . '/test_file.txt';
$result = file_put_contents($testFile, "Test content");
debug_log("File creation result: " . ($result !== false ? 'Success' : 'Failed'));

// Send a JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Test completed successfully. Check the logs.',
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'test_dir' => $testDir,
    'test_file' => $testFile
]);
?> 