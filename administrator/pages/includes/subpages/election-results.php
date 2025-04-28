<?php
require_once __DIR__ . '/../../../classes/Position.php';
require_once __DIR__ . '/../../../classes/Vote.php';
$positions = Position::getInstance()->getAllPositions();
$voteInstance = Vote::getInstance();
$totalVotes = $voteInstance->getTotalVotes();

foreach ($positions as $position):
    $candidates = $voteInstance->getVotesByPosition($position['id']);
    $max_vote = (int)$position['max_vote'];
    
    // Sort candidates by votes DESC, then by lastname and firstname ASC
    usort($candidates, function($a, $b) {
        $av = (int)$a['votes'];
        $bv = (int)$b['votes'];
        if ($av === $bv) {
            $al = strtolower($a['lastname'] . $a['firstname']);
            $bl = strtolower($b['lastname'] . $b['firstname']);
            return $al <=> $bl;
        }
        return $bv <=> $av;
    });

    // Track top N candidates based on max_vote
    $topVotes = [];
    $currentRank = 1;
    $prevVotes = null;
    $rankedCandidates = 0;
    
    foreach ($candidates as $candidate) {
        $votes = (int)$candidate['votes'];
        if ($votes === 0) break; // Stop if we hit candidates with 0 votes
        
        if ($prevVotes !== null && $votes < $prevVotes) {
            $currentRank++;
        }
        
        if ($currentRank <= $max_vote) {
            $topVotes[] = $votes;
            $rankedCandidates++;
        }
        
        $prevVotes = $votes;
    }
?>
<div class="result-section" style="margin-bottom:32px;">
    <h4>
        <?php echo htmlspecialchars($position['description']); ?>
        <small>(Top <?php echo $max_vote; ?> will be elected)</small>
    </h4>
    <div class="table-responsive">
    <table class="table table-bordered table-striped result-table">
        <thead>
            <tr>
                <th>Rank</th>
                <th>Candidate</th>
                <th>Partylist</th>
                <th>Votes</th>
                <th>Percentage</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php 
        $currentRank = 1;
        $prevVotes = null;
        $rankedCandidates = 0;
        
        foreach ($candidates as $candidate):
            $votes = (int)$candidate['votes'];
            if ($votes === 0) continue; // Skip candidates with 0 votes
            
            // Determine rank
            if ($prevVotes !== null && $votes < $prevVotes) {
                $currentRank++;
            }
            
            // Calculate percentage
            $percentage = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 2) : 0;
            
            // Determine if this candidate is in top N
            $isTopN = in_array($votes, $topVotes);
            $rowClass = $isTopN ? 'success' : '';
            $status = $isTopN ? '<span class="label label-success">Elected</span>' : '';
            
            // Handle ties at the cutoff
            if ($currentRank == $max_vote && $votes == $prevVotes) {
                $rowClass = 'warning';
                $status = '<span class="label label-warning">Tie</span>';
            }
        ?>
            <tr class="<?php echo $rowClass; ?>">
                <td><?php echo $currentRank; ?></td>
                <td><?php echo htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']); ?></td>
                <td><?php echo htmlspecialchars($candidate['partylist_name'] ?? 'Independent'); ?></td>
                <td><strong><?php echo $votes; ?></strong></td>
                <td><?php echo $percentage; ?>%</td>
                <td><?php echo $status; ?></td>
            </tr>
        <?php 
            $prevVotes = $votes;
        endforeach; 
        ?>
        </tbody>
    </table>
    </div>
</div>
<?php endforeach; ?>