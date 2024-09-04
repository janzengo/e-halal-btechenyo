<?php
if(!isset($conn)) {
    $conn = new mysqli('localhost', 'root', '', 'e-halal');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}
