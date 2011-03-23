<?php



/*******************************************                                                        
# Test Code                 
*******************************************/

include_once('../classes/Tmn.php');
$LOGFILE	= "../logs/TmnClass-test.log";
$DEBUG		= 1;

	//Constructor test
	
fb("Constructor Test");
$tmnObj	= new Tmn($LOGFILE);

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

	//Auth test

fb("Auth Test");
fb("isAuthenticated(): " . $tmnObj->isAuthenticated());
fb("getAuthenticatedGuid(): " . $tmnObj->getAuthenticatedGuid());
fb("getEmail(): " . $tmnObj->getEmail());
fb("getUser(): ");
fb($tmnObj->getUser());
fb("logout() is commented out");
//$tmnObj->logout();

/*
 * Expected output
 * 
 * Console Output:
 * Auth Test
 * isAuthenticated(): 1
 * getAuthGuid(): 691EC152-0565-CEF4-B5D8-99286252652B
 * getGuid(): 691EC152-0565-CEF4-B5D8-99286252652B
 * getEmail(): michael.harro@gmail.com
 * setGuid('me')
 * getGuid(): 691EC152-0565-CEF4-B5D8-99286252652B
 * setGuid('me')
 * 
 * Screen Output:
 * 
 */

?>
