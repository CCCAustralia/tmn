<?php

/*******************************************                                                        
# index.php - Generic index for PHP GCX SSO login                    
*******************************************/

//GCX login
include_once('../lib/cas/cas.php');		//include the CAS module
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

include_once('TmnUser.php');
$LOGFILE	= "TmnUserClass-test.log";
$DEBUG		= 1;

	//Constructor test
	
fb("Constructor Test");
try {
	$user	= new TmnUser($LOGFILE);
} catch (Exception $e) {
	Reporter::newInstance("logs/default.log")->exceptionHandler($e);
}


/*
 * Expected output
 * 
 * Console Output:
 * Constructor Test
 * [<now>] User Authenticated: guid = 691EC152-0565-CEF4-B5D8-************
 * 
 * Screen Output:
 * 
 */

	//Access test

fb("Access Test");
fb("getFan(): " . $user->getFan());
fb("getSpouseGuid(): " . $user->getSpouseGuid());
try {
	fb("setSpouseGuid('test'): " . $user->setSpouseGuid('test'));
} catch (Exception $e) {
	Reporter::newInstance("logs/default.log")->exceptionHandler($e);
}
fb("getSpouseGuid(): " . $user->getSpouseGuid());
fb("getMpdGuid(): " . $user->getMpdGuid());
try {
	fb("setMpdGuid('testuserguid'): " . $user->setMpdGuid('testuserguid'));
} catch (Exception $e) {
	Reporter::newInstance("logs/default.log")->exceptionHandler($e);
}
fb("getMpdGuid(): " . $user->getMpdGuid());
fb("isAdmin(): " . $user->isAdmin());
fb("resetUser()");$user->resetUser();
fb("getFan(): " . $user->getFan());

/*
 * Expected output
 * 
 * Console Output:
 * Access Test
 * getFan(): 1012299
 * getSpouseGuid():
 * setSpouseGuid('me'):
 * getSpouseGuid(): me
 * getMpdGuid():
 * setMpdGuid('you'):
 * getMpdGuid(): you
 * isAdmin(): 1
 * resetUser()
 * getFan(): 
 * 
 * Screen Output:
 * 
 */


	//CRUD test
fb("CRUD Test");
fb("CREATE");
try {
	fb("setGuid('duplicate')"); $user->setGuid('duplicate');
	fb("createUser()"); $user->createUser();
} catch (Exception $e) {
	Reporter::newInstance("logs/default.log")->exceptionHandler($e);
}

fb("RETRIEVE");
try {
	fb("Error inducing setGuid()"); $user->setGuid('error-inducer');
	fb("load via setGuid('test')"); $user->setGuid('test');
	fb("getSpouseGuid(): " . $user->getSpouseGuid());
} catch (Exception $e) {
	Reporter::newInstance("logs/default.log")->exceptionHandler($e);
}

fb("UPDATE");
try {
	fb("setMpdGuid('testuserguid')"); $user->setMpdGuid('testuserguid');
	fb("updateUser()"); $user->updateUser();
	fb("resetUser()");$user->resetUser();
	fb("retrieveUser()"); $user->retrieveUser();
	fb("getMpdGuid(): " . $user->getMpdGuid());
} catch (Exception $e) {
	Reporter::newInstance("logs/default.log")->exceptionHandler($e);
}

/*
fb("DELETE");
try {
	fb("setGuid('duplicate')"); $user->setGuid('duplicate');
	fb("deleteUser()");$user->deleteUser();
	fb("retrieveUser()"); $user->retrieveUser();
} catch (Exception $e) {
	Reporter::newInstance("logs/default.log")->exceptionHandler($e);
}
*/

/*
 * Expected output
 * 
 * Console Output:
 * CRUD Test
 * CREATE
 * setGuid('duplicate')
 * createUser()
 * RETRIEVE
 * Error inducing setGuid()
 * [<now>] <path>/TmnUser.php; ln <line no.>; Light Exception; User Exception: Cannot Load User with guid=error-inducer. The previous guid was restored. The following Exception was thrown when load was attempted:User Exception: On Retrieve, User Not Found
 * <path>/TmnUser.php; ln <line no.>; Light Exception; User Exception: Cannot Load User with guid=error-inducer. The previous guid was restored. The following Exception was thrown when load was attempted:User Exception: On Retrieve, User Not Found
 * load via setGuid('test')
 * getSpouseGuid():
 * UPDATE
 * setMpdGuid('testuserguid')
 * updateUser()
 * resetUser()
 * retrieveUser()
 * getMpdGuid(): testuserguid
 * setGuid('duplicate')
 * deleteUser()
 * retrieveUser()
 * [<now>] <path>/TmnUser.php; ln <line no.>; Light Exception; User Exception: Cannot Load User with guid=error-inducer. The previous guid was restored. The following Exception was thrown when load was attempted:User Exception: On Retrieve, User Not Found
 * <path>/TmnUser.php; ln <line no.>; Light Exception; User Exception: Cannot Load User with guid=error-inducer. The previous guid was restored. The following Exception was thrown when load was attempted:User Exception: On Retrieve, User Not Found
 * 
 * Screen Output:
 * 
 */

?>
