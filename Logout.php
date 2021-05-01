<?php
session_start();
$session_name = session_name();
$_SESSION = array();
session_destroy();
header('Location: LoginPage.php');
?>
