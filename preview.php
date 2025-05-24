<?php
require_once 'classes/Database.php';
require_once 'classes/CustomSessionHandler.php';
require_once 'classes/User.php';
require_once 'classes/Ballot.php';

$session = CustomSessionHandler::getInstance();
$user = new User();
$db = Database::getInstance();
$ballot = new Ballot();

if (!$user->isLoggedIn()) {
    echo json_encode(['error' => true, 'message' => ['You are not logged in.']]);
    exit();
}

$output = ['error' => false, 'list' => '', 'list_mobile' => '', 'message' => []];

if (!isset($_POST['votes']) || !is_array($_POST['votes'])) {
    echo json_encode(['error' => true, 'message' => ['No vote data submitted.']]);
    exit();
}

try {
    // Get all positions
    $positions = $ballot->getPositions();
    
    if (!$positions) {
        throw new Exception("Error fetching positions");
    }

    $previewHtml = '';
    $previewHtmlMobile = '';
    
    while ($position = $positions->fetch_assoc()) {
        $pos_id = $position['id'];
        
        // Check if this position was voted for
        if (isset($_POST['votes'][$pos_id])) {
            $votes = is_array($_POST['votes'][$pos_id]) ? $_POST['votes'][$pos_id] : [$_POST['votes'][$pos_id]];
            
            // Validate vote count
            if (count($votes) > $position['max_vote']) {
                $output['error'] = true;
                $output['message'][] = 'You may only choose ' . $position['max_vote'] . ' candidates for ' . htmlspecialchars($position['description']);
                continue;
            }
            
            // Start position section for both desktop and mobile
            $previewHtml .= '<div class="well">';
            $previewHtml .= '<h4>' . htmlspecialchars($position['description']) . '</h4>';
            
            $previewHtmlMobile .= '<div class="well">';
            $previewHtmlMobile .= '<h4>' . htmlspecialchars($position['description']) . '</h4>';
            
            // Get candidate details for each vote
            foreach ($votes as $candidate_id) {
                $candidate = $ballot->getCandidate($candidate_id);
                if ($candidate) {
                    // Get partylist info
                    $partylist_sql = "SELECT pl.name FROM candidates c 
                                    LEFT JOIN partylists pl ON c.partylist_id = pl.id 
                                    WHERE c.id = ?";
                    $stmt = $db->getConnection()->prepare($partylist_sql);
                    $stmt->bind_param('i', $candidate_id);
                    $stmt->execute();
                    $partylist_result = $stmt->get_result()->fetch_assoc();
                    $partylist_name = $partylist_result ? $partylist_result['name'] : '';

                    // Build candidate row with mobile-friendly layout
                    $previewHtml .= '<div class="candidate-row">';
                    $previewHtml .= '<div class="candidate-image-container">';
                    $previewHtml .= '<img src="' . (!empty($candidate['photo']) ? 'administrator/'.$candidate['photo'] : 'administrator/assets/images/profile.jpg') . '" 
                                        class="candidate-image" alt="' . htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']) . '">';
                    $previewHtml .= '</div>';
                    $previewHtml .= '<div class="candidate-details">';
                    $previewHtml .= '<div class="candidate-name">' . htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']) . '</div>';
                    $previewHtml .= '<div class="candidate-partylist">' . htmlspecialchars($partylist_name ?: 'Independent') . '</div>';
                    $previewHtml .= '</div>';
                    $previewHtml .= '</div>';
                }
            }
            
            $previewHtml .= '</div>';
            $previewHtmlMobile .= '</div>';
        }
    }
    
    $output['list'] = $previewHtml;
    $output['list_mobile'] = $previewHtmlMobile;

} catch (Exception $e) {
    $output['error'] = true;
    $output['message'][] = $e->getMessage();
}

echo json_encode($output);
exit();
?>
