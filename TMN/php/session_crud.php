<?php

include_once('Tmn.php');

//set the log path
$LOGFILE	= "logs/session_crud.log";
$tmn		= new Tmn($LOGFILE);

if (isset($_POST['mode'])) {
	
	if ($tmn->isAuthenticated()) {
		
		$crud		= $_POST['mode'];

		if ($crud == 'c') {
			$response = array("success"=>true,"data"=>array("session_id"=>75));
			echo json_encode($response);
		} elseif ($crud == 'r') {
			echo '{success: true}';
		} elseif ($crud == 'u') {
			echo '{success: true}';
		} elseif ($crud == 'd') {
			echo '{success: true}';
		}
		
	} else {
		fb('Not Authenticated');
		die('{success: false}');
	}
	
} else {
	fb('Invalid params');
	die('{success: false}');
}

?>