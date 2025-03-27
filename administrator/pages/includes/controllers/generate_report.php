<?php
session_start();
require_once '../../../../init.php';  // Load configurations
require_once '../../../../classes/Database.php';
require_once '../../../../classes/Admin.php';
require_once '../../../../classes/Vote.php';
require_once '../../../../classes/Position.php';
require_once '../../../../classes/Candidate.php';
require_once '../../../../tcpdf/tcpdf.php';

// Check if admin is logged in
$admin = Admin::getInstance();
if (!$admin->isLoggedIn()) {
    die('Unauthorized access');
}

// Initialize classes
$vote = Vote::getInstance();
$position = Position::getInstance();
$candidate = Candidate::getInstance();

// Get election name from config
$parse = parse_ini_file('../../../../administrator/config.ini', FALSE, INI_SCANNER_RAW);
$title = $parse['election_name'];

// Custom TCPDF class with header and footer
class MYPDF extends TCPDF {
    public $isFirstPage = true;

    // Page header
    public function Header() {
        if ($this->getPage() > 1) {
            $this->SetMargins(PDF_MARGIN_LEFT, 30, PDF_MARGIN_RIGHT, true);
        } else {
            $this->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT, true);
        }
        // Logo
        $this->Image('../../../../images/btech.png', 25, 5, 25);
        $this->Image('../../../../images/ehalal.jpg', 165, 8, 20);
        // Set font
        $this->SetFont('helvetica', 'B', 12);
        // Title
        $this->Cell(0, 26, 'Dalubhasaang Politekniko ng Lungsod ng Baliwag', 0, 1, 'C');
        $this->SetFont('helvetica', '', 10);
        $this->SetY(18);
        $this->Cell(0, 0, 'The Official Electoral Board', 0, 1, 'C');
        $this->Cell(0, 0, 'E-Halal BTECHenyo | Vote Wise BTECHenyos!', 0, 1, 'C');

        // Reset the flag after the first page
        $this->isFirstPage = false;
    }

    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
    }
}

try {
    // Create new PDF document
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Result: '.$title);  
    $pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);  
    $pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT);  
    $pdf->setPrintHeader(true);  
    $pdf->setPrintFooter(true);  
    $pdf->SetAutoPageBreak(TRUE, 10);  
    $pdf->SetFont('helvetica', '', 11);  

    // Add a page
    $pdf->AddPage();  

    // Initial content
    $content = '';  
    $content .= '
        <br><br><br><br>
        <h2 align="center" style="line-height: 19px">'.$title.'</h2>
        <h4 align="center">Tally Result</h4>
    ';  

    // Generate content for each position
    $sql = "SELECT * FROM positions ORDER BY priority ASC";
    $query = $vote->getConnection()->query($sql);

    while ($position = $query->fetch_assoc()) {
        $content .= '
            <h4 align="left" style="font-size: 14px; font-weight: bold; padding: 5px;">' . $position['description'] . '</h4>
            <table border="1" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #e0f7fa; font-weight: bold;">
                        <th width="80%">Candidate Name</th>
                        <th width="20%">Votes</th>
                    </tr>
                </thead>
                <tbody>
        ';

        // Get candidates for this position
        $position_id = $position['id'];
        $sql = "SELECT candidates.*, partylists.name AS partylist_name 
                FROM candidates 
                LEFT JOIN partylists ON candidates.partylist_id = partylists.id 
                WHERE position_id = '$position_id' 
                ORDER BY lastname ASC";
        $cquery = $vote->getConnection()->query($sql);
        
        $candidates = [];
        $maxVotes = 0;

        while ($candidate = $cquery->fetch_assoc()) {
            $sql = "SELECT * FROM votes WHERE candidate_id = '" . $candidate['id'] . "'";
            $vquery = $vote->getConnection()->query($sql);
            $votes = $vquery->num_rows;
            
            $candidates[] = [
                'name' => $candidate['lastname'] . ", " . $candidate['firstname'],
                'partylist' => $candidate['partylist_name'],
                'votes' => $votes
            ];
            if ($votes > $maxVotes) {
                $maxVotes = $votes;
            }
        }

        // Add candidate rows
        foreach ($candidates as $candidate) {
            $highlight = ($candidate['votes'] == $maxVotes) ? ' style="background-color:yellow;"' : '';
            $content .= '
                <tr' . $highlight . '>
                    <td width="80%">' . $candidate['partylist'] . ' — ' . $candidate['name'] . '</td>
                    <td width="20%">' . $candidate['votes'] . '</td>
                </tr>
            ';
        }

        $content .= '
                </tbody>
            </table>
            <br/>
        ';
    }

    // Write the content to PDF
    $pdf->writeHTML($content);  

    // Clean output buffer
    ob_clean();

    // Output PDF
    $pdf->Output('election_result.pdf', 'I');

} catch (Exception $e) {
    error_log('PDF Generation Error: ' . $e->getMessage());
    die('Error generating PDF: ' . $e->getMessage());
} 