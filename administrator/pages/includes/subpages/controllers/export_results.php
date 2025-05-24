<?php
// Enable error display
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if script is being run directly for testing/debugging
$is_standalone_test = (php_sapi_name() === 'cli' && isset($argv) && basename($argv[0]) === basename(__FILE__));

// Check if session is already started before calling session_start()
if (session_status() === PHP_SESSION_NONE && !$is_standalone_test) {
    session_start();
}

// Define base path for the project
$base_path = dirname(dirname(dirname(dirname(dirname(__DIR__)))));

// Create logs directory if it doesn't exist
$logs_dir = $base_path . '/logs';
if (!file_exists($logs_dir)) {
    mkdir($logs_dir, 0777, true);
}

// Add debug file for step-by-step tracking
function results_debug($message) {
    global $base_path;
    $logfile = $base_path . '/logs/results_debug.log';
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Enable error logging to file
ini_set('log_errors', 1);
ini_set('error_log', $base_path . '/logs/php-errors.log');
error_log("export_results.php called: " . date('Y-m-d H:i:s'));

// Include shared PDF helper functions
require_once __DIR__ . '/pdf_helpers.php';

// Set TCPDF font paths
define('K_PATH_MAIN', $base_path . '/vendor/tecnickcom/tcpdf/');
define('K_PATH_FONTS', K_PATH_MAIN . 'fonts/');
define('K_PATH_CACHE', sys_get_temp_dir() . '/');

results_debug("Starting results PDF generation");

// Check for TCPDF
$tcpdf_path = check_tcpdf_exists();
require_once $tcpdf_path;

// Custom PDF class with header and footer - only define it once
if (!class_exists('ElectionPDF')) {
    class ElectionPDF extends TCPDF {
        protected $headerTitle;
        protected $electionName;
        
        public function setHeaderTitle($title) {
            $this->headerTitle = $title;
        }
        
        public function setElectionName($name) {
            $this->electionName = $name;
        }
        
        // Helper function to check if a font is valid
        public function isValidFont($fontname) {
            $fontpath = K_PATH_FONTS . $fontname . '.php';
            return file_exists($fontpath);
        }
        
        // Page header with improved font handling
        public function Header() {
            global $base_path;
            
            // Logo
            $leftLogo = $base_path . '/administrator/assets/images/btech.png';
            $rightLogo = $base_path . '/administrator/assets/images/ehalal.jpg';
            
            results_debug("Loading logos - Left: $leftLogo, Right: $rightLogo");
            
            // Check if logo files exist
            if (!file_exists($leftLogo)) {
                results_debug("WARNING: Left logo file not found: $leftLogo");
            }
            
            if (!file_exists($rightLogo)) {
                results_debug("WARNING: Right logo file not found: $rightLogo");
            }
            
            // Improved logo positioning and sizing with error handling
            try {
                if (file_exists($leftLogo)) {
                    $this->Image($leftLogo, 30, 8, 20);
                }
                
                if (file_exists($rightLogo)) {
                    $this->Image($rightLogo, 165, 8, 15);
                }
                results_debug("Logos loaded successfully");
            } catch (Exception $e) {
                results_debug("ERROR loading images: " . $e->getMessage());
            }
            
            // Enhanced header text with better spacing and font handling
            try {
                // Set default font if helvetica is not available
                if (!$this->isValidFont('helvetica')) {
                    $this->SetFont('freesans', 'B', 12);
                    results_debug("Using freesans font as fallback");
                } else {
                    $this->SetFont('helvetica', 'B', 12);
                    results_debug("Using helvetica font");
                }
                
                $this->SetY(8);
                $this->Cell(0, 6, 'Dalubhasaang Politekniko ng Lungsod ng Baliwag', 0, 1, 'C');
                
                if (!$this->isValidFont('helvetica')) {
                    $this->SetFont('freesans', '', 9);
                } else {
                    $this->SetFont('helvetica', '', 9);
                }
                $this->Cell(0, 4, 'The Official Electoral Board', 0, 1, 'C');
                
                if (!$this->isValidFont('helvetica')) {
                    $this->SetFont('freesans', 'B', 9);
                } else {
                    $this->SetFont('helvetica', 'B', 9);
                }
                $this->Cell(0, 4, 'E-Halal BTECHenyo | Vote Wise BTECHenyos!', 0, 1, 'C');
                
                // Election name and title with improved spacing
                $this->Ln(6);
                if (!$this->isValidFont('helvetica')) {
                    $this->SetFont('freesans', 'B', 14);
                } else {
                    $this->SetFont('helvetica', 'B', 14);
                }
                $this->Cell(0, 8, $this->electionName, 0, 1, 'C');
                
                if (!$this->isValidFont('helvetica')) {
                    $this->SetFont('freesans', 'B', 13);
                } else {
                    $this->SetFont('helvetica', 'B', 13);
                }
                $this->Cell(0, 8, $this->headerTitle, 0, 1, 'C');
                
                results_debug("Header text rendered successfully");
            } catch (Exception $e) {
                results_debug("ERROR rendering header text: " . $e->getMessage());
            }
            
            // Add decorative line
            $this->SetLineWidth(0.3);
            $this->SetDrawColor(33, 145, 80); // Set line color to green
            $this->Line(40, $this->GetY() + 2, 170, $this->GetY() + 2);
            
            $this->Ln(8);
        }
        
        // Page footer with enhanced design
        public function Footer() {
            $this->SetY(-25);
            // Add a decorative line
            $this->SetLineWidth(0.3);
            $this->SetDrawColor(33, 145, 80);
            $this->Line(15, $this->GetY() - 2, 195, $this->GetY() - 2);
            
            try {
                // Set footer fonts with fallback
                if (!$this->isValidFont('helvetica')) {
                    $this->SetFont('freesans', 'I', 9);
                    results_debug("Using freesans font for footer");
                } else {
                    $this->SetFont('helvetica', 'I', 9);
                    results_debug("Using helvetica font for footer");
                }
                $this->Cell(0, 10, 'Generated on: ' . date('F d, Y h:i A'), 0, 1, 'L');
                
                if (!$this->isValidFont('helvetica')) {
                    $this->SetFont('freesans', '', 9);
                } else {
                    $this->SetFont('helvetica', '', 9);
                }
                $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'C');
                $this->Cell(0, 10, 'E-Halal BTECHenyo', 0, 0, 'R');
                
                results_debug("Footer rendered successfully");
            } catch (Exception $e) {
                results_debug("ERROR rendering footer: " . $e->getMessage());
            }
        }
    }
}

