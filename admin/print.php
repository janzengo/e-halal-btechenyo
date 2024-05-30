<?php
include 'includes/session.php';

function generateRow($conn){
    $contents = '';
    
    $sql = "SELECT * FROM positions ORDER BY priority ASC";
    $query = $conn->query($sql);
    while($row = $query->fetch_assoc()){
        $id = $row['id'];
        $contents .= '
            <tr>
                <td colspan="2" align="center" style="font-size:15px;"><b>'.$row['description'].'</b></td>
            </tr>
            <tr>
                <td width="80%"><b>Candidates</b></td>
                <td width="20%"><b>Votes</b></td>
            </tr>
        ';

        $sql = "SELECT * FROM candidates WHERE position_id = '$id' ORDER BY lastname ASC";
        $cquery = $conn->query($sql);

        // Find the highest votes for the current position
        $maxVotes = 0;
        $candidates = [];
        while($crow = $cquery->fetch_assoc()){
            $sql = "SELECT * FROM votes WHERE candidate_id = '".$crow['id']."'";
            $vquery = $conn->query($sql);
            $votes = $vquery->num_rows;
            $candidates[] = ['name' => $crow['lastname'].", ".$crow['firstname'], 'votes' => $votes];
            if ($votes > $maxVotes) {
                $maxVotes = $votes;
            }
        }

        // Render candidates and highlight the highest vote
        foreach ($candidates as $candidate) {
            $highlight = ($candidate['votes'] == $maxVotes) ? ' style="background-color:yellow;"' : '';
            $contents .= '
                <tr'.$highlight.'>
                    <td>'.$candidate['name'].'</td>
                    <td>'.$candidate['votes'].'</td>
                </tr>
            ';
        }
    }

    return $contents;
}

$parse = parse_ini_file('config.ini', FALSE, INI_SCANNER_RAW);
$title = $parse['election_title'];

require_once('../tcpdf/tcpdf.php');

class MYPDF extends TCPDF {
    //Page header
    public function Header() {
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
    <table border="1" cellspacing="0" cellpadding="3">  
  ';  
$content .= generateRow($conn);  
$content .= '</table>';  
$pdf->writeHTML($content);  
$pdf->Output('election_result.pdf', 'I');
?>
