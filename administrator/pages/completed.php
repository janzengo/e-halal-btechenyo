<?php
require_once __DIR__ . '/../../init.php';
// administrator/pages/completed.php
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Elections.php';
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Logger.php';

$admin = Admin::getInstance();
$elections = Elections::getInstance();
$view = View::getInstance();
$logger = AdminLogger::getInstance();

// Access control: Only Electoral Head can access
if (!$admin->isLoggedIn() || !$admin->isHead()) {
    $_SESSION['error'] = 'Access Denied. This page is restricted to Electoral Heads only.';
    header('Location: home.php');
    exit();
}

// Get current election details
$current = $elections->getCurrentElection();
if (!$current || $current['status'] !== Elections::STATUS_COMPLETED) {
    $_SESSION['error'] = 'No completed election to display.';
    header('Location: home.php');
    exit();
}

// Check if the current election is already archived
$isArchived = $elections->isCurrentElectionArchived();

// Handle archiving action
if (isset($_POST['archive'])) {
    // Prevent re-archiving
    if ($isArchived) {
        $_SESSION['error'] = 'This election has already been archived.';
        header('Location: completed.php');
        exit();
    }
    
    // Archive the election - this will generate PDFs and save to election_history
    $result = $elections->archiveElection();
    
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    
    header('Location: completed.php');
    exit();
}

