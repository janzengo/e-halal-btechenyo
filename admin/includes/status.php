<?php
// includes/status.php

include 'includes/conn.php';

// Fetch the current election status
$sql = "SELECT status FROM election_status ORDER BY id DESC LIMIT 1";
$query = $conn->query($sql);
$row = $query->fetch_assoc();
$current_status = $row ? $row['status'] : 'off';

// Redirection based on the status
$current_page = basename($_SERVER['PHP_SELF']);

if ($current_status == 'off') {
    // If the election is off, redirect to the general tab
    if ($current_page != 'pre_election.php') {
        header('Location: pre_election.php');
        exit();
    }
} elseif ($current_status == 'pending') {
    // During the pending phase, ensure the user is in the correct pre-election tab
    if ($current_page == 'pre_election_positions.php' && $_SESSION['general_config_complete'] !== true) {
        if ($current_page != 'pre_election.php') {
            $_SESSION['error'] = 'Please complete the General tab first.' . $_SESSION['general_config_complete'];
            header('Location: pre_election.php');
            exit();
        }
    } elseif ($current_page == 'pre_election_candidates.php' && $_SESSION['positions_config_complete'] !== true) {
        $_SESSION['error'] = 'Please complete the Positions tab first.';
        header('Location: pre_election_positions.php');
        exit();
    } elseif (in_array($current_page, ['home.php', 'votes.php', 'voters.php', 'positions.php', 'candidates.php', 'ballot.php', 'logs.php', 'officers.php', 'partylists.php'])) {
        header('Location: pre_election.php');
        exit();
    }
} elseif (in_array($current_status, ['on', 'paused'])) {
    // Redirect to the main dashboard during an active or paused election
    if (strpos($current_page, 'pre_election') !== false) {
        header('Location: home.php');
        exit();
    }
}
?>