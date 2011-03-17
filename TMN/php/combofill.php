<?php
/**
 * Combofill.php - takes 'mode': a POST variable
 * @param mode: the database table you wish to retrieve
 * 
 * returns a json packet with the field names and values.
 */

include_once('Tmn.php');
include_once('TmnSessionComboLoader.php');


//set the log path
$LOGFILE = "logs/combofill.log";

if (isset($_POST['mode'])) {
	
	try {
		
		$tmn = new Tmn($LOGFILE);
		
		if ($tmn->isAuthenticated()) {
		
			$tablename		= $_POST['mode'];
				
			if ($_POST['mode'] == 'Tmn_Sessions') {
				
				if (isset($_POST['aussie_form']) && isset($_POST['overseas_form']) && isset($_POST['home_assignment'])) {
					
					$aussie_form		= ($_POST['aussie_form'] == 'true' ? true : false);
					$overseas_form		= ($_POST['overseas_form'] == 'true' ? true : false);
					$home_assignment	= ($_POST['home_assignment'] == 'true' ? true : false);
					
					$comboLoader	= new TmnSessionComboLoader($LOGFILE, $tmn->getUser(), "Tmn_Sessions", $aussie_form, $overseas_form, $home_assignment);
					
				} else {
					fb('Invalid get_session params');
					die('{success: false}');
				}
				
			} else {
				$comboLoader	= new TmnComboLoader($LOGFILE, $tablename);
			}
		
			echo $comboLoader->produceJson();
		
		}
		
	} catch (Exception $e) {
		Reporter::newInstance($LOGFILE)->exceptionHandler($e);
	}
	
} else {
	fb('Invalid params');
	die('{success: false}');
}

/*
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

*/

?>