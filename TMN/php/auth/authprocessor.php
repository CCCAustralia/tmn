<?php

include_once("../classes/TmnCrudSession.php");
include_once("../classes/TmnCrudUser.php");

//Create the objects required for authorisation
try {
	$logfile			= "../logs/authprocessor.php.log";								//required for logging
	$tmn				= new Tmn($logfile);
	if ($tmn->isAuthenticated()) {
		
		//get rid of slashes if they have been added
		if(get_magic_quotes_gpc()) {
			$response	= stripslashes($_POST['response']);
			if (is_string($_POST['session'])) {
				$session_id	= stripslashes($_POST['session']);
			} else {
				$session_id	= $_POST['session'];
			}
		} else {
			$response = $_POST['response'];
			$session_id	= $_POST['session'];
		}
		
		$user				= new TmnCrudUser($logfile, $tmn->getAuthenticatedGuid());		//the user object
		$session			= new TmnCrudSession($logfile, (int)$session_id);			//the session object
		
		if ($response == "Yes" || $response == "No") {
			try {
				$session->authorise($user, $response);
				echo json_encode(array("success" => true));
			} catch (Exception $e) {
				throw new FatalException("Authorisation Failed: ".$e->getMessage());
			}
		} else {
			throw new FatalException("Authorisation Failed: No response provided.");
		}
	} else {
		throw new FatalException("Authentication Failed: Try Logging in.")
	}
} catch (Exception $e) {
	Reporter::newInstance($logfile)->exceptionHandler($e);
}
?>