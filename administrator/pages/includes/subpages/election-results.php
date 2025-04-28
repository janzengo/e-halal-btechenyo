<?php
require_once __DIR__ . '/../../../classes/Position.php';
require_once __DIR__ . '/../../../classes/Vote.php';
$positions = Position::getInstance()->getAllPositions();
$voteInstance = Vote::getInstance();
$totalVotes = $voteInstance->getTotalVotes();
foreach ($positions as $position):
    $candidates = $voteInstance->getVotesByPosition($position['id']);
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
    // Find highest vote count for highlighting
    $maxVotes = 0;
    foreach ($candidates as $c) {
        if ((int)$c['votes'] > $maxVotes) {
            $maxVotes = (int)$c['votes'];
        }
    }
?>
<div class="result-section" style="margin-bottom:32px;">
    <h4><?php echo htmlspecialchars($position['description']); ?></h4>
    <div class="table-responsive">
    <table class="table table-bordered table-striped result-table">
        <thead>
            <tr>
                <th>Candidate</th>
                <th>Partylist</th>
                <th>Votes</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($candidates as $candidate):
            $votes = (int)$candidate['votes'];
            $percentage = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 2) : 0;
            $highlight = ($votes === $maxVotes && $maxVotes > 0) ? 'top-vote-row' : '';
        ?>
            <tr class="<?php echo $highlight; ?>">
                <td><?php echo htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']); ?></td>
                <td><?php echo htmlspecialchars($candidate['partylist_name'] ?? 'Independent'); ?></td>
                <td><strong><?php echo $votes; ?></strong></td>
                <td><?php echo $percentage; ?>%</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endforeach; ?>