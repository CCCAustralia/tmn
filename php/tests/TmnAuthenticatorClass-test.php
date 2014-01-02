<?php


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
fb("logout() is commented out");
//$tmnObj->logout();

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
