<?php

include_once('../interfaces/TmnAuthorisationProcessorInterface.php');

include_once('../classes/TmnCrud.php');

class TmnAuthorisationProcessor extends TmnCrud implements TmnAuthorisationProcessorInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	/**
	 * 
	 * Flynn to Insert Description
	 * 
	 * @param String		$logfile - path of the file used to log any exceptions or interactions
	 * @param String		$tablename - not used
	 * @param String		$primarykey - not used
	 * @param Assoc Array	$privatetypes - not used
	 * @param Assoc Array	$publictypes - not used
	 * 
	 * @example $user = new TmnCrudUser("logfile.log");					will create an empty TmnAuthoristationProcessor
	 * @example $user = TmnCrudUser::make("logfile.log", "your_auth_session_id");	will create a TmnAuthoristationProcessor filled with the data associated with your_auth_session_id
	 * 
	 * Note: Method will throw FatalException if it can't complete construction.
	 */
	public function __construct($logfile, $tablename=null, $primarykey=null, $privatetypes=null, $publictypes=null) {
		
	}
	
	
	/**
	 * Will create an instance of TmnAuthoristationProcessor and will prefill it with the data associated
	 * with the id passed to it.
	 * 
	 * @param string $logfile			- Path of the file used to log any exceptions or interactions
	 * @param string $auth_session_id	- Unique ID for the authorisation session you want to load into this class
	 * 
	 * Note: will throw LightException if it can't complete this task.
	 */
	public function make($logfile, $auth_session_id) {
		
	}
	
	
	
			///////////////////ACTION FUNCTIONS/////////////////////
			
	/**
	 * 
	 * Enter description here ...
	 * @param int $level
	 * @param int $response
	 */
	public function authorise($level, $response) {
		
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param TmnCrudUser $user
	 */
	public function userIsAuthoriser(TmnCrudUser $user) {
		
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param TmnCrudUser $user
	 * @param TmnCrudUser $level1Authoriser
	 * @param TmnCrudUser $level2Authoriser
	 * @param TmnCrudUser $level3Authoriser
	 */
	public function submit(TmnCrudUser $user, TmnCrudUser $level1Authoriser, TmnCrudUser $level2Authoriser, TmnCrudUser $level3Authoriser) {
		
	}
	
}

?>