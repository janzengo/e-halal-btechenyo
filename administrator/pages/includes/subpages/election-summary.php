<?php
require_once __DIR__ . '/../../../classes/Vote.php';
require_once __DIR__ . '/../../../classes/Position.php';
require_once __DIR__ . '/../../../classes/Candidate.php';
$voteInstance = Vote::getInstance();
$positions = Position::getInstance()->getAllPositions();
$candidate = Candidate::getInstance();
$voteStats = $voteInstance->getVotingStatistics();
$positionsCount = count($positions);
$candidatesCount = 0;
foreach ($positions as $position) {
    $candidatesCount += count($voteInstance->getVotesByPosition($position['id']));
}
?>
<h4>Election Information</h4>
<table class="table table-bordered table-striped">
    <tr>
        <th>Election Name</th>
        <td><?php echo htmlspecialchars($current['election_name']); ?></td>
    </tr>
    <tr>
        <th>Status</th>
        <td><span class="label label-success">Completed</span></td>
    </tr>
    <tr>
        <th>Created At</th>
        <td><?php echo date('F d, Y h:i:s A', strtotime($current['created_at'])); ?></td>
    </tr>
    <tr>
        <th>End Time</th>
        <td><?php echo date('F d, Y h:i:s A', strtotime($current['end_time'])); ?></td>
    </tr>
</table>

<!-- Elected Officers Section -->
<div class="box box-solid">
    <div class="box-header with-border">
        <h4 class="box-title">Elected Officers</h4>
    </div>
    <div class="box-body">
        <div class="row">
            <?php
            $globalWinnerCount = 0;
            foreach ($positions as $pos) {
                $position_id = $pos['id'];
                $position_name = $pos['description'];
                $max_vote = $pos['max_vote'];
                
                // Get candidates for this position with their votes
                $candidates = $voteInstance->getVotesByPosition($position_id);
                
                if (empty($candidates)) {
                    continue;
                }
                
                // Sort by votes (descending)
                usort($candidates, function($a, $b) {
                    return $b['votes'] - $a['votes'];
                });
                
                // Take only top N candidates where N = max_vote
                $winners = array_slice($candidates, 0, $max_vote);
                
                // Display winners
                foreach ($winners as $cand) {
                    if ($cand['votes'] > 0) { // Only show if they have votes
                        $globalWinnerCount++;
                        
                        // Get candidate photo
                        $photoPath = !empty($cand['photo']) ? $cand['photo'] : 'assets/images/profile.jpg';
                        
                        // Winner card
                        echo '<div class="col-md-4 col-sm-6">';
                        echo '<div class="box box-solid" style="margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24); border-top: 3px solid #3c8dbc;">';
                        
                        echo '<div class="box-body text-center">';
                        echo '<img class="img-circle" src="' . htmlspecialchars($photoPath) . '" alt="' . htmlspecialchars($cand['firstname'] . ' ' . $cand['lastname']) . '" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #fff; margin-bottom: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">';
                        echo '<h4 style="font-weight: 600; margin-bottom: 2px;">' . htmlspecialchars($cand['firstname'] . ' ' . $cand['lastname']) . '</h4>';
                        echo '<p style="color: #777; margin-bottom: 10px;">' . htmlspecialchars($cand['partylist_name'] ?? 'Independent') . '</p>';
                        echo '</div>';
                        
                        echo '<div class="box-footer no-padding">';
                        echo '<ul class="nav nav-stacked">';
                        echo '<li style="border-bottom: 1px solid #f4f4f4;"><a href="#" style="pointer-events: none;"><strong>Position:</strong> <span class="pull-right">' . htmlspecialchars($position_name) . '</span></a></li>';
                        echo '<li><a href="#" style="pointer-events: none;"><strong>Votes:</strong> <span class="pull-right badge bg-green">' . $cand['votes'] . '</span></a></li>';
                        echo '</ul>';
                        echo '</div>';
                        
                        echo '</div>';
                        echo '</div>';
                    }
                }
            }
            
            if ($globalWinnerCount == 0) {
                echo '<div class="col-md-12">';
                echo '<div class="alert alert-info text-center" role="alert">';
                echo '<i class="fa fa-info-circle"></i> No elected officers found or votes have not been tallied yet.';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>
<!-- End Elected Officers Section -->

<!-- Charts Section (matching votes.php) -->
<div class="row">
    <div class="col-md-6">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h4 class="box-title">Position Participation Rate</h4>
            </div>
            <div class="box-body">
                <canvas id="positionParticipationChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h4 class="box-title">Partylist Performance</h4>
            </div>
            <div class="box-body">
                <canvas id="partylistChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>
<!-- End Charts Section -->

<!-- Chart.js data variables will be set in the parent page -->
