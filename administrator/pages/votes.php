<?php
require_once __DIR__ . '/../classes/View.php';
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
    <title>E-Halal Voting System | Voting Results</title>
    <?php echo $view->renderHeader(); ?>
    <!-- Additional CSS for data tables -->
    <link rel="stylesheet" href="../plugins/datatables/dataTables.bootstrap.css">
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
                <li><a href="home"><i class="fa fa-dashboard"></i> Home</a></li>
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
                                <button type="button" class="btn btn-success btn-sm" id="printResults">
                                    <i class="fa fa-print"></i> Print Results
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <?php foreach ($positions as $position): ?>
                            <div class="position-results">
                                <h4><?php echo $position['description']; ?></h4>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Candidate</th>
                                                <th>Votes</th>
                                                <th>Percentage</th>
                                                <th>Graph</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $candidates = $vote->getVotesByPosition($position['id']);
                                            $totalVotes = array_sum(array_column($candidates, 'votes'));
                                            foreach ($candidates as $candidate):
                                                $votePercentage = ($totalVotes > 0) 
                                                    ? ($candidate['votes'] / $totalVotes) * 100 
                                                    : 0;
                                            ?>
                                            <tr>
                                                <td><?php echo $candidate['firstname'] . ' ' . $candidate['lastname']; ?></td>
                                                <td><?php echo $candidate['votes']; ?></td>
                                                <td><?php echo number_format($votePercentage, 2); ?>%</td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-success" 
                                                             style="width: <?php echo $votePercentage; ?>%">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vote Timeline -->
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title">Voting Timeline</h3>
                        </div>
                        <div class="box-body">
                            <canvas id="voteTimeline" style="height: 300px;"></canvas>
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
<!-- DataTables -->
<script src="../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../plugins/datatables/dataTables.bootstrap.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(function() {
    // Initialize DataTables
    $('.table').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false
    });

    // Print functionality
    $('#printResults').click(function() {
        window.print();
    });

    // Vote Timeline Chart
    const timelineCtx = document.getElementById('voteTimeline').getContext('2d');
    const timelineData = <?php echo json_encode($vote->getVoteTimeline()); ?>;
    
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: timelineData.labels,
            datasets: [{
                label: 'Votes Cast',
                data: timelineData.data,
                borderColor: '#00a65a',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>

<style>
/* Modern UI Styles */
.box {
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border: none;
    margin-bottom: 30px;
}

.box-header {
    padding: 20px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.box-body {
    padding: 20px;
    background: #fff;
    border-radius: 0 0 15px 15px;
}

.progress {
    height: 20px;
    border-radius: 10px;
    margin-bottom: 10px;
}

.progress-bar {
    border-radius: 10px;
}

.table-responsive {
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 20px;
}

.table {
    margin-bottom: 0;
}

.position-results {
    margin-bottom: 30px;
}

.position-results:last-child {
    margin-bottom: 0;
}

.btn {
    border-radius: 20px;
    padding: 6px 20px;
}

@media print {
    .no-print {
        display: none !important;
    }
    
    .content-wrapper {
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .box {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>

</body>
</html> 