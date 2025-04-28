<?php
// Check if script is being run directly for testing/debugging
$is_standalone_test = (php_sapi_name() === 'cli' && isset($argv) && basename($argv[0]) === basename(__FILE__));

// Check if session is already started before calling session_start()
if (session_status() === PHP_SESSION_NONE && !$is_standalone_test) {
session_start();
}

// Create logs directory if it doesn't exist
$logs_dir = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/logs';
if (!file_exists($logs_dir)) {
    mkdir($logs_dir, 0777, true);
}

// Define base path for the project
$base_path = dirname(dirname(dirname(dirname(dirname(__DIR__)))));

// Immediately after the "enable error logging" section
// Add debug file for step-by-step tracking
function summary_debug($message) {
    global $base_path;
    $logfile = $base_path . '/logs/summary_debug.log';
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Function to check if TCPDF class exists
function check_tcpdf_exists() {
    global $base_path;
    $tcpdf_path = $base_path . '/vendor/tecnickcom/tcpdf/tcpdf.php';
    
    if (!file_exists($tcpdf_path)) {
        summary_debug("ERROR: TCPDF not found at expected path: $tcpdf_path");
        return false;
    }
    
    require_once $tcpdf_path;
    if (!class_exists('TCPDF')) {
        summary_debug("ERROR: TCPDF class not found after including file");
        return false;
    }
    
    summary_debug("TCPDF class loaded successfully");
    return true;
}

// Enable error logging to file
ini_set('log_errors', 1);
ini_set('error_log', $base_path . '/logs/php-errors.log');
error_log("export_summary.php called: " . date('Y-m-d H:i:s'));

summary_debug("Starting summary PDF generation");

// Check for TCPDF
if (!check_tcpdf_exists()) {
    echo "Error: TCPDF library not found or not properly installed.\n";
    exit(1);
}

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

    // Page header
    public function Header() {
            global $base_path;
            
        // Logo
            $leftLogo = $base_path . '/administrator/assets/images/btech.png';
            $rightLogo = $base_path . '/administrator/assets/images/ehalal.jpg';
            
            summary_debug("Loading logos - Left: $leftLogo, Right: $rightLogo");
            
            // Check if logo files exist
            if (!file_exists($leftLogo)) {
                summary_debug("WARNING: Left logo file not found: $leftLogo");
                // Try alternative path
        $leftLogo = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/assets/images/btech.png';
                if (!file_exists($leftLogo)) {
                    summary_debug("WARNING: Left logo also not found at alternative path");
                } else {
                    summary_debug("Left logo found at alternative path");
                }
            }
            
            if (!file_exists($rightLogo)) {
                summary_debug("WARNING: Right logo file not found: $rightLogo");
                // Try alternative path
        $rightLogo = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/assets/images/ehalal.jpg';
                if (!file_exists($rightLogo)) {
                    summary_debug("WARNING: Right logo also not found at alternative path");
                } else {
                    summary_debug("Right logo found at alternative path");
                }
            }
        
        // Improved logo positioning and sizing
            try {
                if (file_exists($leftLogo)) {
        $this->Image($leftLogo, 30, 8, 20);
                    summary_debug("Left logo loaded successfully");
                }
                
                if (file_exists($rightLogo)) {
        $this->Image($rightLogo, 165, 8, 15);
                    summary_debug("Right logo loaded successfully");
                }
                summary_debug("Logos loaded successfully");
            } catch (Exception $e) {
                summary_debug("ERROR loading images: " . $e->getMessage());
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
        
        $this->SetFont('helvetica', 'I', 9);
        $this->Cell(0, 10, 'Generated on: ' . date('F d, Y h:i A'), 0, 1, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'C');
        $this->Cell(0, 10, 'E-Halal BTECHenyo', 0, 0, 'R');
    }
    }
}

