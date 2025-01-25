<?php
// Get the host dynamically
$host = $_SERVER['HTTP_HOST'];

// Define base URL for the application dynamically
if (!defined('BASE_URL')) define('BASE_URL', 'http://' . $host . '/e-halal/');
?>
