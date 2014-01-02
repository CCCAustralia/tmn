<?php
/**
 * Combofill.php - takes 'mode': a POST variable
 * @param mode: the database table you wish to retrieve
 * 
 * returns a json packet with the field names and values.
 */

include_once('../classes/Tmn.php');
include_once('../classes/TmnSessionComboLoader.php');


//set the log path
$LOGFILE = "../logs/TmnSessionComboLoaderClass-test.log";

try {
	
	$tmn = new Tmn($LOGFILE);
	
	if ($tmn->isAuthenticated()) {
	
		$tablename		= "Ministry";
		$aussie_form		= true;
		$overseas_form		= false;
		$home_assignment	= false;
		
		$comboLoader	= new TmnSessionComboLoader($LOGFILE, $tmn->getUser(), "Tmn_Sessions", $aussie_form, $overseas_form, $home_assignment);
	
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
 * {Tmn_Sessions:[{"SESSION_ID": "75","SESSION_NAME": "2011-03-11 03:02:41"}]}
 * 
 */

?>