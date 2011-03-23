<?php

interface TmnAuthenticatorInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	/**
	 * 
	 * This class is responsable for managing the authentication of scripts. It interacts with
	 * the GCX CAS service on your behalf.
	 * It also inherits from Reporter so have a look at ReporterInterface.php more methods
	 * that are available to this class.
	 * 
	 * Note: - When it is created it will automatically check if the
	 * user has a valid CAS session, the script will fail if they aren't logged in.
	 * 		 - This class is a singleton, please use getInstance($logfile) to create it.
	 * 
	 * @param String $logfile - path of the file you want log statements to be ouputed to.
	 * 
	 * Note: Will throw FatalException if it can't complete this task.
	 */
	public static function getInstance($logfile);
	
	
			////////////////AUTHENTICATION FUNCTIONS//////////////
	
	
	/**
	 * 
	 * Uses the GCX CAS service to authenticate the script that calls it, making the user login via the key
	 * if they don't have a valid CAS session.
	 */
	public function authenticate();
	
	/**
	 * 
	 * Logs the user out of their GCX CAS session.
	 */
	public function logout();
	
	/**
	 * 
	 * Tells you if the call to this script has been made from someone with an authenticated
	 * GCX session.
	 */
	public function isAuthenticated();
	
	/**
	 * 
	 * Returns the Global User ID (guid) for the authenticated session.
	 * 
	 * @return String - the user's guid
	 */
	public function getGuid();
	
	/**
	 * 
	 * Returns the email adress for the user with the authenticated session.
	 * 
	 * @return String - the user's email address
	 */
	public function getEmail();
	
}

?>