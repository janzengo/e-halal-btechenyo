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
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <!-- Modern Stats Cards -->
            <div class="row">
                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-gradient" style="background: linear-gradient(135deg, #6B8DD6 0%, #8E37D7 100%);">
                        <div class="inner">
                            <h3><?php echo $stats['positions']; ?></h3>
                            <p>Positions</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-list-alt"></i>
                        </div>
                        <a href="positions" class="small-box-footer">
                            More info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-gradient" style="background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);">
                        <div class="inner">
                            <h3><?php echo $stats['candidates']; ?></h3>
                            <p>Candidates</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-user"></i>
                        </div>
                        <a href="candidates" class="small-box-footer">
                            More info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-gradient" style="background: linear-gradient(135deg, #FFB74D 0%, #FF9800 100%);">
                        <div class="inner">
                            <h3><?php echo $stats['total_voters']; ?></h3>
                            <p>Total Voters</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <a href="voters" class="small-box-footer">
                            More info <i class="fa fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-xs-6">
                    <div class="small-box bg-gradient" style="background: linear-gradient(135deg, #EF5350 0%, #D32F2F 100%);">
                        <div class="inner">
                            <h3><?php echo $stats['voted']; ?></h3>
                            <p>Voters Voted</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <a href="votes" class="small-box-footer">
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
                            <div id="chartsContainer" class="row">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chartTypeSelector = document.getElementById('chartTypeSelector');
                const chartData = <?php echo json_encode($view->getChartData()); ?>;
                const chartsContainer = document.getElementById('chartsContainer');
                
                function createChartConfig(type, data) {
                    const config = {
                        type: type,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: data.position + ' - Vote Distribution',
                                    font: { size: 16 }
                                }
                            }
                        }
                    };
                    
                    if (type === 'line') {
                        config.data = {
                            labels: ['Initial', 'Current'],
                            datasets: data.datasets
                        };
                        config.options.scales = {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        };
                    } 
                    else if (type === 'pie' || type === 'doughnut' || type === 'polarArea') {
                        config.data = {
                            labels: data.candidates,
                            datasets: [{
                                data: data.votes,
                                backgroundColor: data.backgroundColor
                            }]
                        };
                        config.options.plugins.legend = {
                            display: true,
                            position: 'right'
                        };
                    }
                    else { // bar
                        config.data = {
                            labels: data.candidates,
                            datasets: [{
                                label: 'Votes',
                                data: data.votes,
                                backgroundColor: data.backgroundColor
                            }]
                        };
                        config.options.scales = {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1 }
                            }
                        };
                    }
                    
                    return config;
                }

                function renderCharts(type) {
                    // Clear the container
                    chartsContainer.innerHTML = '';
                    
                    // Create new chart elements
                    chartData.forEach((data, index) => {
                        // Create column div
                        const colDiv = document.createElement('div');
                        colDiv.className = 'col-md-6';
                        
                        // Create box div
                        const boxDiv = document.createElement('div');
                        boxDiv.className = 'box box-success';
                        
                        // Create box body
                        const boxBody = document.createElement('div');
                        boxBody.className = 'box-body';
                        
                        // Create canvas
                        const canvas = document.createElement('canvas');
                        canvas.id = 'chart_' + index;
                        canvas.style.height = '300px';
                        
                        // Assemble the elements
                        boxBody.appendChild(canvas);
                        boxDiv.appendChild(boxBody);
                        colDiv.appendChild(boxDiv);
                        chartsContainer.appendChild(colDiv);
                        
                        // Create the chart
                        const ctx = canvas.getContext('2d');
                        new Chart(ctx, createChartConfig(type, data));
                    });
                }

                // Initialize charts
                renderCharts('bar');

                // Handle chart type changes
                chartTypeSelector.addEventListener('change', function() {
                    renderCharts(this.value);
                });
            });
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
</body>
</html>