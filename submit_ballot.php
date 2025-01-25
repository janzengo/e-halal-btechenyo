<?php
require_once 'classes/Database.php';
require_once 'classes/CustomSessionHandler.php';
require_once 'classes/User.php';
require_once 'classes/Ballot.php';
require_once 'classes/Votes.php';

$session = CustomSessionHandler::getInstance();
$user = new User();
$ballot = new Ballot();
$votes = new Votes();

if (!$user->isLoggedIn()) {
    header('location: index.php');
    exit();
}

// Get current voter's ID
$currentVoter = $user->getCurrentUser();
$voterId = $currentVoter['id'];

// Check if voter has already voted
if ($votes->hasVoted($voterId)) {
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

    // Process and submit votes
    $processedVotes = [];
    foreach ($votesData as $position_id => $candidates) {
        if (is_array($candidates)) {
            $processedVotes[$position_id] = $candidates;
        } else {
            $processedVotes[$position_id] = [$candidates];
        }
    }
    
    if ($votes->submitVotes($voterId, $processedVotes)) {
        $_SESSION['just_voted'] = true; // Set flag for just voted
        $session->setSuccess('Your ballot has been successfully submitted!');
        header('location: home.php?vote=complete');
        exit();
    } else {
        $session->setError('An error occurred while submitting your votes. Please try again.');
        header('location: home.php?vote=current');
        exit();
    }
} else {
    header('location: home.php?vote=current');
    exit();
}
