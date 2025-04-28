<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../classes/CustomSessionHandler.php';

$session = CustomSessionHandler::getInstance();

// Clear both error and success messages
$session->clearError();
$session->clearSuccess();

// Return success response
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
?> 