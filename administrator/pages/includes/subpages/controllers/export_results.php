<?php
// Enable error logging to file
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/e-halal/logs/php-errors.log');
error_log("export_results.php called: " . date('Y-m-d H:i:s'));

// Check if session is already started before calling session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/init.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/classes/Database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Admin.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Vote.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Position.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Candidate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Elections.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/vendor/tecnickcom/tcpdf/tcpdf.php';

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

// Custom PDF class with header and footer
class ElectionPDF extends TCPDF {
    protected $headerTitle;
    protected $electionName;
    
    public function setHeaderTitle($title) {
        $this->headerTitle = $title;
    }
    
    public function setElectionName($name) {
        $this->electionName = $name;
    }
    
    // Page header
    public function Header() {
        // Logo
        $leftLogo = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/assets/images/btech.png';
        $rightLogo = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/assets/images/ehalal.jpg';
        
        results_debug("Loading logos - Left: $leftLogo, Right: $rightLogo");
        
        // Check if logo files exist
        if (!file_exists($leftLogo)) {
            results_debug("WARNING: Left logo file not found: $leftLogo");
        }
        
        if (!file_exists($rightLogo)) {
            results_debug("WARNING: Right logo file not found: $rightLogo");
        }
        
        // Improved logo positioning and sizing
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
        
        // Enhanced header text with better spacing
        $this->SetFont('helvetica', 'B', 12);
        $this->SetY(8);
        $this->Cell(0, 6, 'Dalubhasaang Politekniko ng Lungsod ng Baliwag', 0, 1, 'C');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 4, 'The Official Electoral Board', 0, 1, 'C');
        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(0, 4, 'E-Halal BTECHenyo | Vote Wise BTECHenyos!', 0, 1, 'C');
        
        
        // Election name and title with improved spacing
        $this->Ln(6);
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(0, 8, $this->electionName, 0, 1, 'C');
        $this->SetFont('helvetica', 'B', 13);
        $this->Cell(0, 8, $this->headerTitle, 0, 1, 'C');
        
        // Add another decorative line
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
        
        $this->SetFont('helvetica', 'I', 9);
        $this->Cell(0, 10, 'Generated on: ' . date('F d, Y h:i A'), 0, 1, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'C');
        $this->Cell(0, 10, 'E-Halal BTECHenyo', 0, 0, 'R');
    }
}

// Add after the error logging section
// Add debug file for step-by-step tracking
function results_debug($message) {
    $logfile = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/logs/results_debug.log';
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

results_debug("Starting results PDF generation");

try {
    // Start output buffering
    if (session_status() === PHP_SESSION_ACTIVE) {
        ob_start();
    }
    
    // Create new PDF document
    $pdf = new ElectionPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document properties
    $pdf->setHeaderTitle('Official Election Results');
    $pdf->setElectionName($current['election_name']);
    
    $pdf->SetCreator('E-Halal BTECHenyo');
    $pdf->SetAuthor('Electoral Board');
    $pdf->SetTitle($current['election_name'] . ' - Results');
    
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
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(0, 10, $pos['description'], 0, 1, 'L', true);
        $pdf->Ln(1);
        
        // Create results table header with better styling
        $pdf->SetFont('helvetica', 'B', 10);
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
        
        // Add candidate rows with alternating colors
        $pdf->SetFont('helvetica', '', 10);
        $isAlternate = false;
        foreach ($candidates as $candidate) {
            $votes = (int)$candidate['votes'];
            $percentage = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 2) : 0;
            
            // Highlight winner(s) with green background
            if ($votes === max(array_column($candidates, 'votes')) && $votes > 0) {
                $pdf->SetFont('helvetica', 'B', 10);
                $pdf->SetFillColor(230, 255, 230);
            } else {
                $pdf->SetFont('helvetica', '', 10);
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
    
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'CERTIFICATION', 0, 1, 'C');
    $pdf->SetDrawColor(33, 145, 80);
    $pdf->Line(40, $pdf->GetY(), 170, $pdf->GetY());
    $pdf->Ln(10);
    
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(0, 8, 'I hereby certify that the above results are true and accurate based on the electronic votes cast in the E-Halal BTECHenyo.', 0, 'C');
    
    $pdf->Ln(30);
    
    // Enhanced signature line
    $pdf->Line(40, $pdf->GetY(), 170, $pdf->GetY());
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Electoral Board Chairperson', 0, 1, 'C');
    
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
