<?php

interface TmnInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
			
	/**
	 * 
	 * This class is responsable for managing authentication and gathering the user's details.
	 * It also inherits from Reporter so have a look at ReporterInterface.php more methods
	 * that are available to this class.
	 * 
	 * Note: When it is created it will automatically check if the
	 * user has a valid CAS session, the script will fail if they aren't logged in.
	 * 		- To Authenticate a session ie force a login at the start of the script,
	 * 		use Tmn::authenticate() at the start of the script.
	 * 
	 * @param String $logfile - path of the file you want log statements to be ouputed to.
	 */
	public function __construct($logfile);
	
	
			////////////////AUTHENTICATION FUNCTIONS//////////////
	
	
	/**
	 * 
	 * Uses the GCX CAS service to authenticate this script, making the user login via the key
	 * so they have a valid CAS session.
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
	public function getAuthenticatedGuid();
	
	/**
	 * 
	 * Returns the email adress for the user with the authenticated session.
	 * 
	 * @return String - the user's email address
	 */
	public function getEmail();
	
	/**
	 * 
	 * Returns a TmnCrudUser containing all the info CCCA stores about the user with the
	 * authenticated session.
	 * 
	 * @return TmnCrudUser - user object with filled with user's data.
	 */
	public function getUser();
	
}

?>