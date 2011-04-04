<?php

$DEBUG = 1;

include_once("FinancialProcessor.php");
include_once("../lib/FirePHPCore/fb.php");

//Authenticate the user in GCX with phpCAS
include_once('../lib/cas/cas.php');		//include the CAS module
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('{success: false}');

$financial_data = json_decode(stripslashes($_POST['financial_data']), true);

if($DEBUG && $financial_data == '')	$financial_data = json_decode(stripslashes($_REQUEST['financial_data']), true);

$processor = new FinancialProcessor($financial_data, $DEBUG);

echo $processor->process();
?>