<?php
include_once 'classes/Tmn.php';
include_once 'classes/TmnAuthorisationProcessor.php';
include_once 'classes/TmnCrudSession.php';

$logfile = 'logs/submit_tmn_for_authorisation.php.log';
try {
	$tmn = new Tmn($logfile);
	//$tmn->authenticate();
	//Authenticate
	if ($tmn->isAuthenticated()) {
		if (isset($_POST['authorisers']) && isset($_POST['data'])) {
			if(get_magic_quotes_gpc()) {
				$authorisers_string = stripslashes($_POST['authorisers']);
				$data_string = stripslashes($_POST['data']);
	        } else {
	        	$authorisers_string = $_POST['authorisers'];
	        	$data_string = $_POST['data'];
			}
	               
			//decode authorisers
			$authorisers = json_decode($authorisers_string, true);
			
			fb($authorisers);
			//create a TmnAuthorisationProcessor object authsessionid is null because it hasn't been submitted yet
			$session = new TmnCrudSession($logfile, $_POST['session']);
			fb($session);
			//set up the auth users
			$authlevel1 = new TmnCrudUser($logfile, $authorisers['level_1']['user_id']);
			if ($authorisers['level_2']['user_id'] != 0) {
				$authlevel2 = new TmnCrudUser($logfile, $authorisers['level_2']['user_id']);
			}
			if ($authorisers['level_3']['user_id'] != 0) {
				$authlevel3 = new TmnCrudUser($logfile, $authorisers['level_3']['user_id']);
			}
			
				
				
			$reasons1 	= $_POST['level_1'][reasons];
			$reasons2 	= $_POST['level_2'][reasons];
			$reasons3 	= $_POST['level_3'][reasons];
			
			//pass the auth data to submit()
			echo json_encode($session->submit($tmn->getUser(), $authlevel1, $reasons1, $authlevel2, $reasons2, $authlevel3, $reasons3, json_decode($data_string, true)));
			
			
		}
		
	}
} catch (Exception $e) {
	Reporter::newInstance($logfile)->exceptionHandler($e);
}











































?>