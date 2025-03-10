<?php
require_once 'classes/Database.php';
require_once 'classes/CustomSessionHandler.php';
require_once 'classes/User.php';
require_once 'classes/Ballot.php';
require_once 'classes/Votes.php';
require_once 'classes/Election.php';

$session = CustomSessionHandler::getInstance();
$user = new User();
$ballot = new Ballot();
$votes = new Votes();
$election = new Election();

if (!$user->isLoggedIn()) {
    header('location: index.php');
    exit();
}

// Get current voter's ID and election
$currentVoter = $user->getCurrentUser();
$currentElection = $election->getCurrentElection();

if (!$currentElection) {
    $session->setError('No active election found.');
    header('location: home.php');
    exit();
}

// Check if voter has already voted
if ($votes->hasVoted($currentVoter['id'])) {
    header('location: home.php?vote=already_voted');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['votes']) || !is_array($_POST['votes'])) {
        $session->setError('Invalid vote data submitted.');
        header('location: home.php?vote=current');
        exit();
    }

    $votesData = $_POST['votes'];
    
    // Validate that at least one vote was cast
    $hasVotes = false;
    foreach ($votesData as $position_votes) {
        if (!empty($position_votes)) {
            $hasVotes = true;
            break;
        }
    }
    
    if (!$hasVotes) {
        $session->setError('Please vote for at least one candidate.');
        header('location: home.php?vote=current');
        exit();
    }

    try {
        // Start transaction
        $db = Database::getInstance();
        $db->beginTransaction();

        // Validate votes
        $errors = $ballot->validateVotes($votesData);
        if (!empty($errors)) {
            throw new Exception(implode("\n", $errors));
        }

        // Submit votes
        $voteResult = $votes->submitVotes(
            $currentVoter['id'],
            $votesData,
            $currentElection['id']
        );
        
        if (!$voteResult['success']) {
            throw new Exception($voteResult['message']);
        }

        // Send receipt email
        $receiptResult = $ballot->generateReceipt(
            $voteResult['vote_ref'],
            $currentVoter,
            $votesData,
            $currentElection
        );

        if (!$receiptResult['success']) {
            // Log receipt generation failure but don't rollback vote
            error_log("Failed to send receipt email: " . $receiptResult['message']);
            $session->setSuccess('Your vote was recorded successfully, but there was an issue sending the receipt email. Please contact support if needed.');
        } else {
            $session->setSuccess('Your vote was recorded successfully. A receipt has been sent to your email.');
        }

        // Set session variables for success page
        $_SESSION['just_voted'] = true;
        $_SESSION['vote_ref'] = $voteResult['vote_ref'];

        $db->commit();
        header('location: home.php?vote=complete');
        exit();

    } catch (Exception $e) {
        $db->rollback();
        $session->setError($e->getMessage());
        header('location: home.php?vote=current');
        exit();
    }
} else {
    header('location: home.php?vote=current');
    exit();
}
