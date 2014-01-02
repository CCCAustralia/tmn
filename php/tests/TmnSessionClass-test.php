<?php

/*******************************************                                                        
# index.php - Generic index for PHP GCX SSO login                    
*******************************************/

//GCX login
include_once('../../lib/cas/cas.php');		//include the CAS module
//phpCAS::setDebug();			//Debugging mode
phpCAS::client(CAS_VERSION_2_0,'thekey.me',443,'cas');	//initialise phpCAS
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
    header("Location: https://thekey.me/cas/login?service=".curPageURL());
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

include_once('../classes/TmnCrudSession.php');
$LOGFILE	= "../logs/TmnSessionClass-test.log";
$DEBUG		= 1;

	//Constructor test
	
fb("Constructor Test");
try {
	$session	= new TmnCrudSession($LOGFILE);
} catch (Exception $e) {
	Reporter::newInstance($LOGFILE)->exceptionHandler($e);
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
fb("getSessionID(): " . $session->getSessionID());
fb("setSessionID('testsession'): " . $session->setSessionID('testsession'));
fb("getSessionID(): " . $session->getSessionID());
fb("resetSession()");$session->resetSession();
fb("getSessionID(): " . $session->getSessionID());

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

	//Database Interaction test

fb("Database Interaction Test");

$jsonArray = $session->getJsonArray();
foreach ($jsonArray as $jsonString) {
	if ($jsonString != null) {
		$jsonString = str_replace(array('"ministry_levy":MINISTRY_LEVY,', '"s_ministry_levy":S_MINISTRY_LEVY,', '"transfers":[TRANSFERS],'), "", $jsonString);
		$jsonString = str_replace('"international_donations":"INTERNATIONAL_DONATIONS"', '"international_donations":0', $jsonString);
		$jsonObj = json_decode($jsonString, true);
		
		if (isset($jsonObj['aussie-based'])) {
			$id = $session->createSessionFromJson($jsonObj['aussie-based']);
			fb("Session added at: " . $id);
		} elseif (isset($jsonObj['tmn_data'])) {
			$id = $session->createSessionFromJson($jsonObj['tmn_data']);
			fb("Session added at: " . $id);
		} elseif (isset($jsonObj['international-assignment'])) {
			
			//create each session
			$ia_id = $session->createSessionFromJson($jsonObj['international-assignment']);
			fb("Session added at: " . $ia_id);
			$ha_id = $session->createSessionFromJson($jsonObj['home-assignment']);
			fb("Session added at: " . $ha_id);
			
			//update international assignment session with home assignment ref
			$session->resetSession();
			$session->setSessionID($ia_id);
			$session->retrieveSession();
			$session->setHomeAssignmentID($ha_id);
			$session->updateSession();
			
			//update home assignment session with international assignment ref
			$session->resetSession();
			$session->setSessionID($ha_id);
			$session->retrieveSession();
			$session->setInternationalAssignmentID($ia_id);
			$session->updateSession();
			
		} else {
			fb($jsonString);
			fb("unknown json type: ");
		}
	}
}

/*
 * Expected output
 * 
 * Console Output:
 * Database Interaction Test
 * Session added at: 1
 * Session added at: 2
 * Session added at: 3
 * Session added at: 4
 * Session added at: 5
 * Session added at: 6
 * Session added at: 7
 * Session added at: 8
 * Session added at: 9
 * Session added at: 10
 * Session added at: 11
 * Session added at: 12
 * Session added at: 13
 * Session added at: 14
 * Session added at: 15
 * Session added at: 16
 * Session added at: 17
 * Session added at: 18
 * Session added at: 19
 * Session added at: 20
 * Session added at: 21
 * Session added at: 22
 * Session added at: 23
 * Session added at: 24
 * Session added at: 25
 * Session added at: 26
 * Session added at: 27
 * Session added at: 28
 * Session added at: 29
 * Session added at: 30
 * Session added at: 31
 * Session added at: 32
 * Session added at: 33
 * Session added at: 34
 * Session added at: 35
 * Session added at: 36
 * Session added at: 37
 * Session added at: 38
 * Session added at: 39
 * Session added at: 40
 * Session added at: 41
 * Session added at: 42
 * Session added at: 43
 * Session added at: 44
 * Session added at: 45
 * Session added at: 46
 * Session added at: 47
 * Session added at: 48
 * Session added at: 49
 * Session added at: 50
 * Session added at: 51
 * Session added at: 52
 * Session added at: 53
 * Session added at: 54
 * Session added at: 55
 * Session added at: 56
 * Session added at: 57
 * Session added at: 58
 * Session added at: 59
 * Session added at: 60
 * Session added at: 61
 * Session added at: 62
 * Session added at: 63
 * Session added at: 64
 * Session added at: 65
 * Session added at: 66
 * Session added at: 67
 * Session added at: 68
 * Session added at: 69
 * Session added at: 70
 * Session added at: 71
 * Session added at: 72
 * Session added at: 73
 * Session added at: 74
 * Session added at: 75
 * Session added at: 76
 * Session added at: 77
 * Session added at: 78
 * Session added at: 79
 * Session added at: 80
 * Session added at: 81
 * Session added at: 82
 * Session added at: 83
 * Session added at: 84
 * Session added at: 85
 * Session added at: 86
 * Session added at: 87
 * Session added at: 88
 * Session added at: 89
 * Session added at: 90
 * Session added at: 91
 * Session added at: 92
 * Session added at: 93
 * 
 * Screen Output:
 * 
 */

?>
