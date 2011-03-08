<?php

include_once('TmnSession.php');

//set the log path
$LOGFILE = "logs/session_crud.log";

if (isset($_POST['mode'])) {
	
	$crud		= $_POST['mode'];
		
	if ($crud == 'r') {
		
	} elseif ($crud == 'u') {
		
	} elseif ($crud == 'd') {
		
	}
	
} else {
	fb('Invalid params');
	die('{success: false}');
}

?>