<?php
require_once __DIR__ . '/../../classes/View.php';
require_once __DIR__ . '/../../classes/Admin.php';
require_once __DIR__ . '/../../classes/Elections.php';
require_once __DIR__ . '/../../classes/Logger.php';

class SetupController {
    private $view;
    private $admin;
    private $election;
    private $logger;
    private $conn;

    public function __construct() {
        $this->view = View::getInstance();
        $this->admin = Admin::getInstance();
        $this->election = Elections::getInstance();
        $this->logger = AdminLogger::getInstance();
        $this->conn = $this->view->getConnection();
    }

    public function saveSetup() {
        // Check if admin is logged in and is superadmin
        if (!$this->admin->isLoggedIn() || !$this->admin->isSuperAdmin()) {
            $_SESSION['error'] = 'Access Denied. This page is restricted to superadmins only.';
            header('Location: home');
            exit();
        }

        // Check current election status
        $current_status = $this->election->getCurrentStatus();
        if ($current_status !== 'setup') {
            $_SESSION['error'] = 'Setup page is only accessible when election status is in setup mode.';
            header('Location: configure.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Start transaction
                $this->conn->begin_transaction();

                // Validate required fields
                if (empty($_POST['election_name']) || empty($_POST['end_time'])) {
                    throw new Exception('Election name and end time are required.');
                }

                // Validate end time is in the future
                $end_time = new DateTime($_POST['end_time']);
                $now = new DateTime();
                if ($end_time <= $now) {
                    throw new Exception('End time must be in the future.');
                }

                // Update election status
                $sql = "UPDATE election_status SET 
                        election_name = ?, 
                        end_time = ?, 
                        status = 'pending',
                        last_status_change = NOW()
                        WHERE status = 'setup'";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("ss", $_POST['election_name'], $_POST['end_time']);
                $stmt->execute();

                // Log the action
                $this->logger->logAction(
                    $this->admin->getCurrentUser()['username'],
                    'Election setup completed',
                    'Changed election status from setup to pending'
                );

                // Commit transaction
                $this->conn->commit();

                $_SESSION['success'] = 'Election setup completed successfully. The election is now in pending status.';
                header('Location: configure.php');
                exit();

            } catch (Exception $e) {
                // Rollback transaction on error
                $this->conn->rollback();
                
                $_SESSION['error'] = 'Error completing setup: ' . $e->getMessage();
                header('Location: setup.php');
                exit();
            }
        } else {
            // If not POST request, redirect to setup page
            header('Location: setup.php');
            exit();
        }
    }
} 