<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors','On'); 
require_once 'console.class.php';

ignore_user_abort(true);
set_time_limit(0);

if( !isset($_POST['dir']) || $_POST['dir'] == '' ) {
	$_POST['dir'] = __DIR__;
}

$objConsole = new console($_POST['command'], $_POST['dir']);
$objConsole->execute();
?>