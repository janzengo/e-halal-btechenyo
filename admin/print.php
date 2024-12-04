<?php
include 'includes/session.php';

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

$parse = parse_ini_file('config.ini', FALSE, INI_SCANNER_RAW);
$title = $parse['election_name'];

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

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);  
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
$pdf->AddPage();  
$content = '';  
$content .= '
    <br><br><br><br>
    <h2 align="center" style="line-height: 19px">'.$title.'</h2>
    <h4 align="center" >Tally Result</h4>
  ';  
$content .= generateRow($conn);
$pdf->writeHTML($content);  
$pdf->Output('election_result.pdf', 'I');
?>