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
$LOGFILE = "../logs/combofill.log";

if (isset($_POST['mode'])) {
	
	try {
		
		$tmn = new Tmn($LOGFILE);
		
		if ($tmn->isAuthenticated()) {
		
			$tablename		= $_POST['mode'];
				
			if ($_POST['mode'] == 'Tmn_Sessions') {
				
				if (isset($_POST['aussie_form']) && isset($_POST['overseas_form']) && isset($_POST['home_assignment'])) {
					
					$aussie_form		= ($_POST['aussie_form'] == 'true' ? true : false);
					$overseas_form		= ($_POST['overseas_form'] == 'true' ? true : false);
					$home_assignment	= ($_POST['home_assignment'] == 'true' ? true : false);
					
					$comboLoader	= new TmnSessionComboLoader($LOGFILE, $tmn->getUser(), "Tmn_Sessions", $aussie_form, $overseas_form, $home_assignment);
					
				} else {
					fb('Invalid get_session params');
					die('{success: false}');
				}
				
			} else {
				$comboLoader	= new TmnComboLoader($LOGFILE, $tablename);
			}
		
			echo $comboLoader->produceJson();
		
		}
		
	} catch (Exception $e) {
		Reporter::newInstance($LOGFILE)->exceptionHandler($e);
	}
	
} else {
	fb('Invalid params');
	die('{success: false}');
}

?>