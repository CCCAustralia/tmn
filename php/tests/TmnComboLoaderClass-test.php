<?php
/**
 * Combofill.php - takes 'mode': a POST variable
 * @param mode: the database table you wish to retrieve
 * 
 * returns a json packet with the field names and values.
 */

include_once('../classes/Tmn.php');
include_once('../classes/TmnComboLoader.php');


//set the log path
$LOGFILE = "../logs/TmnComboLoaderClass-test.log";

try {
	
	$tmn = new Tmn($LOGFILE);
	
	if ($tmn->isAuthenticated()) {
	
		$tablename		= "Ministry";
		$comboLoader	= new TmnComboLoader($LOGFILE, $tablename);
	
		echo $comboLoader->produceJson();
	
	}
	
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
 * {Ministry:[{"MINISTRY_ID": "Athletes in Action","MINISTRY_LEVY": "0"},{"MINISTRY_ID": "Children of the World","MINISTRY_LEVY": "0"},{"MINISTRY_ID": "CRAM","MINISTRY_LEVY": "20"},{"MINISTRY_ID": "FamilyLife","MINISTRY_LEVY": "0"},{"MINISTRY_ID": "GAiN","MINISTRY_LEVY": "0"},{"MINISTRY_ID": "Here's Life","MINISTRY_LEVY": "0"},{"MINISTRY_ID": "HQ / Core Services","MINISTRY_LEVY": "0"},{"MINISTRY_ID": "International Ministry","MINISTRY_LEVY": "0"},{"MINISTRY_ID": "Jesus Gift to the Nation","MINISTRY_LEVY": "0"},{"MINISTRY_ID": "StudentLife","MINISTRY_LEVY": "2"},{"MINISTRY_ID": "The Significant Woman","MINISTRY_LEVY": "0"},{"MINISTRY_ID": "Youth Ministries Australia","MINISTRY_LEVY": "0"}]}
 * 
 */

?>