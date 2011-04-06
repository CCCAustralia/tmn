<?php
include_once 'classes/Tmn.php';
include_once 'classes/TmnCrudSession.php';
$logfile = 'logs/submit_tmn_for_authorisation.php.log';
$tmn = new Tmn($logfile);
$tmn->authenticate();
//Authenticate
if ($tmn->isAuthenticated()) {
	if (isset($_POST['authorisers']) && isset($_POST['data'])) {
		//decode authorisers
		$authorisers = json_decode($_POST['authorisers']);
		//create a TmnAuthorisationProcessor object authsessionid is null because it hasn't been submitted yet
		$session = new TmnCrudSession($logfile, $_POST['session']);
		//set up the auth users
		$authlevel1 = new TmnUser($logfile, $authorisers['level_1']['user_id']);
		$authlevel2 = new TmnUser($logfile, $authorisers['level_2']['user_id']);
		$authlevel3 = new TmnUser($logfile, $authorisers['level_3']['user_id']);
		
		$reasons1 	= $_POST['level_1'][reasons];
		$reasons2 	= $_POST['level_2'][reasons];
		$reasons3 	= $_POST['level_3'][reasons];
		
		//pass the auth data to submit()
		return json_encode(array('success' => $session->submit($tmn->getUser(), $authlevel1, $reasons1, $authlevel2, $reasons2, $authlevel3, $reasons3, $_POST['data'])));
		
		
	}
	
}











































?>