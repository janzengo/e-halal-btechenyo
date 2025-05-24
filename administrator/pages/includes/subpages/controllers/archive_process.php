<?php
// archive_process.php: Handles archiving, PDF generation, and progress reporting (AJAX)

// Prevent any output before headers
ob_start();

// Debug mode - set to true to see detailed errors
define('DEBUG_MODE', true);

// Set error handling to catch all errors
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Define debug_log function with error_log fallback
function debug_log($message) {
    try {
        $base_path = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $logsDir = $base_path . '/logs';
        
        // Create logs directory if it doesn't exist
        if (!file_exists($logsDir)) {
            mkdir($logsDir, 0777, true);
        }
        
        $logfile = $logsDir . '/archive_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "$timestamp - $message\n";
        
        // Try to write to file, fallback to error_log if fails
        if (!@file_put_contents($logfile, $log_message, FILE_APPEND)) {
            error_log("Failed to write to debug log file. Falling back to error_log.");
            error_log($log_message);
        }
    } catch (Exception $e) {
        error_log("Debug log error: " . $e->getMessage());
        error_log($message);
    }
}

// Function to clean all output buffers and return the cleaned content
function clean_all_output() {
    $output = '';
    while (ob_get_level()) {
        $output .= ob_get_clean();
    }
    return $output;
}

// Function to send JSON response with debug info
function send_json_response($success, $message, $data = []) {
    try {
        debug_log("Preparing JSON response - Success: " . ($success ? 'true' : 'false'));
        debug_log("Response message: " . $message);

        // Clean any existing output first
        $cleaned_output = clean_all_output();
        debug_log("Cleaned output length: " . strlen($cleaned_output));
        
        if (!headers_sent()) {
            header('Content-Type: application/json');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            debug_log("Headers set successfully");
        } else {
            debug_log("WARNING: Headers already sent!");
            debug_log("Output buffer level: " . ob_get_level());
        }
        
        // Prepare response
        $response = array_merge(
            [
                'success' => $success,
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s')
            ],
            $data
        );
        
        if (DEBUG_MODE) {
            $response['debug'] = [
                'memory_usage' => memory_get_usage(true),
                'peak_memory_usage' => memory_get_peak_usage(true),
                'output_cleaned' => strlen($cleaned_output) > 0,
                'cleaned_output' => $cleaned_output,
                'headers_sent' => headers_sent(),
                'output_buffer_level' => ob_get_level()
            ];
        }
        
        // Ensure we're sending valid JSON
        $json = json_encode($response);
        if ($json === false) {
            $json_error = json_last_error_msg();
            debug_log("JSON encode error: " . $json_error);
            throw new Exception("JSON encoding failed: " . $json_error);
        }
        
        debug_log("JSON response prepared successfully");
        debug_log("Response length: " . strlen($json));
        
        echo $json;
        debug_log("JSON response sent");
        exit();
    } catch (Exception $e) {
        error_log("Error in send_json_response: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'error' => $e->getMessage()
        ]);
        exit();
    }
}

// Custom error handler
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    $error_message = sprintf("Error [%d]: %s in %s on line %d", $errno, $errstr, $errfile, $errline);
    error_log($error_message);
    
    if (function_exists('debug_log')) {
        debug_log("PHP Error: " . $error_message);
    }
    
    if (DEBUG_MODE) {
        $data = [
            'error_details' => $error_message,
            'debug' => [
                'error_type' => $errno,
                'error_file' => $errfile,
                'error_line' => $errline,
                'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
            ]
        ];
    } else {
        $data = ['error_details' => 'Check server logs for details'];
    }
    
    send_json_response(false, "Server error occurred", $data);
    return true;
}

// Custom exception handler
function custom_exception_handler($e) {
    $error_message = sprintf(
        "Exception: %s in %s on line %d",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    error_log($error_message);
    
    if (function_exists('debug_log')) {
        debug_log("PHP Exception: " . $error_message);
    }
    
    if (DEBUG_MODE) {
        $data = [
            'error_details' => $error_message,
            'debug' => [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ];
    } else {
        $data = ['error_details' => 'Check server logs for details'];
    }
    
    send_json_response(false, "Server exception occurred", $data);
}

// Set handlers
set_error_handler('custom_error_handler');
set_exception_handler('custom_exception_handler');
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $error_message = sprintf(
            "Fatal Error: %s in %s on line %d",
            $error['message'],
            $error['file'],
            $error['line']
        );
        error_log($error_message);
        
        if (function_exists('debug_log')) {
            debug_log("Fatal Error: " . $error_message);
        }
        
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        
        echo json_encode([
            'success' => false,
            'message' => 'Fatal error occurred',
            'error_details' => DEBUG_MODE ? $error_message : 'Check server logs for details'
        ]);
    }
});

