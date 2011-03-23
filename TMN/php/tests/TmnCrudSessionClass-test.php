<?php


/*******************************************                                                        
# Test Code                 
*******************************************/

include_once('../classes/TmnCrudSession.php');
$LOGFILE	= "../logs/TmnSessionClass-test.log";
$DEBUG		= 1;

	//Constructor test
	
fb("Constructor Test");
try {
	$tmn		= new Tmn($LOGFILE);
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
