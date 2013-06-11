<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors','On'); 
require_once 'console.class.php';


$objConsole = new console($_POST['command']);
$objConsole->execute();
?>