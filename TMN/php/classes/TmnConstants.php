<?php


function getConstants($keyarray) {
	if (file_exists('dbconnect.php')) {			include_once 'dbconnect.php';}
	if (file_exists('../dbconnect.php')) {		include_once '../dbconnect.php';}
	if (file_exists('php/dbconnect.php')){		include_once 'php/dbconnect.php';}
	
	$connection = db_connect();
	$sql = "SELECT * FROM `Constants` WHERE 1";
	$sql = mysql_query($sql);
	$db_values = mysql_fetch_assoc($sql);
	$returnarray = array();
	foreach ($keyarray as $key) {
		$returnarray[$key] = $db_values[$key];
	}
	return $returnarray;
}


?>