if ($is_standalone_test) {
    // Standalone test mode - create mock classes and data
    summary_debug("Running in standalone test mode");
    
    // Define mock classes for testing - do NOT redefine ElectionPDF which is already defined above
    
    // Mock data classes
    class MockAdmin {
        public static $instance;
        public static function getInstance() {
            if (!self::$instance) self::$instance = new self();
            return self::$instance;
        }
        public function isLoggedIn() { return true; }
    }
    
    class MockVote {
        public static $instance;
        public static function getInstance() {
            if (!self::$instance) self::$instance = new self();
            return self::$instance;
        }
        public function getVotingStatistics() {
            return [
                'total_voters' => 100, 
                'voted' => 75, 
                'not_voted' => 25, 
                'percentage' => 75
            ];
        }
        public function getVotesByPosition($positionId) {
            $mockCandidates = [
                [
                    'id' => 1, 
                    'firstname' => 'John', 
                    'lastname' => 'Smith', 
                    'partylist_name' => 'Alpha', 
                    'votes' => 30
                ],
                [
                    'id' => 2, 
                    'firstname' => 'Jane', 
                    'lastname' => 'Doe', 
                    'partylist_name' => 'Beta', 
                    'votes' => 25
                ],
                [
                    'id' => 3, 
                    'firstname' => 'Bob', 
                    'lastname' => 'Jones', 
                    'partylist_name' => 'Gamma', 
                    'votes' => 20
                ]
            ];
            summary_debug("Mock candidates returned for position $positionId");
            return $mockCandidates;
        }
    }
    
    class MockPosition {
        public static $instance;
        public static function getInstance() {
            if (!self::$instance) self::$instance = new self();
            return self::$instance;
        }
        public function getAllPositions() {
            return [
                ['id' => 1, 'description' => 'President', 'max_vote' => 1],
                ['id' => 2, 'description' => 'Vice President', 'max_vote' => 1],
                ['id' => 3, 'description' => 'Secretary', 'max_vote' => 1]
            ];
        }
    }
    
    class MockCandidate {
        public static $instance;
        public static function getInstance() {
            if (!self::$instance) self::$instance = new self();
            return self::$instance;
        }
    }
    
    class MockElections {
        public static $instance;
        public static function getInstance() {
            if (!self::$instance) self::$instance = new self();
            return self::$instance;
        }
        public function getCurrentElection() {
            return [
                'id' => 1,
                'election_name' => 'Test Election 2025',
                'control_number' => 'E-2025-TEST',
                'created_at' => '2025-03-01 08:00:00',
                'end_time' => '2025-03-05 17:00:00'
            ];
        }
    }
    
    class MockVoter {
        public static $instance;
        public static function getInstance() {
            if (!self::$instance) self::$instance = new self();
            return self::$instance;
        }
        public function getVoterCount() { return 100; }
        public function getVotersWhoVoted() { return 75; }
    }
    
    // Set up mock instances
    summary_debug("Creating mock instances for testing");
    $admin = MockAdmin::getInstance();
    $vote = MockVote::getInstance();
    $position = MockPosition::getInstance();
    $candidate = MockCandidate::getInstance();
    $elections = MockElections::getInstance();
    $voter = MockVoter::getInstance();
    $current = $elections->getCurrentElection();
    
    // Load TCPDF library directly
    require_once $base_path . '/vendor/tecnickcom/tcpdf/tcpdf.php';
} else {
    // Normal mode - use actual classes
    summary_debug("Running in normal mode - including required files");
    
    // Use try/catch for includes to identify which one fails
    try {
        // Fix file paths to use relative paths instead of document root
        require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/init.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/classes/Database.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Admin.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Vote.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Position.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Candidate.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Elections.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/classes/Voter.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/e-halal/vendor/tecnickcom/tcpdf/tcpdf.php';
        
        summary_debug("All includes loaded successfully (document root method)");
    } catch (Error $e) {
        summary_debug("Error loading includes (document root method): " . $e->getMessage());
        
        // Fall back to relative path if the document root path doesn't work
        try {
            // Try to fix the path issue by using __DIR__ for a more reliable path resolution
            $base_path = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
            
            require_once $base_path . '/init.php';
            require_once $base_path . '/classes/Database.php';
            require_once $base_path . '/administrator/classes/Admin.php';
            require_once $base_path . '/administrator/classes/Vote.php';
            require_once $base_path . '/administrator/classes/Position.php';
            require_once $base_path . '/administrator/classes/Candidate.php';
            require_once $base_path . '/administrator/classes/Elections.php';
            require_once $base_path . '/administrator/classes/Voter.php';
            require_once $base_path . '/vendor/tecnickcom/tcpdf/tcpdf.php';
            
            summary_debug("All includes loaded successfully (relative path method)");
        } catch (Error $e2) {
            summary_debug("ERROR: Failed to load includes: " . $e2->getMessage());
            die("Failed to load required files: " . $e2->getMessage());
        }
    }
    
    // Check if admin is logged in
    $admin = Admin::getInstance();
    if (!$admin->isLoggedIn()) {
        die('Unauthorized access');
    }
    
    // Initialize classes
    $vote = Vote::getInstance();
    $position = Position::getInstance();
    $candidate = Candidate::getInstance();
    $elections = Elections::getInstance();
    $voter = Voter::getInstance();
    
    // Get current election details
    $current = $elections->getCurrentElection();
    if (!$current) {
        die('No election data available');
    }
}

