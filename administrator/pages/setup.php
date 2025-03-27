<?php
require_once __DIR__ . '/includes/controllers/setup_controller.php';
require_once __DIR__ . '/../classes/View.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/Elections.php';
require_once __DIR__ . '/../classes/Logger.php';

// Initialize classes
$view = View::getInstance();
$admin = Admin::getInstance();
$election = Elections::getInstance();
$logger = AdminLogger::getInstance();

// Check if admin is logged in and is superadmin
if (!$admin->isLoggedIn() || !$admin->isSuperAdmin()) {
    $_SESSION['error'] = 'Access Denied. This page is restricted to superadmins only.';
    header('Location: home');
    exit();
}

// Check current election status
$current_status = $election->getCurrentStatus();
if ($current_status !== 'setup') {
    $_SESSION['error'] = 'Setup page is only accessible when election status is in setup mode.';
    header('Location: configure.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $setupController = new SetupController();
    $setupController->saveSetup();
}

// Get current election details
$sql = "SELECT election_name, end_time FROM election_status WHERE status = 'setup'";
$result = $view->query($sql);
$election_details = $result->fetch_assoc();

// Include necessary modals
include 'includes/modals/officers_modal.php';
include 'includes/modals/positions_modal.php';
include 'includes/modals/candidates_modal.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Setup - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .nav-tabs .nav-link {
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            color: #0d6efd;
            font-weight: bold;
        }
        .tab-content {
            padding: 20px 0;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .btn-group-sm > .btn, .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Election Setup</h4>
                    </div>
                    <div class="card-body">
                        <form id="setupForm" method="POST">
                            <ul class="nav nav-tabs" id="setupTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">General</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="officers-tab" data-bs-toggle="tab" data-bs-target="#officers" type="button" role="tab">Officers</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="positions-tab" data-bs-toggle="tab" data-bs-target="#positions" type="button" role="tab">Positions</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="candidates-tab" data-bs-toggle="tab" data-bs-target="#candidates" type="button" role="tab">Candidates</button>
                                </li>
                            </ul>

                            <div class="tab-content" id="setupTabsContent">
                                <!-- General Tab -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="mb-3">
                                        <label for="election_name" class="form-label">Election Name</label>
                                        <input type="text" class="form-control" id="election_name" name="election_name" value="<?php echo htmlspecialchars($election_details['election_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="end_time" class="form-label">End Time</label>
                                        <input type="datetime-local" class="form-control" id="end_time" name="end_time" value="<?php echo htmlspecialchars($election_details['end_time'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <!-- Officers Tab -->
                                <div class="tab-pane fade" id="officers" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5>Election Officers</h5>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#officersModal">
                                            <i class="bi bi-plus-circle"></i> Add Officer
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="officersTable">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Officers data will be loaded here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Positions Tab -->
                                <div class="tab-pane fade" id="positions" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5>Positions</h5>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#positionsModal">
                                            <i class="bi bi-plus-circle"></i> Add Position
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="positionsTable">
                                            <thead>
                                                <tr>
                                                    <th>Position Name</th>
                                                    <th>Description</th>
                                                    <th>Max Winners</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Positions data will be loaded here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Candidates Tab -->
                                <div class="tab-pane fade" id="candidates" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5>Candidates & Partylists</h5>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#candidatesModal">
                                            <i class="bi bi-plus-circle"></i> Add Candidate
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="candidatesTable">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Position</th>
                                                    <th>Partylist</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Candidates data will be loaded here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Complete Setup</button>
                                <a href="configure.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#officersTable').DataTable();
            $('#positionsTable').DataTable();
            $('#candidatesTable').DataTable();

            // Form validation
            $('#setupForm').on('submit', function(e) {
                let isValid = true;
                let emptyFields = [];

                // Check required fields
                if (!$('#election_name').val()) {
                    emptyFields.push('Election Name');
                    isValid = false;
                }
                if (!$('#end_time').val()) {
                    emptyFields.push('End Time');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in the following required fields:\n' + emptyFields.join('\n'));
                }
            });
        });
    </script>
</body>
</html>
