<?php
require_once 'ket_noi.php';
startSession();

// Destroy session
session_destroy();

// Redirect to home
redirect('index.php');
?>