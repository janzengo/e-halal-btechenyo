<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';  // Direct include of TCPDF

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
}

class CustomReceipt extends TCPDF {  // Use TCPDF directly
    // Page header
    public function Header() {
        // Get the correct path to images
        $imagePath = dirname(__DIR__);
        
        // Logo
        if (file_exists($imagePath . '/images/btech.png')) {
            $this->Image($imagePath . '/images/btech.png', 25, 5, 25);
        }
        if (file_exists($imagePath . '/images/ehalal.jpg')) {
            $this->Image($imagePath . '/images/ehalal.jpg', 165, 8, 20);
        }
        
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
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0);
    }
}

class Receipt {
    private $db;
    private $mail_config;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->mail_config = mail_config();
    }

    public function generate($vote_ref, $voter, $votes_data, $election) {
        try {
            // Get voter's email - student number is used for email
            $email = $voter['student_number'] . '@btech.ph.education';

            // Build receipt HTML
            $receipt = $this->buildHTML($voter, $vote_ref, $votes_data, $election);

            // Send email using PHPMailer
            return $this->sendEmail(
                $email, 
                $receipt, 
                $vote_ref, 
                $election['election_name']
            );

        } catch (Exception $e) {
            error_log("Receipt generation error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function sendEmail($email, $html_content, $vote_ref, $election_name) {
        $mail = new PHPMailer(true);
        
        try {
            // Configure mail
            if ($this->mail_config['use_smtp']) {
                $mail->isSMTP();
                $mail->Host = $this->mail_config['smtp']['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $this->mail_config['smtp']['username'];
                $mail->Password = $this->mail_config['smtp']['password'];
                $mail->SMTPSecure = $this->mail_config['smtp']['encryption'] === 'tls' ? 
                    PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = $this->mail_config['smtp']['port'];
            } else {
                $mail->isMail();
            }
            
            // Recipients
            $mail->setFrom(
                $this->mail_config['mail_from'], 
                $this->mail_config['mail_from_name']
            );
            $mail->addAddress($email);
            $mail->addReplyTo(
                $this->mail_config['mail_reply_to'], 
                $this->mail_config['mail_from_name']
            );
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $election_name . ' - Vote Receipt [Ref: ' . $vote_ref . ']';
            $mail->Body = $html_content;
            $mail->AltBody = "Your vote has been recorded.\nVote Reference: " . $vote_ref;
            
            $mail->send();
            
            return [
                'success' => true,
                'message' => "Receipt sent successfully to $email"
            ];
            
        } catch (Exception $e) {
            error_log("Failed to send receipt email: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Could not send receipt. Error: {$mail->ErrorInfo}"
            ];
        }
    }

    private function buildHTML($voter, $vote_ref, $votes_data, $election) {
        $html = $this->getEmailTemplate($voter, $vote_ref, $election);
        
        // Get all positions
        $sql = "SELECT * FROM positions ORDER BY priority ASC";
        $positions = $this->db->query($sql);
        
        while ($position = $positions->fetch_assoc()) {
            $html .= $this->renderPosition($position, $votes_data);
        }

        $html .= $this->getEmailFooter();
        
        return $html;
    }

    private function renderPosition($position, $votes_data) {
        $html = '<tr>';
        $html .= '<td><strong>' . htmlspecialchars($position['description']) . '</strong></td>';
        
        if (isset($votes_data[$position['id']])) {
            $position_votes = $votes_data[$position['id']];
            if (!is_array($position_votes)) {
                $position_votes = [$position_votes];
            }
            
            $candidates = [];
            foreach ($position_votes as $candidate_id) {
                $candidate = $this->getCandidate($candidate_id);
                if ($candidate) {
                    $candidate_text = $candidate['firstname'] . ' ' . $candidate['lastname'];
                    if (!empty($candidate['partylist_name'])) {
                        $candidate_text .= ' (' . $candidate['partylist_name'] . ')';
                    }
                    $candidates[] = $candidate_text;
                }
            }
            $html .= '<td>' . htmlspecialchars(implode(', ', $candidates)) . '</td>';
        } else {
            $html .= '<td><em>No vote cast</em></td>';
        }
        $html .= '</tr>';
        
        return $html;
    }

    private function getCandidate($candidate_id) {
        $sql = "SELECT c.*, p.name as partylist_name 
                FROM candidates c 
                LEFT JOIN partylists p ON c.partylist_id = p.id 
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $candidate_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function getEmailTemplate($voter, $vote_ref, $election) {
        date_default_timezone_set('Asia/Manila');
        $receiptDateTime = new DateTime(); // Current date and time
        $formattedDateTime = $receiptDateTime->format('F j, Y g:i A');
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Vote Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #1d7c39; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 20px; }
                .vote-ref { font-size: 24px; font-weight: bold; color: #1d7c39; text-align: center; margin: 20px 0; }
                .votes-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .votes-table th { background-color: #1d7c39; color: white; padding: 10px; text-align: left; }
                .votes-table td { padding: 8px; border-bottom: 1px solid #ddd; }
                .footer { text-align: center; font-size: 12px; color: #666; margin-top: 40px; }
            </style>
        </head>
        
        <body>
            <div class="container">
                <div class="header">
                    <h1>{$election['election_name']}</h1>
                </div>
                <div class="content">
                    <h2>Vote Receipt</h2>
                    <div class="vote-ref">Reference: {$vote_ref}</div>
                    <div class="voter-info">
                        <p><strong>Student Number:</strong> {$voter['student_number']}</p>
                        <p><strong>Date and Time:</strong> {$formattedDateTime}</p>
                    </div>
                    <h3 style="text-align: center;">Votes Cast</h3>
                    <table class="votes-table">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Candidate</th>
                            </tr>
                        </thead>
                        <tbody>
HTML;
    }

    private function getEmailFooter() {
        return <<<HTML
                        </tbody>
                    </table>
                </div>
                <div class="footer">
                    <p>This is an official vote receipt from the E-Halal BTECHenyo Voting System.</p>
                    <p>Please keep this document for your records.</p>
                </div>
            </div>
        </body>
        </html>
HTML;
    }

    /**
     * Parse votes data from JSON into a formatted string
     * @param array $votes_data JSON decoded votes data
     * @return array Formatted votes data with position and candidate details
     */
    private function parseVotesData($votes_data) {
        $formatted_votes = [];
        
        try {
            // Get all positions
            $sql = "SELECT * FROM positions ORDER BY priority ASC";
            $positions = $this->db->query($sql);
            
            while ($position = $positions->fetch_assoc()) {
                $position_votes = [];
                $position_votes['position'] = $position['description'];
                $position_votes['candidates'] = [];
                
                if (isset($votes_data[$position['id']])) {
                    $candidate_ids = is_array($votes_data[$position['id']]) ? 
                                   $votes_data[$position['id']] : 
                                   [$votes_data[$position['id']]];
                    
                    foreach ($candidate_ids as $candidate_id) {
                        $sql = "SELECT c.*, p.name as partylist_name 
                               FROM candidates c 
                               LEFT JOIN partylists p ON c.partylist_id = p.id 
                               WHERE c.id = ?";
                        $stmt = $this->db->prepare($sql);
                        $stmt->bind_param("i", $candidate_id);
                        $stmt->execute();
                        $candidate = $stmt->get_result()->fetch_assoc();
                        
                        if ($candidate) {
                            $candidate_info = $candidate['firstname'] . ' ' . $candidate['lastname'];
                            if (!empty($candidate['partylist_name'])) {
                                $candidate_info .= ' (' . $candidate['partylist_name'] . ')';
                            }
                            $position_votes['candidates'][] = $candidate_info;
                        }
                    }
                } else {
                    $position_votes['candidates'][] = 'No vote cast';
                }
                
                $formatted_votes[] = $position_votes;
            }
            
            return $formatted_votes;
        } catch (Exception $e) {
            error_log("Error parsing votes data: " . $e->getMessage());
            return [];
        }
    }

    public function generatePDF($vote_ref) {
        try {
            // Get vote data with votes_data included
            $sql = "SELECT v.*, v.votes_data, e.election_name, vt.student_number 
                    FROM votes v 
                    JOIN voters vt ON vt.id = ?
                    JOIN election_status e ON v.election_id = e.id 
                    WHERE v.vote_ref = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("is", $_SESSION['voter'], $vote_ref);
            $stmt->execute();
            $vote = $stmt->get_result()->fetch_assoc();

            if (!$vote) {
                throw new Exception("Vote not found or unauthorized access");
            }

            // Create PDF
            $pdf = new CustomReceipt('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('E-Halal BTECHenyo');
            $pdf->SetTitle('Vote Receipt - ' . $vote_ref);
            $pdf->setHeaderFont(Array('helvetica', '', 12));
            $pdf->setFooterFont(Array('helvetica', '', 8));
            $pdf->SetDefaultMonospacedFont('helvetica');
            $pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
            $pdf->SetFooterMargin(15);
            $pdf->setPrintHeader(true);
            $pdf->setPrintFooter(true);
            $pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->AddPage();

            // Header content
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, $vote['election_name'], 0, 1, 'C');
            
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 10, 'Vote Receipt', 0, 1, 'C');
            
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Reference: ' . $vote_ref, 0, 1, 'C');
            
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Cell(0, 10, 'Student Number: ' . $vote['student_number'], 0, 1, 'L');
            $pdf->Cell(0, 10, 'Date: ' . date('F j, Y g:i A', strtotime($vote['created_at'])), 0, 1, 'L');
            
            // Votes Cast table
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'Votes Cast', 0, 1, 'C');

            // Table headers
            $pdf->SetFillColor(29, 124, 57); // #1d7c39
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 11);
            
            // Define column widths
            $posWidth = 50;
            $candWidth = 120;
            
            $pdf->Cell($posWidth, 8, 'Position', 1, 0, 'L', true);
            $pdf->Cell($candWidth, 8, 'Candidate', 1, 1, 'L', true);
            
            // Reset text color
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 11);

            // Get and format votes
            $votes_data = json_decode($vote['votes_data'], true);
            $sql = "SELECT * FROM positions ORDER BY priority ASC";
            $positions = $this->db->query($sql);
            
            while ($position = $positions->fetch_assoc()) {
                $startY = $pdf->GetY();
                $startX = $pdf->GetX();
                
                if (isset($votes_data[$position['id']])) {
                    $position_votes = $votes_data[$position['id']];
                    if (!is_array($position_votes)) {
                        $position_votes = [$position_votes];
                    }
                    
                    $candidates = [];
                    foreach ($position_votes as $candidate_id) {
                        $candidate = $this->getCandidate($candidate_id);
                        if ($candidate) {
                            $candidate_text = $candidate['firstname'] . ' ' . $candidate['lastname'];
                            if (!empty($candidate['partylist_name'])) {
                                $candidate_text .= ' (' . $candidate['partylist_name'] . ')';
                            }
                            $candidates[] = $candidate_text;
                        }
                    }
                    
                    // Calculate required height
                    $lineHeight = 8;
                    $totalHeight = max(count($candidates) * $lineHeight, $lineHeight);
                    
                    // Draw position cell
                    $pdf->Cell($posWidth, $totalHeight, $position['description'], 1, 0, 'L');
                    
                    // Move to candidate column
                    $pdf->SetX($startX + $posWidth);
                    
                    // Draw candidate cell with all candidates
                    $candidateY = $startY;
                    foreach ($candidates as $index => $candidate) {
                        if ($index === 0) {
                            // First candidate
                            $pdf->Cell($candWidth, $lineHeight, $candidate, ($index === count($candidates) - 1 ? 1 : 'LTR'), 1, 'L');
                        } else if ($index === count($candidates) - 1) {
                            // Last candidate
                            $pdf->SetXY($startX + $posWidth, $candidateY);
                            $pdf->Cell($candWidth, $lineHeight, $candidate, 'LRB', 1, 'L');
                        } else {
                            // Middle candidates
                            $pdf->SetXY($startX + $posWidth, $candidateY);
                            $pdf->Cell($candWidth, $lineHeight, $candidate, 'LR', 1, 'L');
                        }
                        $candidateY += $lineHeight;
                    }
                    
                    // Move to next row
                    $pdf->SetXY($startX, $startY + $totalHeight);
                    
                } else {
                    // No votes case
                    $pdf->SetFont('helvetica', 'I', 11);
                    $pdf->Cell($posWidth, 8, $position['description'], 1, 0, 'L');
                    $pdf->Cell($candWidth, 8, 'No vote cast', 1, 1, 'L');
                    $pdf->SetFont('helvetica', '', 11);
                }
            }

            // Footer text
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 6, 'This is an official vote receipt from the E-Halal BTECHenyo Voting System.', 0, 1, 'C');
            $pdf->Cell(0, 6, 'Please keep this document for your records.', 0, 1, 'C');

            return $pdf->Output('Vote_Receipt_' . $vote_ref . '.pdf', 'S');

        } catch (Exception $e) {
            error_log("PDF generation error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function requestReceipt($vote_ref) {
        try {
            // Get vote data without joining voters table
            $sql = "SELECT v.*, v.votes_data, e.election_name, v.created_at 
                    FROM votes v 
                    JOIN election_status e ON v.election_id = e.id 
                    WHERE v.vote_ref = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("s", $vote_ref);
            $stmt->execute();
            $vote = $stmt->get_result()->fetch_assoc();

            if (!$vote) {
                throw new Exception("Vote reference not found");
            }

            // Format the date
            $date = date('F j, Y g:i A', strtotime($vote['created_at']));
            
            // Build HTML content with the formatted date
            $html = $this->buildRequestHTML($vote_ref, $vote, $date);
            
            return [
                'success' => true,
                'html' => $html
            ];

        } catch (Exception $e) {
            error_log("Receipt request error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function buildRequestHTML($vote_ref, $vote, $date) {
        $votes_data = json_decode($vote['votes_data'], true);
    
        $html = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Vote Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 800px; margin: 20px auto; padding: 20px; }
                .header { background-color: #1d7c39; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                .content { background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 20px; }
                .vote-ref { font-size: 24px; font-weight: bold; color: #1d7c39; text-align: center; margin: 20px 0; }
                .votes-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .votes-table th { background-color: #1d7c39; color: white; padding: 12px; text-align: left; }
                .votes-table td { padding: 10px; border: 1px solid #ddd; }
                .votes-table tr:nth-child(even) { background-color: #f2f2f2; }
                .footer { text-align: center; font-size: 12px; color: #666; margin-top: 40px; }
                .download-btn { 
                    display: inline-block;
                    background-color: #1d7c39;
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 5px;
                    margin-top: 20px;
                }
                .download-btn:hover { background-color: #166b30; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>{$vote['election_name']}</h1>
                </div>
                <div class="content">
                    <div class="vote-ref">Reference: {$vote_ref}</div>
                    <p style="text-align: left;"><strong>Date:</strong> {$date}</p>
                    <h3 style="text-align: center;">Votes Cast</h3>
                    <table class="votes-table">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Candidate</th>
                            </tr>
                        </thead>
                        <tbody>
    HTML;
    
        // Properly render positions and candidates
        $positions = $this->db->query("SELECT * FROM positions ORDER BY priority ASC");
        while ($position = $positions->fetch_assoc()) {
            $html .= $this->renderPosition($position, $votes_data);
        }
    
        $html .= <<<HTML
                        </tbody>
                    </table>
                    <div class="text-center" style="margin-top: 20px;">
                        <a href="request_receipt.php" class="btn btn-default btn-flat">
                            Search Another Receipt
                        </a>
                    </div>
                </div>
                <div class="footer">
                    <p>This is an official vote receipt from the E-Halal BTECHenyo Voting System.</p>
                    <p>Please keep this document for your records.</p>
                </div>
            </div>
        </body>
        </html>
    HTML;
    
        return $html;
    }
}
