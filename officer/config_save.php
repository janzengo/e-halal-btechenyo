<?php
include 'includes/session.php';

$return = 'home.php';
if (isset($_GET['return'])) {
    $return = $_GET['return'];
}

if (isset($_POST['save'])) {
    $title = $_POST['title'];

    // Write to the config.ini file securely
    $file = 'config.ini';
    $content = 'election_title = ' . $title;

    // Check if the file is writable
    if (is_writable($file)) {
        // Write to the file using file locking to prevent race conditions
        if (file_put_contents($file, $content, LOCK_EX)) {
            $_SESSION['success'] = 'Election title updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update election title';
        }
    } else {
        $_SESSION['error'] = 'The config file is not writable';
    }
} else {
    $_SESSION['error'] = "Fill up config form first";
}

header('location: ' . $return);
