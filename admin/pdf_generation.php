<?php
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

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
        $this->Image('../images/btech.png', 25, 5, 25);
        $this->Image('../images/ehalal.jpg', 165, 8, 20);
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

function generatePdf($html, $filePath) {
    $pdf = new MYPDF();
    $pdf->SetMargins(10, 30, 10); // Set margins: left, top, right
    $pdf->SetHeaderMargin(10); // Set header margin
    $pdf->SetFooterMargin(15); // Set footer margin
    $pdf->AddPage();
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($filePath, 'F');
}

function generateDetailsHtml($conn, $election_id) {
    // Fetch election details from the database
    $sql = "SELECT * FROM election_status WHERE id = $election_id";
    $query = $conn->query($sql);
    $election = $query->fetch_assoc();

    $html = '<h1>Election Details</h1>';
    $html .= '<p><b>Election Name:</b> ' . $election['election_name'] . '</p>';
    $html .= '<p><b>Election Date:</b> ' . $election['start_time'] . ' to ' . $election['end_time'] . '</p>';

    // Fetch positions and candidates
    $html .= '<h2>Positions</h2>';
    $sql = "SELECT * FROM positions ORDER BY priority ASC";
    $query = $conn->query($sql);
    while ($position = $query->fetch_assoc()) {
        $html .= '<h3>' . $position['description'] . '</h3>';
        $html .= '<table border="1" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr style="background-color: #e0f7fa;"><th>Candidate Name</th><th>Partylist</th></tr></thead>';
        $html .= '<tbody>';
        $sql = "SELECT candidates.*, partylists.name AS partylist_name 
                FROM candidates 
                LEFT JOIN partylists ON candidates.partylist_id = partylists.id 
                WHERE position_id = " . $position['id'] . " 
                ORDER BY lastname ASC";
        $cquery = $conn->query($sql);
        while ($candidate = $cquery->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td>' . $candidate['firstname'] . ' ' . $candidate['lastname'] . '</td>';
            $html .= '<td>' . $candidate['partylist_name'] . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
    }
    $html .= '<hr>';

    // Fetch partylists
    $html .= '<h2>Partylists</h2>';
    $html .= '<table border="1" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">';
    $html .= '<thead><tr style="background-color: #e0f7fa;"><th>Partylist Name</th><th>Number of Candidates</th></tr></thead>';
    $html .= '<tbody>';
    $sql = "SELECT partylists.name AS partylist_name, COUNT(candidates.id) AS num_candidates 
            FROM partylists 
            LEFT JOIN candidates ON candidates.partylist_id = partylists.id 
            GROUP BY partylists.id 
            ORDER BY partylists.name ASC";
    $query = $conn->query($sql);
    while ($partylist = $query->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . $partylist['partylist_name'] . '</td>';
        $html .= '<td>' . $partylist['num_candidates'] . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    // Fetch voters
    $html .= '<h2>All Voters Registered</h2>';
    $sql = "SELECT voters.*, courses.description AS description 
            FROM voters 
            LEFT JOIN courses ON voters.course_id = courses.id 
            ORDER BY lastname ASC";
    $query = $conn->query($sql);
    $html .= '<table border="1" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">';
    $html .= '<thead><tr style="background-color: #e0f7fa;"><th>Voter ID</th><th>Name</th><th>Course</th></tr></thead>';
    $html .= '<tbody>';
    while ($voter = $query->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . $voter['voters_id'] . '</td>';
        $html .= '<td>' . $voter['firstname'] . ' ' . $voter['lastname'] . '</td>';
        $html .= '<td>' . $voter['description'] . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    // Fetch admin users
    $html .= '<h2>Officers Registered</h2>';
    $sql = "SELECT * FROM admin ORDER BY lastname ASC";
    $query = $conn->query($sql);
    $html .= '<table border="1" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">';
    $html .= '<thead><tr style="background-color: #e0f7fa;"><th>Username</th><th>Officer Name</th></tr></thead>';
    $html .= '<tbody>';
    while ($admin = $query->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . $admin['username'] . '</td>';
        $html .= '<td>' . $admin['firstname'] . ' ' . $admin['lastname'] . '</td>';
        $html .= '</tr>';
    }
    $html .= '</tbody></table>';

    return $html;
}

function generateResultsHtml($conn, $election_id) {
    // Fetch election results from the database
    $html = '<h1>Election Results</h1>';
    $html .= generateRow($conn);
    $html .= '</table>';
    return $html;
}

function generateDetailsPdf($conn, $election_id, $filePath) {
    $html = generateDetailsHtml($conn, $election_id);
    generatePdf($html, $filePath);
}

function generateResultsPdf($conn, $election_id, $filePath) {
    $html = generateResultsHtml($conn, $election_id);
    generatePdf($html, $filePath);
}

function generateRow($conn) {
    $contents = '';

    $sql = "SELECT * FROM positions ORDER BY priority ASC";
    $query = $conn->query($sql);

    while ($position = $query->fetch_assoc()) {
        $contents .= '
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

        // Fetch candidates for the current position and determine the highest vote count
        $position_id = $position['id'];
        $sql = "SELECT candidates.*, partylists.name AS partylist_name 
                FROM candidates 
                LEFT JOIN partylists ON candidates.partylist_id = partylists.id 
                WHERE position_id = '$position_id' 
                ORDER BY lastname ASC";
        $cquery = $conn->query($sql);
        
        $candidates = [];
        $maxVotes = 0;

        while ($candidate = $cquery->fetch_assoc()) {
            $sql = "SELECT * FROM votes WHERE candidate_id = '" . $candidate['id'] . "'";
            $vquery = $conn->query($sql);
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

        // Render each candidate's row, highlighting the highest vote count
        foreach ($candidates as $candidate) {
            $highlight = ($candidate['votes'] == $maxVotes) ? ' style="background-color:yellow;"' : '';
            $contents .= '
                <tr' . $highlight . '>
                    <td width="80%">' . $candidate['partylist'] . ' — ' . $candidate['name'] . '</td>
                    <td width="20%">' . $candidate['votes'] . '</td>
                </tr>
            ';
        }

        $contents .= '
                </tbody>
            </table>
            <br/>
        ';
    }

    return $contents;
}

function generateDetailsSections($conn, $election_id) {
    // Update query to only use available columns from voters table
    $voters_query = "SELECT v.*, c.description as course_name 
                    FROM voters v 
                    LEFT JOIN courses c ON v.course_id = c.id 
                    ORDER BY v.student_number ASC";  // Order by student number instead
    
    $result = $conn->query($voters_query);
    if (!$result) {
        throw new Exception("Error fetching voters: " . $conn->error);
    }

    // Modify how you display voter information in PDF
    $voters_data = array();
    while ($row = $result->fetch_assoc()) {
        $voters_data[] = array(
            'Student Number' => $row['student_number'],
            'Course' => $row['course_name'],
            'Voting Status' => $row['has_voted'] ? 'Voted' : 'Not Voted',
            'Registration Date' => date('M d, Y', strtotime($row['created_at']))
        );
    }

    return $voters_data;
}

function generateDetailsPdfWithHeader($conn, $election_id, $details_pdf_path, $election_name) {
    try {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);  
        $pdf->SetAuthor('E-Halal Voting System');
        $pdf->SetTitle($election_name . ' - Election Details');
        
        // Add a page
        $pdf->AddPage();  
        
        // Set font
        $pdf->SetFont('helvetica', '', 12);
        
        // Add election name as header
        $pdf->Cell(0, 10, $election_name, 0, 1, 'C');
        $pdf->Ln(5);
        
        // Get voters data
        $voters_data = generateDetailsSections($conn, $election_id);
        
        // Create table header
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(50, 7, 'Student Number', 1);
        $pdf->Cell(80, 7, 'Course', 1);
        $pdf->Cell(30, 7, 'Status', 1);
        $pdf->Cell(30, 7, 'Registered', 1);
        $pdf->Ln();
        
        // Add table rows
        $pdf->SetFont('helvetica', '', 10);
        foreach ($voters_data as $voter) {
            $pdf->Cell(50, 6, $voter['Student Number'], 1);
            $pdf->Cell(80, 6, $voter['Course'], 1);
            $pdf->Cell(30, 6, $voter['Voting Status'], 1);
            $pdf->Cell(30, 6, $voter['Registration Date'], 1);
            $pdf->Ln();
        }
        
        // Save PDF
        $pdf->Output($details_pdf_path, 'F');

    } catch (Exception $e) {
        error_log("Error generating details PDF: " . $e->getMessage());
        throw new Exception("Failed to generate details PDF: " . $e->getMessage());
    }
}

function generateResultsPdfWithHeader($conn, $election_id, $filePath, $title) {
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
    $pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Details: '.$title);  
    $pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);  
    $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT); // Set left and right margins#
    $pdf->setPrintHeader(true); 
    $pdf->setPrintFooter(true);  
    $pdf->SetAutoPageBreak(TRUE, 15); // Set bottom margin to 15
    $pdf->SetFont('helvetica', '', 11);  
    
    // Add the first page
    $pdf->AddPage();  
    
    // Header content for results
    $content = '';  
    $content .= '
        <br><br><br><br>
        <h2 align="center">'.$title.'</h2>
        <h4 align="center">Tally Result</h4>
    ';
    $content .= generateRow($conn); // Function to generate rows in the table
    $pdf->writeHTML($content);

    // Output the file
    $pdf->Output($filePath, 'F');

    
}