summary_debug("Starting summary PDF generation");

/**
 * Calculate voter participation statistics
 * @param Voter $voter The voter instance to use for database access
 * @return array Associative array with participation statistics
 */
function calculateVoterParticipation($voter) {
    // Get total voters count using Voter class method
    $totalVoters = $voter->getVoterCount();
    
    // Get voted count using the Voter class method
    $votedCount = $voter->getVotersWhoVoted();
    
    // Calculate not voted count
    $notVotedCount = $totalVoters - $votedCount;
    
    // Calculate participation rate (percentage)
    $participationRate = $totalVoters > 0 ? round(($votedCount / $totalVoters) * 100, 1) : 0;
    
    return [
        'total_voters' => $totalVoters,
        'voted_count' => $votedCount,
        'not_voted_count' => $notVotedCount,
        'participation_rate' => $participationRate
    ];
}

try {
    summary_debug("Creating new ElectionPDF instance");
    // Create new PDF document with enhanced settings
    $pdf = new ElectionPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Increase memory limit to handle large PDFs
    ini_set('memory_limit', '256M');
    
    summary_debug("Setting document properties");
    // Set document properties
    $pdf->setHeaderTitle('Election Summary Report');
    $pdf->setElectionName($current['election_name']);
    
    $pdf->SetCreator('E-Halal BTECHenyo');  
    $pdf->SetAuthor('Electoral Board');
    $pdf->SetTitle($current['election_name'] . ' - Summary');  
    
    summary_debug("Setting font and margin properties");
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
    
    // Add progress delay for better user experience with progress feedback
    if (!$is_standalone_test) {
        // Start output buffering to capture any output before PDF generation
        ob_start();
        
        // Only use SSE when explicitly requested via query param
        if (isset($_GET['progress']) && $_GET['progress'] == 'true') {
            // Send headers to prevent browser timeout
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            flush();
            
            // Output progress message
            echo "data: Starting PDF generation...\n\n";
            flush();
            // Small delay to show progress
            usleep(500000); // 0.5 second
        }
    }
    
    summary_debug("Adding first page");
    // Add first page
    $pdf->AddPage();
    
    // Progress update
    if (!$is_standalone_test && isset($_GET['progress']) && $_GET['progress'] == 'true') {
        echo "data: Creating election information section...\n\n";
        flush();
        usleep(300000); // 0.3 second
    }
    
    summary_debug("Creating election information section");
    // Election Information Section with enhanced styling
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(0, 10, 'Election Information', 0, 1, 'L', true);
    $pdf->Ln(1);
    
    // Create info table with improved styling
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(240, 240, 240);
    
    summary_debug("Creating info table data");
    // Info table data with better layout
    $infoData = array(
        array('Control Number', $current['control_number']),
        array('Status', 'Completed'),
        array('Start Date', date('F d, Y h:i A', strtotime($current['created_at']))),
        array('End Date', date('F d, Y h:i A', strtotime($current['end_time'])))
    );
    
    summary_debug("Rendering info table");
    foreach ($infoData as $i => $row) {
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor($i % 2 == 0 ? 249 : 255, 249, 249);
        $pdf->Cell(45, 8, $row[0], 1, 0, 'L', true);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(130, 8, $row[1], 1, 1, 'L', true);
    }
    
    $pdf->Ln(4);
    
    // Progress update
    if (!$is_standalone_test && isset($_GET['progress']) && $_GET['progress'] == 'true') {
        echo "data: Creating voter participation section...\n\n";
        flush();
        usleep(300000); // 0.3 second
    }
    
    summary_debug("Creating voter participation section");
    // Voter Participation Section with enhanced styling
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(0, 10, 'Voter Participation', 0, 1, 'L', true);
    $pdf->Ln(1);
    
    summary_debug("Getting voting statistics");
    // Get voting statistics
    $voteStats = $vote->getVotingStatistics();
    
    summary_debug("Creating participation table");
    // Create participation table with improved styling
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->SetTextColor(51, 51, 51);
    $pdf->Cell(100, 8, 'Category', 1, 0, 'L', true);
    $pdf->Cell(75, 8, 'Count', 1, 1, 'C', true);
    
    $pdf->SetFont('helvetica', '', 10);
    $participationData = array(
        array('Total Eligible Voters', $voteStats['total_voters']),
        array('Total Votes Cast', $voteStats['voted']),
        array('Did Not Vote', $voteStats['not_voted']),
        array('Voter Turnout', $voteStats['percentage'] . '%')
    );
    
    summary_debug("Rendering participation table");
    foreach ($participationData as $i => $row) {
        $pdf->SetFillColor($i % 2 == 0 ? 249 : 255, 249, 249);
        $pdf->Cell(100, 8, $row[0], 1, 0, 'L', true);
        if ($i == 3) { // Highlight turnout percentage
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(33, 145, 80);
        }
        $pdf->Cell(75, 8, $row[1], 1, 1, 'C', true);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(51, 51, 51);
    }
    
    $pdf->Ln(4);
    
    // Progress update
    if (!$is_standalone_test && isset($_GET['progress']) && $_GET['progress'] == 'true') {
        echo "data: Creating elected officers section...\n\n";
        flush();
        usleep(300000); // 0.3 second
    }
    
    summary_debug("Creating elected officers section");
    // Elected Officers Section with enhanced styling
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(0, 10, 'Elected Officers', 0, 1, 'L', true);
    $pdf->Ln(1);
    
    summary_debug("Creating winners table");
    // Create winners table with improved styling
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Cell(60, 8, 'Position', 1, 0, 'L', true);
    $pdf->Cell(70, 8, 'Candidate', 1, 0, 'L', true);
    $pdf->Cell(45, 8, 'Partylist', 1, 1, 'L', true);
    
    summary_debug("Getting positions data");
    // Get all positions
    $positions = $position->getAllPositions();
    $isAlternate = false;
    
    summary_debug("Creating winners table rows - positions count: " . count($positions));
    foreach ($positions as $pos) {
        summary_debug("Processing position ID: " . $pos['id'] . " - " . $pos['description']);
        $candidates = $vote->getVotesByPosition($pos['id']);
        
        if (!empty($candidates)) {
            summary_debug("Position has " . count($candidates) . " candidates");
            // Sort by votes (descending)
            usort($candidates, function($a, $b) {
                return $b['votes'] - $a['votes'];
            });
            
            // Get max winners for this position
            $maxWinners = (int)$pos['max_vote'];
            summary_debug("Position {$pos['description']} has max_vote of {$maxWinners}");
            
            // Take only top N candidates where N = max_vote
            foreach ($candidates as $index => $cand) {
                if ($index < $maxWinners && $cand['votes'] > 0) {
                    $pdf->SetFillColor($isAlternate ? 249 : 255, 249, 249);
                    
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(60, 8, $pos['description'], 1, 0, 'L', true);
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->Cell(70, 8, $cand['firstname'] . ' ' . $cand['lastname'], 1, 0, 'L', true);
                    $pdf->Cell(45, 8, $cand['partylist_name'] ?? 'Independent', 1, 1, 'L', true);
                    
                    $isAlternate = !$isAlternate;
                    summary_debug("Added winner: " . $cand['firstname'] . ' ' . $cand['lastname'] . " (position " . ($index + 1) . " of " . $maxWinners . ")");
                }
            }
        } else {
            summary_debug("Position has no candidates");
        }
    }
    
    $pdf->Ln(4);
    
    // Progress update
    if (!$is_standalone_test && isset($_GET['progress']) && $_GET['progress'] == 'true') {
        echo "data: Creating detailed vote statistics section...\n\n";
        flush();
        usleep(500000); // 0.5 second
    }
    
    summary_debug("Creating detailed vote statistics section");
    // Add a detailed vote statistics section that shows all candidates and their votes
    $pdf->AddPage();
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(0, 10, 'Detailed Vote Statistics', 0, 1, 'L', true);
    $pdf->Ln(1);
    
    // Loop through each position
    $positions = $position->getAllPositions();
    foreach ($positions as $pos) {
        summary_debug("Processing detailed stats for position ID: " . $pos['id'] . " - " . $pos['description']);
        
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
        
        // Get candidates for this position and sort by votes (highest first)
        try {
            // Get candidates for this position
            $candidates = $vote->getVotesByPosition($pos['id']);
            summary_debug("Retrieved " . (is_array($candidates) ? count($candidates) : 0) . " candidates for position " . $pos['id']);
            
            if (is_array($candidates) && !empty($candidates)) {
                summary_debug("Candidate data structure: " . json_encode(array_keys($candidates[0])));
                
                // Sort candidates by votes (highest first)
                usort($candidates, function($a, $b) {
                    return (isset($b['votes']) ? $b['votes'] : 0) - (isset($a['votes']) ? $a['votes'] : 0);
                });
                
                // Calculate total votes for this position to get percentages
                $totalVotesForPosition = 0;
                foreach ($candidates as $cand) {
                    $totalVotesForPosition += (isset($cand['votes']) ? $cand['votes'] : 0);
                }
                
                // Add candidate rows with alternating colors
                $pdf->SetFont('helvetica', '', 10);
                $isAlternate = false;
                
                foreach ($candidates as $index => $cand) {
                    // Add debug logging to inspect candidate data
                    summary_debug("Processing candidate: " . json_encode($cand));
                    
                    // Add null checks for all array accesses to prevent undefined index errors
                    $firstName = isset($cand['firstname']) ? $cand['firstname'] : '';
                    $lastName = isset($cand['lastname']) ? $cand['lastname'] : '';
                    $partylist = isset($cand['partylist_name']) ? $cand['partylist_name'] : 'Independent';
                    $votes = isset($cand['votes']) ? $cand['votes'] : 0;
                    
                    $votePercentage = ($totalVotesForPosition > 0) ? 
                        round(($votes / $totalVotesForPosition) * 100, 1) : 0;
                    
                    // Highlight top N candidates based on max_vote
                    if ($index < $pos['max_vote'] && $votes > 0) {
                        $pdf->SetFont('helvetica', 'B', 10);
                        $pdf->SetFillColor(230, 255, 230); // Light green for winners
                        summary_debug("Highlighting candidate " . $firstName . " " . $lastName . " as top " . ($index + 1) . " of " . $pos['max_vote'] . " allowed winners");
                    } else {
                        $pdf->SetFont('helvetica', '', 10);
                        $pdf->SetFillColor($isAlternate ? 255 : 249, 249, 249);
                    }
                    
                    $pdf->Cell(75, 8, $firstName . ' ' . $lastName, 1, 0, 'L', true);
                    $pdf->Cell(55, 8, $partylist, 1, 0, 'L', true);
                    $pdf->Cell(25, 8, $votes, 1, 0, 'C', true);
                    $pdf->Cell(25, 8, $votePercentage . '%', 1, 1, 'C', true);
                    
                    // Reset text color (in case it was changed)
                    $pdf->SetTextColor(51, 51, 51);
                    $isAlternate = !$isAlternate;
                }
            } else {
                summary_debug("No candidates found for position " . $pos['id']);
                $pdf->SetFont('helvetica', 'I', 10);
                $pdf->Cell(0, 8, 'No candidates for this position', 1, 1, 'C', true);
            }
        } catch (Exception $e) {
            summary_debug("ERROR getting candidates: " . $e->getMessage());
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 8, 'Error retrieving candidate data', 1, 1, 'C', true);
        }
        
        $pdf->Ln(4); // Reduced spacing between positions
        
        // Add page if not enough space
        if ($pdf->GetY() > 250) {
            $pdf->AddPage();
        }
    }
    
    // Progress update
    if (!$is_standalone_test && isset($_GET['progress']) && $_GET['progress'] == 'true') {
        echo "data: Creating certification section...\n\n";
        flush();
        usleep(300000); // 0.3 second
    }
    
    summary_debug("Creating certification section");
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
    $pdf->MultiCell(0, 8, 'I hereby certify that the above information is true and accurate based on the records of the election.', 0, 'C');
    
    $pdf->Ln(30);
    
    // Enhanced signature line
    $pdf->Line(40, $pdf->GetY(), 170, $pdf->GetY());
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Electoral Board Chairperson', 0, 1, 'C');

    summary_debug("Generating output PDF");
    // Output PDF
    if ($is_standalone_test) {
        // In standalone test mode, always output to file
        $output_file = $base_path . '/test_summary.pdf';
        summary_debug("Saving test PDF to: " . $output_file);
        $pdf->Output($output_file, 'F');
        summary_debug("TEST PDF saved successfully to: " . $output_file);
        echo "PDF created successfully at: " . $output_file . "\n";
    } else if (isset($_GET['save']) && $_GET['save'] && isset($_GET['path'])) {
        // When saving to file (used for archiving)
        
        // For progress updates
        if (isset($_GET['progress']) && $_GET['progress'] == 'true') {
            echo "data: Saving PDF file...\n\n";
            flush();
            usleep(500000); // 0.5 second
        }
        
        // Clean the output buffer to prevent "some data has already been output" error
        if (!$is_standalone_test) {
            // Discard any buffered content
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
        
        // Get the file name from the path
        $fileName = basename($relativePath);
        $dirPath = dirname($relativePath);
        
        // Make sure we have a valid file name (use summary.pdf if needed)
        if ($fileName === 'details.pdf') {
            $relativePath = $dirPath . '/summary.pdf';
            summary_debug("Renamed details.pdf to summary.pdf for database storage");
        }
        
        // Store the normalized path for later use in the database
        $GLOBALS['normalized_summary_path'] = ltrim($relativePath, '/');
        
        // For the actual file save, ensure it's a valid full path
        // If the original path doesn't exist, make sure to use a valid path
        if (!file_exists(dirname($originalPath))) {
            summary_debug("Warning: Directory doesn't exist: " . dirname($originalPath));
            // Attempt to create directory if needed
            if (!mkdir(dirname($originalPath), 0777, true)) {
                summary_debug("Error: Failed to create directory: " . dirname($originalPath));
                // Fallback to a known writable location
                $originalPath = $_SERVER['DOCUMENT_ROOT'] . '/e-halal/administrator/archives/' . $current['control_number'] . '/summary.pdf';
                summary_debug("Falling back to path: " . $originalPath);
                // Try to create this directory if it doesn't exist
                if (!file_exists(dirname($originalPath)) && !mkdir(dirname($originalPath), 0777, true)) {
                    summary_debug("Error: Also failed to create fallback directory");
                    throw new Exception("Failed to create directory for PDF output");
                }
            }
        }
        
        // Rename the file if needed in the original path too
        if (basename($originalPath) === 'details.pdf') {
            $originalPath = dirname($originalPath) . '/summary.pdf';
        }
        
        summary_debug("Saving PDF to physical path: " . $originalPath);
        try {
            $pdf->Output($originalPath, 'F'); // Save to file using original path
            if (!file_exists($originalPath)) {
                summary_debug("Error: File was not created at: " . $originalPath);
                throw new Exception("Failed to create file at: " . $originalPath);
            }
            summary_debug("PDF saved successfully. Normalized path for DB: " . $GLOBALS['normalized_summary_path']);
        } catch (Exception $saveException) {
            summary_debug("Error saving PDF: " . $saveException->getMessage());
            throw $saveException; // Re-throw to be caught by the outer try-catch
        }
        
        // Final progress update
        if (isset($_GET['progress']) && $_GET['progress'] == 'true') {
            echo "data: PDF generation complete!\n\n";
            flush();
        }
    } else {
        // When displaying in browser
        
        // For progress updates
        if (isset($_GET['progress']) && $_GET['progress'] == 'true') {
            echo "data: Preparing PDF for download...\n\n";
            flush();
            usleep(500000); // 0.5 second
        }
        
        // Clean the output buffer to prevent "some data has already been output" error
        if (!$is_standalone_test) {
            // Discard any buffered content
            ob_clean();
        }
        
        summary_debug("Sending PDF for download");
        $pdf->Output($current['election_name'] . '_Summary.pdf', 'I'); // Inline download
        summary_debug("PDF download initiated");
    }
    summary_debug("PDF generation complete");

    if (!$is_standalone_test) {
        if (isset($_GET['progress']) && $_GET['progress'] == 'true') {
            echo "data: PDF generation successful!\n\n";
            flush();
        }
    } else {
        echo "PDF generation successful!\n";
    }
    
} catch (Exception $e) {
    summary_debug("ERROR: " . $e->getMessage());
    summary_debug("Stack trace: " . $e->getTraceAsString());
    error_log('PDF Generation Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    $GLOBALS['pdf_error'] = $e->getMessage();
    
    // Instead of just returning silently, output an error message
    if ($is_standalone_test) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "Check logs for more details.\n";
    } else {
        echo "<h2>PDF Generation Error</h2>";
        echo "<p>An error occurred while generating the PDF: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>Please check the error logs for more details.</p>";
    }
    exit(1);
}
