<?php
require_once __DIR__ . '/../../classes/Ballot.php';

if(isset($_POST['id'])) {
    $ballot = Ballot::getInstance();
    $result = $ballot->movePositionUp($_POST['id']);
    echo json_encode($result);
} 