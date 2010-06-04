<?php

include_once("logger.php");
include_once("dbconnect.php");
include_once("../lib/FirePHPCore/fb.php");

//Authenticate the user in GCX with phpCAS
include_once('../lib/cas/cas.php');		//include the CAS module
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('{success: false}');

//grab guid
if (isset($_SESSION['phpCAS'])) {
	$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
	$xmlobject = new SimpleXmlElement($xmlstr);
	$guid = $xmlobject->authenticationSuccess->attributes->ssoGuid;
}

$LOGFILE = "./logs/submit_tmn.log";


$connection = db_connect();

//session needs to be FAN, remove when multiple sessions is implemented
$sql = mysql_query('SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID="'.$guid.'";');
$res = mysql_fetch_assoc($sql);
$session = $res['FIN_ACC_NUM'];
$jsonObj = json_decode(stripslashes($_POST['json']), true);

if (isset($jsonObj['aussie-based'])) {
	$jsonObj = $jsonObj['aussie-based'];
} else if (isset($jsonObj['home-assignment'])) {
	$jsonObj = $jsonObj['home-assignment'];
} else {
	//the format of the json is wrong, tell the ui program to change what they send
}

$sql = mysql_query('SELECT * FROM Sessions WHERE SESSION_ID="'.$session.'";');

if (mysql_num_rows($sql) == 1){
	$sql = 'UPDATE Sessions SET GUID="'.$guid.'",SESSION_NAME="'.$jsonObj['firstname'].' '.$jsonObj['surname'].'",FIN_ACC_NO="'.$jsonObj['fan'].'",JSON="'.$_POST['json'].'" WHERE SESSION_ID="'.$session.'";';
	$res = mysql_query($sql);
} else {
	$sql = 'INSERT INTO Sessions (SESSION_ID,GUID,SESSION_NAME,FIN_ACC_NO,JSON) VALUES ("'.$session.'", "'.$guid.'", "'.$jsonObj['firstname'].' '.$jsonObj['surname'].'", "'.$jsonObj['fan'].'", "'.$_POST['json'].'");';
	//$sql = 'INSERT INTO Sessions (SESSION_ID,JSON) VALUES ("'.$session.'", "'.$_POST['json'].'");';
	$res = mysql_query($sql);
}

?>