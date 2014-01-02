<?php
$DEBUG = 1;

include_once("logger.php");
include_once("dbconnect.php");
include_once("FinancialSubmitter.php");
if($DEBUG) require_once("../lib/FirePHPCore/fb.php");

//Authenticate the user in GCX with phpCAS
include_once('../lib/cas/cas.php');		//include the CAS module
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'thekey.me',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('{success: false}');

if($DEBUG) ob_start();		//enable firephp logging
/*
if (isset($_SESSION['phpCAS'])) {
	$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
	$xmlobject = new SimpleXmlElement($xmlstr);
	$guid = $xmlobject->authenticationSuccess->attributes->ssoGuid;
}
*/

$formdata = $_POST;
if ($DEBUG) fb($formdata);
$fs = new FinancialSubmitter($formdata, $DEBUG);
echo $fs->submit();
if ($DEBUG) fb($fs);
die();

?>