<?php

/*******************************************                                                        
# index.php - Generic index for PHP GCX SSO login                    
*******************************************/

//GCX login
include_once('../../lib/cas/cas.php');		//include the CAS module

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

include_once('../classes/TmnAuthenticator.php');
$LOGFILE	= "../logs/TmnAuthenticatorClass-test.log";
$DEBUG = 1;

	//Constructor test

fb("Constructor Test");
try {
	$authObj	= TmnAuthenticator::getInstance($LOGFILE);
} catch (Exception $e) {
	Reporter::newInstance($LOGFILE)->exceptionHandler($e);
}

/*
 * Expected output
 * 
 * Console Output:
 * Constructor Test
 * 
 * Screen Output:
 * 
 */

/*
 * Expected output (With above GCX code commented out)
 * 
 * Console Output:
 * Constructor Test
 * <filename>; ln <line num>; Fatal Exception; Authentication Exception: User Not Authenticated
 * 
 * Screen Output:
 * {success:false}
 * 
 */

/*
 * Expected output (with guid cookie missing)
 * 
 * Console Output:
 * Constructor Test
 * <filename>; ln <line num>; Fatal Exception; Authentication Exception: User's GUID Not Found
 * 
 * Screen Output:
 * {success:false}
 * 
 */

	//Auth test

fb("Auth Test");
fb("isAuthenticated(): " . $authObj->isAuthenticated());
fb("getGuid(): " . $authObj->getGuid());
fb("getEmail(): " . $authObj->getEmail());

/*
 * Expected output
 * 
 * Console Output:
 * Auth Test
 * isAuthenticated(): 1
 * getGuid(): <your guid>
 * getEmail(): <your email>
 * 
 * Screen Output:
 * 
 */

	//Singleton test

fb("Singleton Test");
fb("this:"); fb($authObj);
fb("::getInstance():"); fb(TmnAuthenticator::getInstance($LOGFILE));
fb("clone authObj: ");
try {
	clone $authObj;
} catch (Exception $e) {
	Reporter::newInstance($LOGFILE)->exceptionHandler($e);
}

/*
 * Expected output
 * 
 * Console Output:
 * Singleton Test
 * this:
 * <instance of TmnAuth with your GCX data>
 * ::getInstance():
 * <same instance of TmnAuth with your GCX data>
 * clone authObj:
 * <filename>; ln <line num>; Light Exception; Authentication Exception: TmnAuth Cannot be cloned
 * 
 * Screen Output:
 * 
 */

?>
