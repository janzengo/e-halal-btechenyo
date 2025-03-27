<?php
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Position.php';
require_once __DIR__ . '/../classes/Candidate.php';
require_once __DIR__ . '/../classes/Voter.php';
require_once __DIR__ . '/../classes/Vote.php';
require_once __DIR__ . '/../classes/Admin.php';

// Initialize classes
$view = View::getInstance();
$position = Position::getInstance();
$candidate = Candidate::getInstance();
$voter = Voter::getInstance();
$vote = Vote::getInstance();
$admin = Admin::getInstance();

// Check if admin is logged in
if (!$admin->isLoggedIn()) {
    header('Location: ../administrator');
    exit();
}

// Get statistics
$stats = [
    'positions' => $position->getPositionCount(),
    'candidates' => $candidate->getCandidateCount(),
    'total_voters' => $voter->getVoterCount(),
    'voted' => $voter->getVotersWhoVoted()
];

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>E-Halal Voting System | Admin Dashboard</title>
    <?php echo $view->renderHeader(); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php 
    echo $view->renderNavbar();
    echo $view->renderMenubar();
    ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Dashboard
                <small>Control Panel</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i>Reports</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
        <?php
            if(isset($_SESSION['error'])){
                echo "
                    <div class='alert alert-danger alert-dismissible'>
                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                        <h4><i class='icon fa fa-warning'></i> Error!</h4>
                        ".$_SESSION['error']."
                    </div>
                ";
                unset($_SESSION['error']);
            }
            if(isset($_SESSION['success'])){
                echo "
                    <div class='alert alert-success alert-dismissible'>
                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                        <h4><i class='icon fa fa-check'></i> Success!</h4>
                        ".$_SESSION['success']."
                    </div>
                ";
                unset($_SESSION['success']);
            }
            ?>
            <!-- Modern Stats Cards -->
            <div class="row">
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-gradient" style="background: linear-gradient(135deg, #6B8DD6 0%, #8E37D7 100%);">
                        <div class="inner" style="color: white;">
                            <h3 style="color: white;"><?php echo $stats['positions']; ?></h3>
                            <p style="color: white;">Positions</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-list-alt"></i>
                        </div>
                        <a href="positions" class="small-box-footer" style="color: white;">
                            More info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-gradient" style="background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);">
                        <div class="inner" style="color: white;">
                            <h3 style="color: white;"><?php echo $stats['candidates']; ?></h3>
                            <p style="color: white;">Candidates</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <a href="candidates" class="small-box-footer" style="color: white;">
                            More info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-gradient" style="background: linear-gradient(135deg, #FFB74D 0%, #FF9800 100%);">
                        <div class="inner" style="color: white;">
                            <h3 style="color: white;"><?php echo $stats['total_voters']; ?></h3>
                            <p style="color: white;">Total Voters</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <a href="voters" class="small-box-footer" style="color: white;">
                            More info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-gradient" style="background: linear-gradient(135deg, #EF5350 0%, #D32F2F 100%);">
                        <div class="inner" style="color: white;">
                            <h3 style="color: white;"><?php echo $stats['voted']; ?></h3>
                            <p style="color: white;">Voters Voted</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <a href="votes" class="small-box-footer" style="color: white;">
                            More info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Position Charts -->
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="box-title">Vote Distribution by Position</h3>
                                <div class="chart-controls">
                                    <select id="chartTypeSelector" class="form-control" style="width: 200px; display: inline-block;">
                                        <option value="bar">Bar Chart</option>
                                        <option value="line">Line Graph</option>
                                        <option value="pie">Pie Chart</option>
                                        <option value="doughnut">Doughnut Chart</option>
                                        <option value="polarArea">Polar Area</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="box-body">
                            <?php 
                            $chartData = $view->getChartData();
                            ?>
                            <div id="chartsContainer" class="row">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Load Chart.js from node_modules -->
            <script src="<?php echo BASE_URL; ?>node_modules/chart.js/dist/chart.umd.js"></script>

            <!-- Initialize Charts -->
            <script>
                var chartData = <?php echo json_encode($view->getChartData()); ?>;

                function initCharts() {
                    const chartsContainer = document.getElementById('chartsContainer');
                    const chartTypeSelector = document.getElementById('chartTypeSelector');

                    if (!chartData || chartData.length === 0) {
                        chartsContainer.innerHTML = '<div class="alert alert-warning">No voting data available to display.</div>';
                        return;
                    }

                    function renderChart(type) {
                        chartsContainer.innerHTML = ''; // Clear previous charts

                        chartData.forEach((data, index) => {
                            // Create the chart container
                            const chartDiv = document.createElement('div');
                            chartDiv.className = 'col-md-6';
                            chartDiv.style.marginBottom = '20px';

                            const chartBox = document.createElement('div');
                            chartBox.style.padding = '20px';
                            chartBox.style.background = '#fff';
                            chartBox.style.borderRadius = '8px';
                            chartBox.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                            chartBox.style.height = '400px';

                            const canvas = document.createElement('canvas');
                            canvas.id = 'chart_' + index;
                            chartBox.appendChild(canvas);
                            chartDiv.appendChild(chartBox);
                            chartsContainer.appendChild(chartDiv);

                            try {
                                const ctx = canvas.getContext('2d');
                                new Chart(ctx, {
                                    type: type,
                                    data: {
                                        labels: data.candidates,
                                        datasets: [{
                                            label: data.position,
                                            data: data.votes,
                                            backgroundColor: [
                                                'rgba(255, 99, 132, 0.7)',   // Pink
                                                'rgba(54, 162, 235, 0.7)',   // Blue
                                                'rgba(255, 206, 86, 0.7)',   // Yellow
                                                'rgba(75, 192, 192, 0.7)',   // Teal
                                                'rgba(153, 102, 255, 0.7)',  // Purple
                                                'rgba(255, 159, 64, 0.7)',   // Orange
                                                'rgba(46, 204, 113, 0.7)',   // Green
                                                'rgba(231, 76, 60, 0.7)',    // Red
                                                'rgba(52, 73, 94, 0.7)',     // Dark Blue
                                                'rgba(155, 89, 182, 0.7)'    // Lavender
                                            ],
                                            borderColor: [
                                                'rgb(255, 99, 132)',
                                                'rgb(54, 162, 235)',
                                                'rgb(255, 206, 86)',
                                                'rgb(75, 192, 192)',
                                                'rgb(153, 102, 255)',
                                                'rgb(255, 159, 64)',
                                                'rgb(46, 204, 113)',
                                                'rgb(231, 76, 60)',
                                                'rgb(52, 73, 94)',
                                                'rgb(155, 89, 182)'
                                            ],
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            title: {
                                                display: true,
                                                text: data.position,
                                                font: { size: 16 }
                                            }
                                        },
                                        scales: type !== 'pie' && type !== 'doughnut' ? {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    stepSize: 1
                                                }
                                            }
                                        } : undefined
                                    }
                                });
                            } catch (error) {
                                chartBox.innerHTML = `<div class="alert alert-danger">Error creating chart</div>`;
                            }
                        });
                    }

                    // Initial render
                    renderChart('bar');

                    // Handle chart type changes
                    chartTypeSelector.addEventListener('change', function() {
                        renderChart(this.value);
                    });
                }

                // Wait for DOM to be ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initCharts);
                } else {
                    initCharts();
                }
            </script>

            <style>
                /* Modern UI Styles */
                .content-wrapper {
                    background-color: #f4f6f9;
                }
                
                .small-box {
                    border-radius: 15px;
                    overflow: hidden;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
                    transition: transform 0.3s ease;
                    cursor: pointer;
                }
                
                .small-box .icon {
                    font-size: 70px;
                    right: 20px;
                    top: 20px;
                    opacity: 0.3;
                    transition: all 0.3s ease;
                }
                
                .small-box:hover .icon {
                    font-size: 75px;
                    opacity: 0.4;
                }
                
                .small-box .inner {
                    padding: 20px;
                }
                
                .small-box h3 {
                    font-size: 38px;
                    font-weight: 600;
                    margin: 0;
                    white-space: nowrap;
                    color: #fff;
                }
                
                .small-box p {
                    font-size: 15px;
                    color: rgba(255, 255, 255, 0.9);
                    margin-bottom: 0;
                }
                
                .small-box .small-box-footer {
                    background: rgba(0, 0, 0, 0.1);
                    color: rgba(255, 255, 255, 0.9);
                    padding: 8px 0;
                    transition: all 0.3s ease;
                }
                
                .small-box:hover .small-box-footer {
                    background: rgba(0, 0, 0, 0.2);
                }
                
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
                
                .box-header .box-title {
                    font-size: 18px;
                    font-weight: 600;
                    color: #333;
                }
                
                .box-body {
                    padding: 20px;
                    background: #fff;
                    border-radius: 0 0 15px 15px;
                }
                
                canvas {
                    border-radius: 10px;
                    padding: 10px;
                }
                
                /* Responsive adjustments */
                @media (max-width: 767px) {
                    .small-box {
                        margin-bottom: 20px;
                    }
                    
                    .small-box h3 {
                        font-size: 30px;
                    }
                    
                    .box {
                        margin-bottom: 20px;
                    }
                }
                
                .chart-controls {
                    float: right;
                }
                
                .chart-controls select {
                    border-radius: 20px;
                    padding: 5px 15px;
                    border: 1px solid #ddd;
                    background: #fff;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                    transition: all 0.3s ease;
                }
                
                .chart-controls select:hover {
                    border-color: #aaa;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                }
                
                .chart-controls select:focus {
                    outline: none;
                    border-color: #4CAF50;
                    box-shadow: 0 2px 10px rgba(76,175,80,0.2);
                }

                .chart-container canvas {
                    max-height: 400px;
                }
                
                .chart-controls {
                    float: right;
                }
                
                .chart-controls select {
                    border-radius: 10px;
                    padding: 5px 15px;
                    border: 1px solid #ddd;
                    background: #fff;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                    transition: all 0.3s ease;
                }
                
                .chart-controls select:hover {
                    border-color: #aaa;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                }
                
                .chart-controls select:focus {
                    outline: none;
                    border-color: #4CAF50;
                    box-shadow: 0 2px 10px rgba(76,175,80,0.2);
                }

                .box {
                    margin-bottom: 20px;
                }
            </style>
        </section>
    </div>
    <!-- /.content-wrapper -->
    <?php echo $view->renderFooter(); ?>
</div>
<!-- ./wrapper -->

<?php echo $view->renderScripts(); ?>
</body>
</html>