// Include required files with dynamic base path
require_once $base_path . '/init.php';
require_once $base_path . '/classes/Database.php';
require_once $base_path . '/administrator/classes/Admin.php';
require_once $base_path . '/administrator/classes/Vote.php';
require_once $base_path . '/administrator/classes/Position.php';
require_once $base_path . '/administrator/classes/Candidate.php';
require_once $base_path . '/administrator/classes/Elections.php';

// Check if admin is logged in
$admin = Admin::getInstance();
if (!$admin->isLoggedIn()) {
    error_log("export_results.php: Unauthorized access");
    die('Unauthorized access');
}

// Initialize classes
$vote = Vote::getInstance();
$position = Position::getInstance();
$candidate = Candidate::getInstance();
$elections = Elections::getInstance();

// Get current election details
$current = $elections->getCurrentElection();
if (!$current) {
    die('No election data available');
}

try {
    // Start output buffering
    if (session_status() === PHP_SESSION_ACTIVE) {
        ob_start();
    }
    
    // Create new PDF document with error handling
    try {
        $pdf = new ElectionPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        results_debug("PDF instance created successfully");
    } catch (Exception $e) {
        results_debug("Error creating PDF instance: " . $e->getMessage());
        throw $e;
    }
    
    // Set document properties with error handling
    try {
        $pdf->setHeaderTitle('Official Election Results');
        $pdf->setElectionName($current['election_name']);
        
        $pdf->SetCreator('E-Halal BTECHenyo');
        $pdf->SetAuthor('Electoral Board');
        $pdf->SetTitle($current['election_name'] . ' - Results');
        results_debug("PDF properties set successfully");
    } catch (Exception $e) {
        results_debug("Error setting PDF properties: " . $e->getMessage());
        throw $e;
    }
    
    // Enhanced default properties
    $pdf->setHeaderFont(Array('helvetica', '', 12));
    $pdf->setFooterFont(Array('helvetica', '', 8));
    $pdf->SetDefaultMonospacedFont('courier');
    
    // Adjusted margins for better layout
    $pdf->SetMargins(15, 60, 15);
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(15);
    
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->setImageScale(1.25);
    
    // Add first page
    $pdf->AddPage();
    
    // Get all positions
    $positions = $position->getAllPositions();
    $totalVotes = $vote->getTotalVotes();
    
    // Process each position with enhanced styling
    foreach ($positions as $pos) {
        // Position header with improved design
        if ($pdf->isValidFont('helvetica')) {
            $pdf->SetFont('helvetica', 'B', 13);
        } else {
            $pdf->SetFont('freesans', 'B', 13);
        }
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(0, 10, $pos['description'], 0, 1, 'L', true);
        $pdf->Ln(1);
        
        // Create results table header with better styling
        if ($pdf->isValidFont('helvetica')) {
            $pdf->SetFont('helvetica', 'B', 10);
        } else {
            $pdf->SetFont('freesans', 'B', 10);
        }
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetTextColor(51, 51, 51);
        
        // Adjusted column widths for better proportion
        $pdf->Cell(75, 8, 'Candidate', 1, 0, 'L', true);
        $pdf->Cell(55, 8, 'Partylist', 1, 0, 'L', true);
        $pdf->Cell(25, 8, 'Votes', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Percentage', 1, 1, 'C', true);
        
        // Get and sort candidates
        $candidates = $vote->getVotesByPosition($pos['id']);
        usort($candidates, function($a, $b) {
            return $b['votes'] - $a['votes'];
        });
        
        // Get max winners for this position
        $maxWinners = (int)$pos['max_vote'];
        results_debug("Position {$pos['description']} has max_vote of {$maxWinners}");
        
        // Add candidate rows with alternating colors
        if ($pdf->isValidFont('helvetica')) {
            $pdf->SetFont('helvetica', '', 10);
        } else {
            $pdf->SetFont('freesans', '', 10);
        }
        $isAlternate = false;
        foreach ($candidates as $index => $candidate) {
            $votes = (int)$candidate['votes'];
            $percentage = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 2) : 0;
            
            // Highlight top N candidates based on max_vote, where N is max_vote
            // Only highlight if they have at least 1 vote
            if ($index < $maxWinners && $votes > 0) {
                if ($pdf->isValidFont('helvetica')) {
                    $pdf->SetFont('helvetica', 'B', 10);
                } else {
                    $pdf->SetFont('freesans', 'B', 10);
                }
                $pdf->SetFillColor(230, 255, 230); // Light green for winners
                $indexPlusOne = $index + 1;
                results_debug("Highlighting candidate " . $candidate['firstname'] . " " . $candidate['lastname'] . " as top " . $indexPlusOne . " of " . $maxWinners . " allowed winners");
            } else {
                if ($pdf->isValidFont('helvetica')) {
                    $pdf->SetFont('helvetica', '', 10);
                } else {
                    $pdf->SetFont('freesans', '', 10);
                }
                $pdf->SetFillColor($isAlternate ? 255 : 249, 249, 249);
            }
            
            $pdf->Cell(75, 8, $candidate['firstname'] . ' ' . $candidate['lastname'], 1, 0, 'L', true);
            $pdf->Cell(55, 8, $candidate['partylist_name'] ?? 'Independent', 1, 0, 'L', true);
            $pdf->Cell(25, 8, $votes, 1, 0, 'C', true);
            $pdf->Cell(25, 8, $percentage . '%', 1, 1, 'C', true);
            
            $isAlternate = !$isAlternate;
        }
        
        $pdf->Ln(4); // Reduced from 8 to 4
        
        // Add page if not enough space
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
        }
    }
    
    // Enhanced certification section - Always on a new page
    $pdf->AddPage();
    
    // Center the certification content vertically
    $pageHeight = $pdf->getPageHeight();
    $currentY = $pdf->GetY();
    $contentHeight = 100; // Approximate height of certification content
    $startY = max($currentY, ($pageHeight - $contentHeight) / 2);
    $pdf->SetY($startY);
    
    try {
        if ($pdf->isValidFont('helvetica')) {
            $pdf->SetFont('helvetica', 'B', 14);
        } else {
            $pdf->SetFont('freesans', 'B', 14);
        }
        $pdf->Cell(0, 10, 'CERTIFICATION', 0, 1, 'C');
        $pdf->SetDrawColor(33, 145, 80);
        $pdf->Line(40, $pdf->GetY(), 170, $pdf->GetY());
        $pdf->Ln(10);
        
        if ($pdf->isValidFont('helvetica')) {
            $pdf->SetFont('helvetica', '', 11);
        } else {
            $pdf->SetFont('freesans', '', 11);
        }
        $pdf->MultiCell(0, 8, 'I hereby certify that the above results are true and accurate based on the electronic votes cast in the E-Halal BTECHenyo.', 0, 'C');
        
        $pdf->Ln(30);
        
        // Enhanced signature line
        $pdf->Line(40, $pdf->GetY(), 170, $pdf->GetY());
        if ($pdf->isValidFont('helvetica')) {
            $pdf->SetFont('helvetica', 'B', 12);
        } else {
            $pdf->SetFont('freesans', 'B', 12);
        }
        $pdf->Cell(0, 10, 'Electoral Board Chairperson', 0, 1, 'C');
        
        results_debug("Certification section rendered successfully");
    } catch (Exception $e) {
        results_debug("ERROR rendering certification section: " . $e->getMessage());
    }
    
    // Output PDF
    if (isset($_GET['save']) && $_GET['save'] && isset($_GET['path'])) {
        // Clean output buffer
        if (session_status() === PHP_SESSION_ACTIVE) {
            ob_clean();
        }
        
        // Fix the path handling - keep original path for file saving but normalize for DB
        $originalPath = $_GET['path']; // Keep the original path for actual file saving
        $relativePath = $_GET['path']; // This will be normalized for database storage
        
        // Store a normalized version for the database
        if (strpos($relativePath, $_SERVER['DOCUMENT_ROOT']) === 0) {
            $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $relativePath);
        }
        
        // Remove any references to /e-halal/ in the path for database
        $relativePath = str_replace('/e-halal/', '/', $relativePath);
        
        // Normalize slashes for database
        $relativePath = preg_replace('#/+#', '/', $relativePath);
        
        // Ensure we're consistent with archives path for database
        if (strpos($relativePath, 'administrator/archives/') === 0) {
            $relativePath = str_replace('administrator/archives/', 'archives/', $relativePath);
        }
        
        // Store the normalized path for later use in the database
        $GLOBALS['normalized_results_path'] = ltrim($relativePath, '/');
        
        // For the actual file save, ensure it's a valid full path
        // If the original path doesn't exist, make sure to use a valid path
        if (!file_exists(dirname($originalPath))) {
            results_debug("Warning: Directory doesn't exist: " . dirname($originalPath));
            // Attempt to create directory if needed
            if (!mkdir(dirname($originalPath), 0777, true)) {
                results_debug("Error: Failed to create directory: " . dirname($originalPath));
                // Fallback to a known writable location
                $originalPath = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/archives/' . 
                    basename(dirname($originalPath)) . '/results.pdf';
                results_debug("Falling back to path: " . $originalPath);
                // Try to create this directory if it doesn't exist
                if (!file_exists(dirname($originalPath)) && !mkdir(dirname($originalPath), 0777, true)) {
                    results_debug("Error: Also failed to create fallback directory");
                    throw new Exception("Failed to create directory for PDF output");
                }
            }
        }
        
        results_debug("Saving PDF to physical path: " . $originalPath);
        try {
            $pdf->Output($originalPath, 'F'); // Save to file using original path
            if (!file_exists($originalPath)) {
                results_debug("Error: File was not created at: " . $originalPath);
                throw new Exception("Failed to create file at: " . $originalPath);
            }
            results_debug("PDF saved successfully. Normalized path for DB: " . $GLOBALS['normalized_results_path']);
        } catch (Exception $saveException) {
            results_debug("Error saving PDF: " . $saveException->getMessage());
            throw $saveException; // Re-throw to be caught by the outer try-catch
        }
    } else {
        // Clean output buffer
        if (session_status() === PHP_SESSION_ACTIVE) {
            ob_clean();
        }
        
        $pdf->Output($current['election_name'] . '_Results.pdf', 'I'); // Inline download
    }
} catch (Exception $e) {
    error_log('PDF Generation Error: ' . $e->getMessage());
    $GLOBALS['pdf_error'] = $e->getMessage();
    return;
}
