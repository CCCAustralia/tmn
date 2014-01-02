<?php


/*******************************************                                                        
# Test Code                 
*******************************************/

include_once('../classes/Tmn.php');
include_once('../classes/TmnAuthorisationProcessor.php');
include_once('../classes/TmnCrudSession.php');
$LOGFILE	= "../logs/TmnAuthorisationProcessorClass-test.log";
$DEBUG		= 1;

	//Constructor test
	
fb("Constructor Test");
try {
	$tmn		= new Tmn($LOGFILE);
	$user 		= new TmnCrudUser($LOGFILE, "test");
	$session	= new TmnCrudSession($LOGFILE, 1234);
	//$authproc	= new TmnAuthorisationProcessor($LOGFILE, );
	
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
/*
fb("Session Test");

fb("getOwner()");
fb($session->getOwner());
fb("setOwner(tmn->getUser())");
$session->setOwner($tmn->getUser());
fb("getOwner()");
fb($session->getOwner());
fb("setOwner()");
$session->setOwner();
fb("getOwner()");
fb($session->getOwner());
fb("setOwnerGuid('test')");
$session->setOwnerGuid('test');
fb("getOwner()");
fb($session->getOwner());
//fb("setOwnerGuid(tmn->getAuthenticatedGuid())");
//$session->setOwnerGuid($tmn->getAuthenticatedGuid());
fb("getOwnerGuid()");
fb($session->getOwnerGuid());
*/

fb("setOwnerGuid('test')");
$session->setOwnerGuid('test');

	//Access test

fb("Access Test");
//userIsAuthoriser
try {
	//fb($user);
	//fb($session);
	$session->authorise($session->getOwner(), "Yes");

} catch (Exception $e) {
	Reporter::newInstance($LOGFILE)->exceptionHandler($e);
}

/*
 * Expected output
 * 
 * Console Output:
 * Access Test
 * 
 * 
 * Screen Output:
 * 
 */

	//Database Interaction test

fb("Database Interaction Test");



/*
 * Expected output
 * 
 * Console Output:
 * Database Interaction Test
 * 
 * 
 * Screen Output:
 * 
 */

?>
