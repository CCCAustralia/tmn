<?php

interface TmnAuthorisationProcessorInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	/**
	 * 
	 * Flynn to Insert Description
	 * 
	 * @param String		$logfile	- path of the file used to log any exceptions or interactions
	 * @param String		$id			- id of authorisation session to be loaded into object
	 * 
	 * @example $user = new TmnAuthorisationProcessorInterface("logfile.log");							will create an empty TmnAuthoristationProcessor
	 * @example $user = new TmnAuthorisationProcessorInterface("logfile.log", "your_auth_session_id");	will create a TmnAuthoristationProcessor filled with the data associated with your_auth_session_id
	 * 
	 * Note: Method will throw FatalException if it can't complete construction.
	 */
	public function __construct($logfile, $id);
	
	
	/**
	 * Will create an instance of TmnAuthoristationProcessor and will prefill it with the data associated
	 * with the id passed to it.
	 * 
	 * @param string $logfile			- Path of the file used to log any exceptions or interactions
	 * @param string $auth_session_id	- Unique ID for the authorisation session you want to load into this class
	 * 
	 * @return TmnAuthorisationProcessor
	 * 
	 * Note: will throw LightException if it can't complete this task.
	 */
	public function make($logfile, $auth_session_id);
	
	
	
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
	 * 
	 * Enter description here ...
	 * @param TmnCrudUser $user
	 * @param TmnCrudUser $level1Authoriser
	 * @param TmnCrudUser $level2Authoriser
	 * @param TmnCrudUser $level3Authoriser
	 */
	public function submit(TmnCrudUser $user, TmnCrudUser $level1Authoriser, TmnCrudUser $level2Authoriser, TmnCrudUser $level3Authoriser);
	
}

?>