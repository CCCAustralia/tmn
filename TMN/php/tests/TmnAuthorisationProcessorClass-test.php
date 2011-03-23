<?php


/*******************************************                                                        
# Test Code                 
*******************************************/

include_once('../classes/Tmn.php');
include_once('../classes/TmnAuthorisationProcessor.php');
$LOGFILE	= "../logs/TmnAuthorisationProcessorClass-test.log";
$DEBUG		= 1;

	//Constructor test
	
fb("Constructor Test");
try {
	$tmn		= new Tmn($LOGFILE);
	$session	= new TmnAuthorisationProcessor($LOGFILE);
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
//getOwner
//setOwner
//getOwnerGuid
//setOwnerGuid

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
