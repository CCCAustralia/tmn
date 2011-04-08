<?php
$DEBUG = 1;

if($DEBUG) require_once("../../lib/FirePHPCore/fb.php");
include_once "../dbconnect.php";
include_once "../logger.php";
include_once("../FinancialSubmitter.php");
include_once("../classes/TmnCrudSession.php");


//Authenticate the user in GCX with phpCAS
include_once('../../lib/cas/cas.php');		//include the CAS module
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('{success: false}');

if($DEBUG) ob_start();		//enable firephp logging

if (isset($_SESSION['phpCAS'])) {
	$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
	$xmlobject = new SimpleXmlElement($xmlstr);
	$guid = $xmlobject->authenticationSuccess->attributes->ssoGuid;
}

$connection = db_connect();
$LOGFILE = "logs/tmn-viewer-backend.log";

if (!isset($_POST["mode"]))
	die('{"success": false}');

if ($_POST["mode"] == "load") {
	//if ($DEBUG) {$guid="test";}
	$rows = "SELECT SESSION_ID, SESSION_NAME FROM Tmn_Sessions WHERE AUTH_SESSION_ID IN (SELECT AUTH_SESSION_ID FROM Auth_Table WHERE ";
	$rows .= "(AUTH_USER = '"	.$guid."' ".																															" && FINANCE_RESPONSE = 'Pending') || ";
	$rows .= "(AUTH_LEVEL_1 = '".$guid."' ".											"&& LEVEL_2_RESPONSE = 'Pending' ".		"&& LEVEL_3_RESPONSE = 'Pending'".		" && FINANCE_RESPONSE = 'Pending') || ";
	$rows .= "(AUTH_LEVEL_2 = '".$guid."' ".	"&& LEVEL_1_RESPONSE = 'Yes' ".													"&& LEVEL_3_RESPONSE = 'Pending'".		" && FINANCE_RESPONSE = 'Pending') || ";
	$rows .= "(AUTH_LEVEL_3 = '".$guid."' ".	"&& LEVEL_1_RESPONSE = 'Yes' ".			"&& LEVEL_2_RESPONSE = 'Yes'".													" && FINANCE_RESPONSE = 'Pending'))";
	//TODO: USE PREPAREDSTATEMENTS
	if($DEBUG) {fb($rows);}
	$rows = mysql_query($rows);
	$returndata = "";
	
	for ($i = 0; $i < mysql_num_rows($rows); $i++) {
		$r = mysql_fetch_assoc($rows);
		$returndata .= "{";
		foreach ($r as $k=>$v) {
			$returndata .= "\"".$k."\": \"".$r[$k]."\",";
		}
		$returndata = trim($returndata, ",");
		$returndata .= "},";
	}
	
	$returndata = trim($returndata,",");
	
	echo '{"data":['.$returndata.'] }';
} else if ($_POST["mode"] == "get") {
	
	$crudsession = new TmnCrudSession("../logs/authviewer.log", (int)$_REQUEST["session"]);
	
	$crudsession->retrieve();
	$cruddata = $crudsession->produceJsonForDisplay();
	fb("cruddata:");
	fb($cruddata);
	$cruddata = (array)json_decode($cruddata);
	
	//TODO: ADD AUTH DATA FROM AUTH_TABLE(TO BE MODIFIED, WITH SUBMITTER)
	//$cruddata['session'] = $cruddata['session_id'];
	$returndata = array("success" => true, "tmn_data" => array('aussie-based'=>$cruddata));
	
	echo json_encode($returndata);
	
} else {			//if its an invalid mode tell the front end it failed
	echo '{"success": false}';
}

?>