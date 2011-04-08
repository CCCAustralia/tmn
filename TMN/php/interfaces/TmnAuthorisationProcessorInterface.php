<?php

interface TmnAuthorisationProcessorInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	/**
	 * 
	 * Flynn to Insert Description
	 * 
	 * @param String		$logfile					- path of the file used to log any exceptions or interactions
	 * @param String		$auth_session_id			- id of authorisation session to be loaded into object. If $auth_session_id is null, a new, blank row will be created and make() must be used to fill it.
	 * 
	 * @example $user = new TmnAuthorisationProcessorInterface("logfile.log");							will create an empty TmnAuthoristationProcessor
	 * @example $user = new TmnAuthorisationProcessorInterface("logfile.log", "your_auth_session_id");	will create a TmnAuthoristationProcessor filled with the data associated with your_auth_session_id
	 * 
	 * Note: Method will throw FatalException if it can't complete construction.
	 */
	public function __construct($logfile, $auth_session_id = null);
	
	
	
			///////////////////ACCESSOR FUNCTIONS/////////////////////
			
	
	/**
	 * Give it the auth level you want and it will return an array of the auth reasons for that level
	 * @param	int		$auth_level	- authorisation level you want reasons for
	 * @return	array				- array of reasons
	 */
	public function getReasonsArray($auth_level);
	
	
			///////////////////ACTION FUNCTIONS/////////////////////
			
	/**
	 * 
	 * Applies the specified authorisation response to the appropriate session in the database, then takes action based on the next level of authorisation required.
	 * 
	 * @param TmnCrudUser $user
	 * @param string $response 		- Possible values are "Yes", "No", "Pending"
	 */
	public function authorise(TmnCrudUser $user, $response);
	
	/**
	 * Determines the next authoriser, and emails them.
	 * 
	 * @param unknown_type $authlevel	- Auth Level of the current user.
	 */
	//private function notifyNext($authlevel);
	
	
	/**
	 * Determines if the specified user is a valid authoriser for the authsession defined at construction.
	 * 
	 * @param TmnCrudUser $user
	 * 
	 * @return Integer 		- The specifed user's authorisation level with possible values:
	 * 						NULL	= not an authoriser
	 * 						0		= user (submit)
	 * 						1-3		= authoriser lvl. 1-3
	 */
	public function userIsAuthoriser(TmnCrudUser $user);
	
	/**
	 * Will fill the row in Auth_Table with the specified auth data and begin the authorisation process.
	 * 
	 * @param $auth_user
	 * @param $auth_level_1
	 * @param $auth_level_1_reasons
	 * @param $auth_level_2
	 * @param $auth_level_2_reasons
	 * @param $auth_level_3
	 * @param $auth_level_3_reasons
	 * 
	 */
	public function submit( TmnCrudUser $auth_user, TmnCrudUser $auth_level_1, $auth_level_1_reasons = null, TmnCrudUser $auth_level_2 = null, $auth_level_2_reasons = null, TmnCrudUser $auth_level_3 = null, $auth_level_3_reasons = null);
	
	/**
	 * Fetches the current authorisation progress of the session
	 * 
	 * @return an assoc array containing the current response (Yes, No, Pending) and a name (who is responsible for that response)
	 */
	public function getOverallProgress();
}

?>