<?php

$DEBUG = 1;
$NEWVERSION = 0;
$VERSIONNUMBER = "0-0-1";

if ($DEBUG) {
	$force_debug = "-debug";
} else {
	$force_debug = "";
}

if ($NEWVERSION){
	$force_reload = "?" . $VERSIONNUMBER;
} else {
	$force_reload = "";
}

include_once("../classes/TmnCrudSession.php");
include_once("../classes/TmnCrudUser.php");

/*********************************************************
# GENERIC PHP CAS SSO/FIREBUG/GUID INITIALISATION SCRIPT #                 
*********************************************************/

//GCX login
include_once('../dbconnect.php');								//include the database connect class
include_once("../../lib/FirePHPCore/fb.php");					//include the firebug module
if($DEBUG) ob_start();											//enable firephp logging
include_once('../../lib/cas/cas.php');							//include the CAS module
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('{success: false}');

if (isset($_SESSION['phpCAS'])) {
	$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
	$xmlobject = new SimpleXmlElement($xmlstr);
	$guid = $xmlobject->authenticationSuccess->attributes->ssoGuid;
}

/*********************************************************
# GENERIC PHP CAS SSO/FIREBUG/GUID INITIALISATION SCRIPT #                 
*********************************************************/
$guid = "test";
//Create the objects required for authorisation
try {
	$logfile			= "../logs/authprocessor.php.log";								//required for logging
	$user				= new TmnCrudUser($logfile, $guid);								//the user object
	$session			= new TmnCrudSession($logfile, (int)$_POST['session']);			//the session object
} catch (Exception $e) {
	die($e->getMessage());
}

if ($_POST['response'] == "Yes" || $_POST['response'] == "No") {
	try {
		$session->authorise($user, $_POST['response']);
		echo json_encode(array("success" => true));
	} catch (Exception $e) {
		throw new LightException("Authorisation Failed: ".$e->getMessage());
	}
} else {
	throw new LightException("Authorisation Failed: No response provided.");
}
?>