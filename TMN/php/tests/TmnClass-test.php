<?php

$DEBUG = 1;

/*******************************************                                                        
# index.php - Generic index for PHP GCX SSO login                    
*******************************************/

//GCX login
include_once('../../lib/cas/cas.php');		//include the CAS module
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
//echo GetMainBaseFromURL(curPageURL()). "<br />";
    header("Location: https://signin.mygcx.org/cas/login?service=".curPageURL());
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


/*******************************************                                                        
# Test Code                 
*******************************************/

include_once('../Tmn.php');
$LOGFILE	= "TmnClass-test.log";

	//Constructor test
	
fb("Constructor Test");
$tmnObj	= new Tmn($LOGFILE);

	//Auth test

fb("Auth Test");
fb("isAuthenticated(): " . $tmnObj->isAuthenticated());
fb("getAuthGuid(): " . $tmnObj->getAuthGuid());
fb("getGuid(): " . $tmnObj->getAuthGuid());
fb("getEmail(): " . $tmnObj->getEmail());

fb("setGuid('me')"); $tmnObj->setGuid('me');
fb("getGuid(): " . $tmnObj->getAuthGuid());
fb("setGuid('me')"); $tmnObj->setGuid('me');

	//Database test

fb("Database Test");

?>