// Handle reset action (starting a new election)
if (isset($_POST['reset_election'])) {
    // Ensure the current election is archived first
    if (!$isArchived) {
        $_SESSION['error'] = 'You must archive the current election before starting a new one.';
        header('Location: completed.php');
        exit();
    }
    
    // Reset the election system
    $result = $elections->resetElection();
    
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: configure.php'); // Redirect to configuration page
    } else {
        $_SESSION['error'] = $result['message'];
        header('Location: completed.php');
    }
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">    
<head>
    <?php echo $view->renderHeader(); ?>
    
    <title>Election Completed - Summary & Archive</title>
    
    </head>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">
    <?php echo $view->renderNavbar(); ?>
    <div class="content-wrapper">
        <div class="container">
            <section class="content-header">
                <h2>Election Summary: <?php echo htmlspecialchars($current['election_name']); ?></h2>
                <?php if ($isArchived): ?>
                <div class="archived-badge">
                    <span class="label label-success"><i class="fa fa-check-circle"></i> Archived</span>
                    <span class="control-number">Control #: <?php echo htmlspecialchars($current['control_number']); ?></span>
                </div>
                <?php elseif(!$isArchived): ?>
                <div class="archived-badge">
                    <span class="label label-danger"><i class="fa fa-check-circle"></i> Not Archived</span>
                    <span class="control-number">Control #: <?php echo htmlspecialchars($current['control_number']); ?></span>
                </div>
                <?php endif; ?>
            </section>
            <section class="content">
                <div class="alert alert-info">
                    <strong>Election Completed!</strong> Please review the results and archive the election. Archiving will generate PDFs and save all records to the election history.
                </div>
                
                <!-- Start of panel -->
                <div class="panel panel-default">
                    <div class="panel-body">
                        <!-- Updated tab structure with full-width tabs -->
                        <div class="custom-tabs">
                            <button class="custom-tab-btn active" data-tab="results">Election Results</button>
                            <button class="custom-tab-btn" data-tab="summary">Election Summary</button>
                        </div>
                        <div class="custom-tab-content active" id="results">
                            <?php include __DIR__ . '/includes/subpages/election-results.php'; ?>
                        </div>
                        <div class="custom-tab-content" id="summary">
                            <?php include __DIR__ . '/includes/subpages/election-summary.php'; ?>
                        </div>
                    </div>
                </div>
                <!-- End of panel -->
                
                <!-- Action buttons container - moved inside the content section -->
                <div class="actions-container mt-4 mb-4 p-3">
                    <div class="actions-title">Actions</div>
                    <div class="actions-btn-group">
                        <button type="button" class="btn btn-primary btn-lg mr-2" id="export-results">
                            <i class="fa fa-file-pdf"></i> Export Results
                        </button>
                        <button type="button" class="btn btn-info btn-lg mr-2" id="export-summary">
                            <i class="fa fa-file-pdf"></i> Export Summary
                        </button>
                        <?php if (!$isArchived): ?>
                        <button type="button" id="archive-election-btn" class="btn btn-success btn-lg d-inline-block mr-2">
                            <i class="fa fa-archive"></i> Archive Election
                        </button>
                        <!-- Archive Progress Modal will be injected by JS -->
                        <?php endif; ?>
                    </div>
                </div>
                <!-- End of action buttons container -->
                
                <!-- Start New Election button (only appears when archived) -->
                <?php if ($isArchived): ?>
                <div class="new-election-container mt-4 mb-4 p-3">
                    <div class="actions-title">New Election</div>
                    <div class="new-election-btn-group">
                        <form method="POST" id="resetElectionForm">
                            <button type="button" id="resetElectionBtn" class="btn btn-warning btn-lg">
                                <i class="fa fa-plus-circle"></i> Start New Election
                            </button>
                            <input type="hidden" name="reset_election" value="1">
                        </form>
                        <p class="help-text mt-2">
                            <i class="fa fa-info-circle"></i> This will reset all votes, candidates, and positions for a new election setup.
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
            </section>
        </div> <!-- end .container -->
    </div> <!-- end .content-wrapper -->
    
    <?php echo $view->renderFooter(); ?>
    
    <script src="<?= BASE_URL ?>administrator/assets/js/completed-tabs.js?v=2"></script>
    <script src="<?= BASE_URL ?>administrator/assets/js/archive-progress.js?v=2"></script>
    <!-- Chart.js & Election Summary Charts -->
    <script src="<?= BASE_URL ?>node_modules/chart.js/dist/chart.umd.js"></script>
    <script>
    window.BASE_URL = '<?= BASE_URL ?>';

    <?php 
    // Initialize $voteInstance and $positions here if not already done in included files
    if (!isset($voteInstance)) {
        require_once __DIR__ . '/../classes/Vote.php';
        $voteInstance = Vote::getInstance();
    }

    if (!isset($positions)) {
        require_once __DIR__ . '/../classes/Position.php';
        $positions = Position::getInstance()->getAllPositions();
    }

    // Now proceed with chart data generation
    ?>

    // Prepare data for partylist chart
    window.partylistData = <?php 
        $partylistVotes = $voteInstance->getVotesByPartylist();
        echo json_encode([
            'labels' => array_column($partylistVotes, 'partylist_name'),
            'data' => array_column($partylistVotes, 'total_votes')
        ]); 
    ?>;

    // Prepare data for position participation chart
    window.positionData = <?php 
        $positionData = [];
        foreach ($positions as $pos) {
            $candidates = $voteInstance->getVotesByPosition($pos['id']);
            $totalVotes = array_sum(array_column($candidates, 'votes'));
            $positionData[] = [
                'position' => $pos['description'],
                'votes' => $totalVotes
            ];
        }
        $chartData = [
            'labels' => array_column($positionData, 'position'),
            'data' => array_column($positionData, 'votes')
        ];
        echo json_encode($chartData);
    ?>;
    </script>
    <script src="<?= BASE_URL ?>administrator/pages/includes/scripts/votes.js"></script>

    <!-- End Chart.js & Election Summary Charts -->
    
    <!-- Add our HTML to Image library -->
    <script src="<?= BASE_URL ?>node_modules/html-to-image/dist/html-to-image.js"></script>
    <script src="<?= BASE_URL ?>administrator/assets/js/html2image.js"></script>
    
    <!-- Add html2canvas as a fallback -->
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    
    <!-- Add event handlers for export buttons -->
    <script>
    // Export Results button click handler
    document.getElementById('export-results').addEventListener('click', function() {
        window.open('<?= BASE_URL ?>administrator/pages/includes/subpages/controllers/export_results.php', '_blank');
    });

    // Export Summary button click handler
    document.getElementById('export-summary').addEventListener('click', function() {
        // Open in a new window/tab but without the progress parameter
        // This ensures clean PDF output without the SSE messages
        window.open('<?= BASE_URL ?>administrator/pages/includes/subpages/controllers/export_summary.php', '_blank');
    });
    </script>
    <!-- End Chart.js & Election Summary Charts -->

    <!-- Add SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Add reset election confirmation -->
    <script>
    document.getElementById('resetElectionBtn')?.addEventListener('click', function() {
        Swal.fire({
            title: 'Start New Election?',
            html: 
                '<div class="text-left">' +
                '<p>This action will:</p>' +
                '<ul>' +
                '<li>Delete all current candidates and their images</li>' +
                '<li>Remove all positions and partylists</li>' +
                '<li>Clear all voter records</li>' +
                '<li>Reset the election status</li>' +
                '</ul>' +
                '<p class="text-warning"><strong>This action cannot be undone!</strong></p>' +
                '</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, start new election',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('resetElectionForm').submit();
            }
        });
    });
    </script>
</div> <!-- end .wrapper -->
</body>
</html>
