<?php
include_once('php/classes/TmnConstants.php');
$constants = getConstants(array("VERSIONNUMBER"));
$DEBUG = 1;
$NEWVERSION = 1;
$VERSIONNUMBER = $constants['VERSIONNUMBER'];//"2-1-1";

if ($DEBUG) {
	$force_debug = "-debug";
} else {
	$force_debug = "";
}

if ($NEWVERSION && $DEBUG == 1){
	$force_reload = "?" . time();
} else if ($NEWVERSION && $DEBUG == 0) {
	$force_reload = "?" . $VERSIONNUMBER;
} else {
	$force_reload = "";
}

/*******************************************
#                                                             
# index.php - Generic index for PHP GCX SSO login                    
*******************************************/

//GCX login
include_once('lib/cas/cas.php');		//include the CAS module
include_once('php/dbconnect.php');
include_once('php/classes/email.php');
//phpCAS::setDebug();			//Debugging mode
phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
$_CAS_CLIENT_CALLED = 1;
phpCAS::setNoCasServerValidation();	//no SSL validation for the CAS server
phpCAS::forceAuthentication();		//require the user to log in to CAS




//user is now authenticated by the CAS server and the user's login name can be read with phpCAS::getUser()

//logout if desired
if (isset($_REQUEST['logout'])) {
	phpCAS::logout();
}


//fetch a ticket if absent
if ($_REQUEST['ticket'] == '' && $_REQUEST['id'] == '')
{
//echo GetMainBaseFromURL(curPageURL()). "<br />";
    header("Location: https://signin.mygcx.org/cas/login?service=".curPageURL());
}

$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
$xmlobject = new SimpleXmlElement($xmlstr);

//check if they are a valid user (If not show the rego page)
$tmn_connection = db_connect();
$sql			= mysql_query("SELECT GUID FROM User_Profiles WHERE GUID='" . (string)($xmlobject->authenticationSuccess->attributes->ssoGuid) . "';", $tmn_connection);
if (mysql_num_rows($sql) == 1){
	//if the user has a valid email address update email address in the database when they log in
	if (Email::validateAddress(phpCAS::getUser())) {
		$sql = mysql_query("UPDATE `User_Profiles` SET EMAIL='" . phpCAS::getUser() . "' WHERE GUID='". (string)($xmlobject->authenticationSuccess->attributes->ssoGuid)."';", $tmn_connection);
	}
		
	//ouput tmn page
	echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
	
	if ($DEBUG) {
		echo '<link rel="stylesheet" type="text/css" href="lib/resources/css/loading.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/resources/css/ext-all.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/statusbar/css/statusbar.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/resources/css/customstyles.css'.$force_reload.'" />';
	} else {
		echo '<link rel="stylesheet" type="text/css" href="lib/resources/css/tmn-all.css'.$force_reload.'" />';
	}
	
	echo '<title>TMN</title></head><body><div id="loading-mask"></div><div id="loading"><span id="loading-message">Loading. Please wait...</span></div><script type="text/javascript">document.getElementById("loading-message").innerHTML = "Loading Ext Library...";</script>';
	
	if ($DEBUG) {
		echo '<script type="text/javascript" src="lib/ext-base.js'.$force_reload.'"></script><script type="text/javascript" src="lib/ext-all'.$force_debug.'.js'.$force_reload.'"></script>';
	} else {
		echo '<script type="text/javascript" src="lib/ext.js'.$force_reload.'">';
	}
	
	echo '<script type="text/javascript">document.getElementById("loading-message").innerHTML = "Loading Custom Libraries...";</script>';
	
	if ($DEBUG) {
		echo '<script type="text/javascript" src="lib/DateRangeValidationType.js'.$force_reload.'"></script><script type="text/javascript" src="lib/statusbar/StatusBar.js'.$force_reload.'"></script><script type="text/javascript" src="lib/statusbar/ValidationStatus.js'.$force_reload.'"></script><script type="text/javascript" src="lib/Printer-all.js'.$force_reload.'"></script><script type="text/javascript" src="lib/iconcombo/Ext.ux.IconCombo.js'.$force_reload.'"></script>';
	} else {
		echo '<script type="text/javascript" src="lib/custom-libraries-all.js'.$force_reload.'"></script>';
	}
	
	echo '<script type="text/javascript">document.getElementById("loading-message").innerHTML = "Loading TMN Form...";</script>';
	
	if ($DEBUG) {
		echo '<script type="text/javascript" src="ui/AuthorisationPanel.js'.$force_reload.'"></script><script type="text/javascript" src="ui/SummaryPanel.js'.$force_reload.'"></script><script type="text/javascript" src="ui/PrintForm.js'.$force_reload.'"></script><script type="text/javascript" src="ui/InternalTransfers.js'.$force_reload.'"></script><script type="text/javascript" src="ui/FinancialDetailsForm.js'.$force_reload.'"></script><script type="text/javascript" src="ui/PersonalDetailsForm.js'.$force_reload.'"></script><script type="text/javascript" src="ui/TmnView.js'.$force_reload.'"></script><script type="text/javascript" src="ui/TmnController.js'.$force_reload.'"></script>';
	} else {
		echo '<script type="text/javascript" src="ui/tmn-all.js'.$force_reload.'"></script>';
	}
	
	echo '<center><div id="tmn-cont"></div></center><!-- Fields required for history management --><form id="history-form" class="x-hidden"><input type="hidden" id="x-history-field" /><iframe id="x-history-frame"></iframe></form></body></html>';
} else {
	echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html><head>	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	<style type="text/css">	.body-look{		padding:10px;		border-color: #8db2e3;		background-color: #deecfd;		font: normal 14px tahoma,arial,helvetica;		color: #416aa3;	}	.title-look{		padding:6px;		background-image: url(lib/resources/images/default/panel/top-bottom.gif);		color:#15428b;		font:bold 14px tahoma,arial,verdana,sans-serif;	}	</style>	<title>User Not Found!</title></head><body>	<center>		<div class="title-look" style="position:relative;left:20px;width:608px;text-align:left;">User Not Found!</div>		<div class="body-look" style="position:relative;left:20px;width:600px;">You where not found in our system.<br />If you think you should be able to submit a TMN then register your details for processing.<br />Our Security checks are usually take One buisness day to complete.</div>	</center>	<br />	<div class="title-look">Submit Details for Registration</div>	<form class="body-look" name="security_scan" action="security_scan.php" method="post">		<label for="email">GCX Email Address (this is how we will contact you, if it is wrong go to <a href="https://signin.mygcx.org/cas/selfservice.htm">https://signin.mygcx.org/cas/selfservice.htm</a> and update it. Then try to register again.): </label><input type="text" name="email" value="' . phpCAS::getUser() . '" style="position:relative;left:121px;" readonly /> <br />		<label for="fan">Financial Account Number: </label><input type="text" name="fan" style="position:relative;left:26px;" /> <br />		<input type="submit" value="Submit" />	</form></body></html>';
}

function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}
?>
