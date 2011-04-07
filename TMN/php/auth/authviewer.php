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
	//$crudsession->setField("SESSION_ID", $_POST["session"]);
	$crudsession->retrieve();
	$cruddata = $crudsession->produceJson();	//produce json for display
	/*$cruddata['session'] = $cruddata['session_id'];
	foreach ($cruddata as $key=>$value) {
		$cruddata[strtoupper($key)] = $value;
	}*/
	fb("cruddata:");
	fb($cruddata);
	//$cruddata = "{\"success\": true, \"tmn_data\": {\"aussie-based\":".$cruddata."}}";
	
	//$finsub = new FinancialSubmitter($cruddata, 1);
	//$returndata = $finsub->submit();
	//fb("finsub->submit():"); fb($returndata);
						//grab the response of the submittion process
						
	/*
	$obj = json_decode($returndata, true);
	if ($obj["success"] == "true" || $obj["success"] == true){	//if the reprocessing worked prepare a packet so that it looks like it came from the database
		$json["aussie-based"] = $obj["tmn_data"];					//put the data into an associative array field called aussie-based
		
		$return["success"] = true;									//add a success field to the return packet
		$return["tmn_data"] = $json;								//copy the json field into the return packet
		$returndata = json_encode($return);									//return the encoded packet
	}
	*/
	$cruddata = (array)json_decode($cruddata);
	//TODO: ADD AUTH DATA FROM AUTH_TABLE(TO BE MODIFIED, WITH SUBMITTER)
	//$cruddata['session'] = $cruddata['session_id'];
	$returndata = array("success" => true, "tmn_data" => array('aussie-based'=>$cruddata));
	$returndata = json_encode($returndata);
	fb("returndata:");
	fb($returndata);
	//TODO: HANDLE OVERSEAS MISSIONARIES
	echo $returndata;
	
} else {			//if its an invalid mode tell the front end it failed
	echo '{"success": false}';
}

?>