<?php

$DEBUG = 0;
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

/*******************************************
#                                                             
# index.php - Generic index for PHP GCX SSO login                    
*******************************************/

//GCX login
include_once('lib/cas/cas.php');		//include the CAS module
include_once('php/dbconnect.php');
//phpCAS::setDebug();			//Debugging mode
phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
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
    header("Location: https://signin.mygcx.org/cas/login?service=".curPageURL());
}

$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
$xmlobject = new SimpleXmlElement($xmlstr);

//check if they are a valid user (If not show the rego page)
$tmn_connection = db_connect();
$sql = mysql_query("SELECT ADMIN_TAB FROM User_Profiles WHERE GUID='".($xmlobject->authenticationSuccess->attributes->ssoGuid)."';", $tmn_connection);
if (mysql_num_rows($sql) == 1) {
	$row = mysql_fetch_assoc($sql);
	if ($row['ADMIN_TAB'] == 1) {
		echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><style type="text/css">html, body { color:#444444;font-family: Lucida,monospace;;font-size:14;height: 100%; } #loading-mask {position: absolute;top: 0;left: 0;width: 100%;height: 100%;background: #000000;z-index: 1;} #loading {position: absolute;top: 40%;left: 45%;z-index: 2;} #loading span {background: url("lib/resources/images/default/grid/loading.gif") no-repeat left center;padding: 5px 30px;display: block;}</style><link rel="stylesheet" type="text/css" href="lib/resources/css/ext-all.css'.$force_reload.'"/><link rel="stylesheet" type="text/css" href="lib/resources/css/customstyles.css'.$force_reload.'" /><title>TMN Viewer</title></head><body><div id="loading-mask"></div><div id="loading"><span id="loading-message">Loading. Please wait...</span></div><script type="text/javascript">document.getElementById("loading-message").innerHTML = "Loading Core API...";</script><script type="text/javascript" src="lib/ext-base.js'.$force_reload.'"></script><script type="text/javascript">document.getElementById("loading-message").innerHTML = "Loading Ext Library...";</script><script type="text/javascript" src="lib/ext-all'.$force_debug.'.js'.$force_reload.'"></script><script type="text/javascript">document.getElementById("loading-message").innerHTML = "Loading Custom Libraries...";</script><script type="text/javascript" src="lib/Printer-all.js'.$force_reload.'"></script><script type="text/javascript">document.getElementById("loading-message").innerHTML = "Loading TMN Viewer...";</script><script type="text/javascript" src="ui/PrintForm.js'.$force_reload.'"></script><script type="text/javascript" src="ui/viewer.js'.$force_reload.'"></script><center><div id="tmn-viewer-controls-cont"></div><div id="tmn-viewer-cont"></div></center></body></html>';
	} else {
		echo "You don't have permission to access this page. If you think you should be able to access this page, contact <a href=\"mailto:tech.team@ccca.org.au\">tech.team@ccca.org.au</a>";
	}
} else {
	echo "You don't have permission to access this page. If you think you should be able to access this page, contact <a href=\"mailto:tech.team@ccca.org.au\">tech.team@ccca.org.au</a>";
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
