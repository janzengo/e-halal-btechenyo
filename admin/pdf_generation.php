<?php
require_once('../tcpdf/tcpdf.php');

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

function generateDetailsPdfWithHeader($conn, $election_id, $filePath, $title) {
    $pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
    $pdf->SetCreator(PDF_CREATOR);  
    $pdf->SetTitle('Details: '.$title);  
    $pdf->SetHeaderData('', '', PDF_HEADER_TITLE, PDF_HEADER_STRING);  
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));  
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));  
    $pdf->SetDefaultMonospacedFont('helvetica');  
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);  
    $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT); // Set left and right margins
    $pdf->setPrintHeader(true); 
    $pdf->setPrintFooter(true);  
    $pdf->SetAutoPageBreak(TRUE, 15); // Set bottom margin to 15
    $pdf->SetFont('helvetica', '', 11);  
    
    // Add the first page
    $pdf->AddPage();  
    
    $content = '';  
    $content .= '
        <br><br><br><br>
        <h2 align="center">'.$title.'</h2>
        <h4 align="center" >Election Details</h4>
    ';  
    $content .= generateDetailsSections($conn, $election_id);  
    $pdf->writeHTML($content);  
    
    // Add subsequent pages if needed
    // $pdf->AddPage();
    // $pdf->writeHTML($additionalContent);
    
    $pdf->Output($filePath, 'F');
}

function generateDetailsSections($conn, $election_id) {
    $contents = '';

    // Fetch election details from the database
    $sql = "SELECT * FROM election_status WHERE id = $election_id";
    $query = $conn->query($sql);
    $election = $query->fetch_assoc();

    $contents .= '
        <h3 align="left" style="font-size: 16px; font-weight: bold;">Election Name</h3>
        <p align="left">'.$election['election_name'].'</p>
        <h3 align="left" style="font-size: 16px; font-weight: bold;">Election Date</h3>
        <p align="left">'.$election['start_time'].' to '.$election['end_time'].'</p>
    ';

    // Fetch positions and candidates
    $contents .= '
        <h3 align="left" style="font-size: 16px; font-weight: bold;">Positions</h3>
    ';
    $sql = "SELECT * FROM positions ORDER BY priority ASC";
    $query = $conn->query($sql);
    while ($position = $query->fetch_assoc()) {
        $contents .= '
            <h4 align="left" style="font-size: 14px; font-weight: bold;">'.$position['description'].'</h4>
            <table border="1" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;line-height: 10px !important;">
                <thead>
                    <tr style="background-color: #e0f7fa;font-weight: bold;">
                        <th>Candidate Name</th>
                        <th>Partylist</th>
                    </tr>
                </thead>
                <tbody>
        ';
        $sql = "SELECT candidates.*, partylists.name AS partylist_name 
                FROM candidates 
                LEFT JOIN partylists ON candidates.partylist_id = partylists.id 
                WHERE position_id = " . $position['id'] . " 
                ORDER BY lastname ASC";
        $cquery = $conn->query($sql);
        while ($candidate = $cquery->fetch_assoc()) {
            $contents .= '
                <tr>
                    <td>'.$candidate['firstname'].' '.$candidate['lastname'].'</td>
                    <td>'.$candidate['partylist_name'].'</td>
                </tr>
            ';
        }
        $contents .= '
                </tbody>
            </table>
        ';
    }
    $contents .= '<br pagebreak="true"/>';

    // Fetch partylists
    $contents .= '
        <h3 align="left" style="font-size: 16px; font-weight: bold;">Partylists</h3>
        <table border="1" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #e0f7fa;">
                    <th>Partylist Name</th>
                    <th>Number of Candidates</th>
                </tr>
            </thead>
            <tbody>
    ';
    $sql = "SELECT partylists.name AS partylist_name, COUNT(candidates.id) AS num_candidates 
            FROM partylists 
            LEFT JOIN candidates ON candidates.partylist_id = partylists.id 
            GROUP BY partylists.id 
            ORDER BY partylists.name ASC";
    $query = $conn->query($sql);
    while ($partylist = $query->fetch_assoc()) {
        $contents .= '
            <tr>
                <td>'.$partylist['partylist_name'].'</td>
                <td>'.$partylist['num_candidates'].'</td>
            </tr>
        ';
    }
    $contents .= '
            </tbody>
        </table>
    ';

    // Add a page break before the Registered Voters table
    $contents .= '<br pagebreak="true"/>';

    // Fetch voters
    $contents .= '<br><br><br><br>  
        <h3 align="left" style="font-size: 16px; font-weight: bold;margin-top: 100px !important;">All Voters Registered</h3>
        <table border="1" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #e0f7fa;">
                    <th>Voter ID</th>
                    <th>Name</th>
                    <th>Course</th>
                </tr>
            </thead>
            <tbody>
    ';
    $sql = "SELECT voters.*, courses.description AS description 
            FROM voters 
            LEFT JOIN courses ON voters.course_id = courses.id 
            ORDER BY lastname ASC";
    $query = $conn->query($sql);
    while ($voter = $query->fetch_assoc()) {
        $contents .= '
            <tr>
                <td>'.$voter['voters_id'].'</td>
                <td>'.$voter['firstname'].' '.$voter['lastname'].'</td>
                <td>'.$voter['description'].'</td>
            </tr>
        ';
    }
    $contents .= '
            </tbody>
        </table>
    ';

    $contents .= '<br pagebreak="true"/>';
    // Fetch admin users
    $contents .= '
        <h3 align="left" style="font-size: 16px; font-weight: bold;">Officers Registered</h3>
        <table border="1" cellspacing="0" cellpadding="3" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #e0f7fa;">
                    <th>Username</th>
                    <th>Officer Name</th>
                </tr>
            </thead>
            <tbody>
    ';
    $sql = "SELECT * FROM admin ORDER BY lastname ASC";
    $query = $conn->query($sql);
    while ($admin = $query->fetch_assoc()) {
        $contents .= '
            <tr>
                <td>'.$admin['username'].'</td>
                <td>'.$admin['firstname'].' '.$admin['lastname'].'</td>
            </tr>
        ';
    }
    $contents .= '
            </tbody>
        </table>
    ';

    return $contents;
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

