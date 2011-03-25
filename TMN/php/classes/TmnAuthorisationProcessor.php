<?php

include_once('../interfaces/TmnAuthorisationProcessorInterface.php');

include_once('../classes/TmnCrud.php');

class TmnAuthorisationProcessor extends TmnCrud implements TmnAuthorisationProcessorInterface {
	
			////Instance Variables////
	private $logfile;
	private $authsessionid;
	private $authcrud;
	
	
	
	
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
		$this->logfile = $logfile;
		if (!file_exists($this->logfile)) {
			$log = fopen($this->logfile, "c");
			fclose($log);
		}

		parent::__construct($this->logfile);

		
	}

	
	
	/**
	 * Will create an instance of TmnAuthorisationProcessor and will prefill it with the data associated
	 * with the id passed to it.
	 * 
	 * @param string $logfile			- Path of the file used to log any exceptions or interactions
	 * @param string $auth_session_id	- Unique ID for the authorisation session you want to load into this class
	 * 
	 * Note: will throw LightException if it can't complete this task.
	 */
	public function make($logfile, $auth_session_id) {
		$this->authsessionid = $auth_session_id;		//store the authsessionid
		$newObj = new TmnAuthorisationProcessor(		//construct a new object with crud 
			$this->logfile,
			"Auth_Table", 
			"AUTH_SESSION_ID", 
			array(
				'AUTH_USER' 			=> "s",
				'AUTH_LEVEL_1' 			=> "s",
				'AUTH_LEVEL_2'			=> "s",
				'AUTH_LEVEL_3'			=> "s"
			),
			array(
				'USER_RESPONSE'			=> "s",
				'LEVEL_1_RESPONSE'		=> "s",
				'LEVEL_2_RESPONSE'		=> "s",
				'LEVEL_3_RESPONSE'		=> "s",
				'FINANCE_RESPONSE'		=> "s"
			)
		);
		
		$this->authcrud = $newObj;
		$this->d($newObj);
		//TODO: complete make(), confirm output, once interface bug is fixed
		
	}
	
	
	
			///////////////////ACTION FUNCTIONS/////////////////////

	public function authorise(TmnCrudUser $user, $response) {
		//Check that make() has been run to set up the authorisationprocessor
		if ($this->authsessionid == null) {
			throw new FatalException("TmnAuthorisationProcessor not set up correctly, use make(logfile, authsessionid) first.", 0);
		}
		
		//validate the response string
		if ($response != "Yes" && $response != "No" && $response != "Pending") {
			throw new LightException("Invalid response string. Needs to be Yes, No or Pending");
			return null;
		}
		
		//fetch the user's authenticationlevel
		$authlevel = $this->userIsAuthoriser($user);
		$fieldname = "";	//initialise
		
		if ($authlevel) {
			//Form the identifying field name string for the level of authentication for which the user is valid
			if ($authlevel == 0) {
				$fieldname = "USER";
			} else {
				$fieldname = "LEVEL_".$authlevel;
			}
			
			//define which row to set the response
			$this->authcrud->loadDataFromAssocArray(array("AUTH_SESSION_ID" => $this->authsessionid));
			//set the response
			$this->authcrud->setField($fieldname."_RESPONSE", $response);
			
			//TODO: Workflow - email the appropriate recipiants
			
		} else {
			throw new LightException("Specified user is not an authoriser");
		}
		
	}
	

	public function userIsAuthoriser(TmnCrudUser $user) {
		//fetch the authorisers
		//check if user
		//check if authoriser lev 1-3
		//return the user's authorisation level (0 is user)
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