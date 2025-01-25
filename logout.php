<?php
require_once 'init.php';
require_once 'classes/CustomSessionHandler.php';
require_once 'classes/User.php';

$user = new User();
$user->logout();

header('location: index.php');
exit();
?>