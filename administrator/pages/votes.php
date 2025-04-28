<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Elections.php';
Elections::enforceCompletedRedirect();
Elections::enforceSetupRedirect();
require_once __DIR__ . '/../classes/Position.php';
require_once __DIR__ . '/../classes/Vote.php';
require_once __DIR__ . '/../classes/Admin.php';

// Initialize classes
$view = View::getInstance();
$position = Position::getInstance();
$vote = Vote::getInstance();
$admin = Admin::getInstance();

// Check if admin is logged in
if (!$admin->isLoggedIn()) {
    header('Location: ../administrator');
    exit();
}

// Get voting data
$positions = $position->getAllPositions();
$voteStats = $vote->getVotingStatistics();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>E-Halal BTECHenyo | Voting Results</title>
    <?php echo $view->renderHeader(); ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>administrator/assets/css/admin.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php 
    echo $view->renderNavbar();
    echo $view->renderMenubar();
    ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <section class="content-header">
            <h1>
                Voting Results
                <small>Detailed Vote Analysis</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="home"><i class="fa fa-dashboard"></i>Reports</a></li>
                <li class="active">Votes</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Voting Progress -->
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title">Voting Progress</h3>
                        </div>
                        <div class="box-body">
                            <div class="progress-group">
                                <span class="progress-text">Overall Voter Turnout</span>
                                <span class="progress-number">
                                    <b><?php echo $voteStats['voted']; ?></b>/<?php echo $voteStats['total_voters']; ?>
                                </span>
                                <div class="progress">
                                    <?php 
                                    $percentage = ($voteStats['total_voters'] > 0) 
                                        ? ($voteStats['voted'] / $voteStats['total_voters']) * 100 
                                        : 0;
                                    ?>
                                    <div class="progress-bar progress-bar-success" 
                                         style="width: <?php echo $percentage; ?>%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Position-wise Results -->
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title">Position-wise Results</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-primary btn-sm custom" id="generateReport">
                                    <i class="fa fa-file-pdf"></i> Generate Report
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <?php if (empty($positions)): ?>
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> No positions have been configured yet.
                                </div>
                            <?php else: ?>
                                <?php foreach ($positions as $position): ?>
                                <div class="position-results">
                                    <h4><?php echo htmlspecialchars($position['description']); ?></h4>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th width="40%">Candidate</th>
                                                    <th width="15%">Votes</th>
                                                    <th width="15%">Percentage</th>
                                                    <th width="30%">Graph</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $candidates = $vote->getVotesByPosition($position['id']);
                                                if (empty($candidates)): ?>
                                                    <tr>
                                                        <td colspan="4" class="text-center">No candidates for this position.</td>
                                                    </tr>
                                                <?php else:
                                                    // Calculate total votes for this position
                                                    $totalVotes = array_sum(array_column($candidates, 'votes'));
                                                    
                                                    // Get max votes allowed for this position
                                                    $maxVotesPerVoter = $position['max_votes'] ?? 1;
                                                    
                                                    // Adjust total possible votes based on number of voters and max votes per voter
                                                    $totalPossibleVotes = $voteStats['total_voters'] * $maxVotesPerVoter;
                                                    
                                                    foreach ($candidates as $candidate):
                                                        // Calculate percentage based on total possible votes
                                                        $votePercentage = ($totalPossibleVotes > 0) 
                                                            ? ($candidate['votes'] / $totalPossibleVotes) * 100 
                                                            : 0;
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($candidate['partylist_name'] ?: 'Independent'); ?></small>
                                                    </td>
                                                    <td><strong><?php echo $candidate['votes']; ?></strong></td>
                                                    <td><?php echo number_format($votePercentage, 1); ?>%</td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar progress-bar-success" 
                                                                 style="width: <?php echo $votePercentage; ?>%"
                                                                 title="<?php echo number_format($votePercentage, 1); ?>%">
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endforeach; 
                                                endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row">
                <div class="col-md-6">
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title">Position Participation Rate</h3>
                        </div>
                        <div class="box-body">
                            <canvas id="positionParticipationChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title">Partylist Performance</h3>
                        </div>
                        <div class="box-body">
                            <canvas id="partylistChart" style="height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php echo $view->renderFooter(); ?>
</div>

<!-- Scripts -->
<?php echo $view->renderScripts(); ?>

<!-- Chart.js -->
<script src="<?php echo BASE_URL; ?>node_modules/chart.js/dist/chart.umd.js"></script>

<script>
    // Global variables for votes.js
    window.BASE_URL = '<?php echo BASE_URL; ?>';
    
    // Prepare data for partylist chart
    window.partylistData = <?php 
        $partylistVotes = $vote->getVotesByPartylist();
        error_log("Partylist votes data: " . json_encode($partylistVotes));
        echo json_encode([
            'labels' => array_column($partylistVotes, 'partylist_name'),
            'data' => array_column($partylistVotes, 'total_votes')
        ]); 
    ?>;

    // Prepare data for position participation chart
    window.positionData = <?php 
        $positionData = [];
        foreach ($positions as $pos) {
            $candidates = $vote->getVotesByPosition($pos['id']);
            $totalVotes = array_sum(array_column($candidates, 'votes'));
            $positionData[] = [
                'position' => $pos['description'],
                'votes' => $totalVotes
            ];
            error_log("Position {$pos['description']}: {$totalVotes} votes");
        }
        $chartData = [
            'labels' => array_column($positionData, 'position'),
            'data' => array_column($positionData, 'votes')
        ];
        error_log("Position chart data: " . json_encode($chartData));
        echo json_encode($chartData);
    ?>;

    // Debug data
    console.log('Position Data:', window.positionData);
    console.log('Partylist Data:', window.partylistData);
</script>

<!-- Custom scripts -->
<script src="<?php echo BASE_URL; ?>administrator/pages/includes/scripts/votes.js"></script>

</body>
</html> 