try {
    // Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

    debug_log("Script started");
    debug_log("Error reporting configured");
    debug_log("Error logging configured");
debug_log("Session started");

    // Define base path using document root
    $base_path = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
    debug_log("Base path set to: " . $base_path);
    
    // Load required files using absolute paths
debug_log("Loading basic includes");
    require_once $base_path . '/init.php';
debug_log("init.php loaded");
    require_once $base_path . '/classes/Database.php';
debug_log("Database.php loaded");

debug_log("Loading admin classes");
    require_once $base_path . '/administrator/classes/Admin.php';
debug_log("Admin.php loaded");
    require_once $base_path . '/administrator/classes/Elections.php';
debug_log("Elections.php loaded");

    // Set headers for JSON response
debug_log("Headers set");

    // Get Admin instance and check authentication
debug_log("Getting Admin instance");
$admin = Admin::getInstance();
debug_log("Admin instance created");

    if (!$admin->isLoggedIn()) {
        send_json_response(false, "Unauthorized access", ['error_details' => 'User not logged in']);
    }
debug_log("Authentication passed");

    // Get Elections instance
debug_log("Getting Elections instance");
$elections = Elections::getInstance();
debug_log("Elections instance created");

    // Get current election
debug_log("Getting current election");
$current = $elections->getCurrentElection();
debug_log("Current election data received: " . json_encode($current));

    if (!$current || $current['status'] !== 'completed') {
        send_json_response(false, "Invalid election status", [
            'error_details' => 'Election must be completed to archive',
            'current_status' => $current ? $current['status'] : 'no election'
        ]);
}
debug_log("Election status valid");

    // Get control number
$controlNumber = $current['control_number'];
debug_log("Control number: " . $controlNumber);

    // Set up archive directory paths
    $parentDir = $base_path . '/administrator/archives';
    $archiveDir = $parentDir . '/' . $controlNumber;
    
debug_log("Parent directory path: " . $parentDir);
debug_log("Archive directory path: " . $archiveDir);

// Create parent directory if it doesn't exist
    debug_log("Creating parent directory");
    if (!file_exists($parentDir)) {
    if (!mkdir($parentDir, 0777, true)) {
            throw new Exception("Failed to create parent directory: " . $parentDir);
        }
    }
    debug_log("Parent directory created");

    // Check if parent directory is writable
if (!is_writable($parentDir)) {
        throw new Exception("Parent directory is not writable: " . $parentDir);
}
debug_log("Parent directory is writable");

    // Create archive directory
    debug_log("Creating archive directory");
    if (!file_exists($archiveDir)) {
    if (!mkdir($archiveDir, 0777, true)) {
            throw new Exception("Failed to create archive directory: " . $archiveDir);
        }
    }
    debug_log("Archive directory created");

    // Check if archive directory is writable
if (!is_writable($archiveDir)) {
        throw new Exception("Archive directory is not writable: " . $archiveDir);
}
debug_log("Archive directory is writable");

    // Check required image files for PDF generation
debug_log("Checking required image files for PDF generation");
    $leftLogo = $base_path . '/administrator/assets/images/btech.png';
    $rightLogo = $base_path . '/administrator/assets/images/ehalal.jpg';

    debug_log("Left logo path: " . $leftLogo);
    debug_log("Right logo path: " . $rightLogo);
    
    if (!file_exists($leftLogo)) {
        throw new Exception("Left logo file not found: " . $leftLogo);
}
    if (!file_exists($rightLogo)) {
        throw new Exception("Right logo file not found: " . $rightLogo);
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
        $export_results_path = __DIR__ . '/export_results.php';
        debug_log("Export results path: " . $export_results_path);
        
        if (!file_exists($export_results_path)) {
            // Try alternative locations
            $alternative_paths = [
                $base_path . '/administrator/pages/includes/export_results.php',
                $base_path . '/administrator/export_results.php',
                $base_path . '/export_results.php'
            ];
            
            debug_log("Export results file not found in primary location, trying alternatives...");
            foreach ($alternative_paths as $alt_path) {
                debug_log("Trying path: " . $alt_path);
                if (file_exists($alt_path)) {
                    $export_results_path = $alt_path;
                    debug_log("Found export_results.php at: " . $alt_path);
                    break;
                }
            }
            
            if (!file_exists($export_results_path)) {
                throw new Exception("Could not find export_results.php in any expected location");
            }
        }
        
        // Start output buffering with a callback that logs output
        ob_start(function($buffer) {
            if (!empty($buffer)) {
                debug_log("PDF generation output: " . substr($buffer, 0, 100) . "...");
            }
            return '';
        });
        
        debug_log("Including file: " . $export_results_path);
        include $export_results_path;
        
        // Clean any remaining output buffers
        while (ob_get_level()) {
    ob_end_clean();
        }
        
        // Check for PDF generation error
        if (isset($GLOBALS['pdf_error'])) {
            throw new Exception("Results PDF generation failed: " . $GLOBALS['pdf_error']);
}

        // Verify file was created
if (!file_exists($resultsPath)) {
            throw new Exception("Results PDF file was not created");
        }
        
        // Verify file size
        $fileSize = filesize($resultsPath);
        if ($fileSize === 0) {
            throw new Exception("Results PDF file is empty");
        }
        
        debug_log("Results PDF created successfully - Size: " . $fileSize . " bytes");
    } catch (Exception $e) {
        debug_log("Error in results PDF generation: " . $e->getMessage());
        throw new Exception("Results PDF generation failed: " . $e->getMessage());
    }

// Step 2: Generate Summary PDF
debug_log("Starting Summary PDF generation");
$summaryPath = $archiveDir . '/summary.pdf';
debug_log("Summary path: " . $summaryPath);

$_GET['save'] = 1;
$_GET['path'] = $summaryPath;
unset($GLOBALS['pdf_error']);

debug_log("Including export_summary.php");
try {
        $export_summary_path = __DIR__ . '/export_summary.php';
        debug_log("Export summary path: " . $export_summary_path);
        
        if (!file_exists($export_summary_path)) {
            // Try alternative locations
            $alternative_paths = [
                $base_path . '/administrator/pages/includes/export_summary.php',
                $base_path . '/administrator/export_summary.php',
                $base_path . '/export_summary.php'
            ];
            
            debug_log("Export summary file not found in primary location, trying alternatives...");
            foreach ($alternative_paths as $alt_path) {
                debug_log("Trying path: " . $alt_path);
                if (file_exists($alt_path)) {
                    $export_summary_path = $alt_path;
                    debug_log("Found export_summary.php at: " . $alt_path);
                    break;
                }
            }
            
            if (!file_exists($export_summary_path)) {
                throw new Exception("Could not find export_summary.php in any expected location");
            }
        }
        
        // Start output buffering with a callback that logs output
        ob_start(function($buffer) {
            if (!empty($buffer)) {
                debug_log("PDF generation output: " . substr($buffer, 0, 100) . "...");
            }
            return '';
        });
        
        debug_log("Including file: " . $export_summary_path);
        include $export_summary_path;
        
        // Clean any remaining output buffers
        while (ob_get_level()) {
    ob_end_clean();
        }
        
        // Check for PDF generation error
        if (isset($GLOBALS['pdf_error'])) {
            throw new Exception("Summary PDF generation failed: " . $GLOBALS['pdf_error']);
        }
        
        // Wait briefly to ensure file system has completed writing
        usleep(500000); // 0.5 second delay
        
        // Verify file was created
if (!file_exists($summaryPath)) {
            throw new Exception("Summary PDF file was not created at path: " . $summaryPath);
        }
        
        // Verify file size
        $fileSize = filesize($summaryPath);
        if ($fileSize === 0) {
            throw new Exception("Summary PDF file is empty at path: " . $summaryPath);
        }
        
        // Verify file is readable
        if (!is_readable($summaryPath)) {
            throw new Exception("Summary PDF file is not readable at path: " . $summaryPath);
        }
        
        // Try to open the file to verify it's a valid PDF
        $fileContent = file_get_contents($summaryPath);
        if ($fileContent === false) {
            throw new Exception("Failed to read Summary PDF file content");
        }
        
        // Check if it starts with %PDF (PDF file signature)
        if (substr($fileContent, 0, 4) !== '%PDF') {
            throw new Exception("Generated file is not a valid PDF");
        }

        debug_log("Summary PDF created successfully - Size: " . $fileSize . " bytes");
    } catch (Exception $e) {
        throw new Exception("Summary PDF generation failed: " . $e->getMessage());
    }
    
    // Step 3: Archive the election in the database
    debug_log("Starting database archival process");
    try {
        $archiveResult = $elections->archiveElection([
            'details_pdf' => str_replace($base_path, '', $summaryPath),
            'results_pdf' => str_replace($base_path, '', $resultsPath)
        ]);
        
        if (!$archiveResult['success']) {
            throw new Exception("Database archival failed: " . $archiveResult['message']);
        }
        
        debug_log("Database archival completed successfully");
    } catch (Exception $e) {
        throw new Exception("Failed to archive election in database: " . $e->getMessage());
    }
    
    // Send success response
    send_json_response(true, "Archive process completed successfully", [
        'archive_path' => $archiveDir,
        'files' => [
            'results' => $resultsPath,
            'summary' => $summaryPath
        ],
        'control_number' => $current['control_number']
    ]);
    
} catch (Exception $e) {
    $error_message = sprintf(
        "Archive Process Error: %s in %s on line %d",
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );
    error_log($error_message);
    
    if (function_exists('debug_log')) {
        debug_log("Fatal error: " . $error_message);
    }
    
    if (DEBUG_MODE) {
        $data = [
            'error_details' => $error_message,
            'debug' => [
                'trace' => $e->getTraceAsString(),
                'memory_usage' => memory_get_usage(true),
                'peak_memory_usage' => memory_get_peak_usage(true)
            ]
        ];
} else {
        $data = ['error_details' => 'Archive process failed. Check server logs for details.'];
    }
    
    send_json_response(false, "Archive process failed", $data);
}

// Final cleanup
$cleaned = clean_all_output();
if ($cleaned && DEBUG_MODE) {
    error_log("Cleaned output at end of script: " . $cleaned);
}

debug_log("Script completed");
if (ob_get_level() > 0) {
    debug_log("WARNING: Output buffers still active at end of script");
    debug_log("Cleaning remaining output buffers");
    $final_output = clean_all_output();
    debug_log("Final cleaned output length: " . strlen($final_output));
}
?>
