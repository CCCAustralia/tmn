<?php
/**
 * Combofill.php - takes 'mode': a POST variable
 * @param mode: the database table you wish to retrieve
 * 
 * returns a json packet with the field names and values.
 */

include_once "dbconnect.php";
include_once "logger.php";

//connect to database
$connection = db_connect();
//set the log path
$LOGFILE = "logs/combofill.log";

//fetch the parameter from POST
$tablename = $_POST['mode'];

//check for sql injection by finding spaces in the parameter
$issql = true;
if (!strstr($tablename, ' '))
	$issql = false;

//if the request is invalid
if ($tablename == 'User_Profiles' || $tablename == 'Sessions' || $tablename == 'Authorising' || $issql)
	die();

//form the sql statement
$rows = "SELECT * FROM ".$tablename;
$rows = mysql_query($rows);

//form the returned json with the sql result:
//iterate through each returned row
for ($i = 0; $i < mysql_num_rows($rows); $i++) {
	$r = mysql_fetch_assoc($rows);
	$returndata .= "{";
	//iterate through each field in the row
	foreach ($r as $k=>$v) {
		$returndata .= "\"".$k."\": \"".$r[$k]."\",";
	}
	$returndata = trim($returndata, ",");
	$returndata .= "},";
}

//trim
$returndata = trim($returndata,",");

//return
echo '{	'.$tablename.':['.$returndata.'] }';

//$connection.close();


?>