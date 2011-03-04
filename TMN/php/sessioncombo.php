<?php
/**
 * Combofill.php - takes 'mode': a POST variable
 * @param mode: the database table you wish to retrieve
 * 
 * returns a json packet with the field names and values.
 */

include_once "dbconnect.php";
include_once "logger.php";

include_once("../lib/FirePHPCore/fb.php");

$DEBUG = true;

//Authenticate the user in GCX with phpCAS
include_once('../lib/cas/cas.php');		//include the CAS module
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) { //if your not logged into gcx quit
	if ($DEBUG) fb('Auth failed');
	die('{success: false}');
}
	
if (isset($_SESSION['phpCAS'])) {
	$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
	$xmlobject = new SimpleXmlElement($xmlstr);
	$guid = $xmlobject->authenticationSuccess->attributes->ssoGuid;
} else {
	if ($DEBUG) fb('No Guid');
	die('{success: false}');
}

//connect to database
$connection = db_connect();
//set the log path
$LOGFILE = "logs/sessioncombo.log";

//fetch the parameter from POST
$tablename		= 'Tmn_Sessions';
$aussie_form	= ($_POST['aussie_form'] == 'true' ? true : false);
$home_assignment= ($_POST['home_assignment'] == 'true' ? true : false);
$overseas_form	= ($_POST['overseas_form'] == 'true' ? true : false);

//check for sql injection by finding spaces in the parameter
$issql = true;
if (!strstr($tablename, ' ')) {
	$issql = false;
}

//if the request is invalid
if ($tablename == 'User_Profiles' || $tablename == 'Sessions' || $tablename == 'Auth_Table' || $tablename == 'Authorising' || $issql) {
	if ($DEBUG) fb('Wrong Table');
	die('{success: false}');
}

//grab the users info
$userrows = "SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID = '". $guid ."'";
$userrows = mysql_query($userrows);

if (mysql_num_rows($userrows) == 1) {
	$userrow = mysql_fetch_assoc($userrows);
	$fan = $userrow['FIN_ACC_NUM'];
} else {
	if ($DEBUG) fb('User Conflict'); fb($userrows);
	die('{success: false}');
}
	
//form the sql statement
if ($aussie_form) {
	$rows = "SELECT SESSION_ID, DATE_MODIFIED FROM Tmn_Sessions WHERE FAN = ". $fan . " AND HOME_ASSIGNMENT_SESSION_ID = NULL AND INTERNATIONAL_ASSIGNMENT_SESSION_ID = NULL";
} elseif ($overseas_form) {
	if ($home_assignment) {
		$rows = "SELECT SESSION_ID, DATE_MODIFIED FROM Tmn_Sessions WHERE FAN = ". $fan . " AND HOME_ASSIGNMENT_SESSION_ID = NULL AND INTERNATIONAL_ASSIGNMENT_SESSION_ID != NULL";
	} else {
		$rows = "SELECT SESSION_ID, DATE_MODIFIED FROM Tmn_Sessions WHERE FAN = ". $fan . " AND HOME_ASSIGNMENT_SESSION_ID != NULL AND INTERNATIONAL_ASSIGNMENT_SESSION_ID = NULL";
	}
} else {
	if ($DEBUG) fb('Form not Aussie or Overseas');
	die('{success: false}');
}
$rows = mysql_query($rows);

if (mysql_num_rows($userrows) > 0) {
	
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
	echo '{	sessions:['.$returndata.'] }';

} else {
	if ($DEBUG) fb('Empty Query');
	die('{success: false}');
}

//$connection.close();


?>