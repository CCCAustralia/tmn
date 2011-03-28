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
	public function __construct($logfile, $auth_session_id) {
		$this->logfile = $logfile;

		parent::__construct(		//construct a new object with crud 
			$this->logfile,
			"Auth_Table", 
			"AUTH_SESSION_ID", 
			array(
				'AUTH_SESSION_ID'		=> "i",
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

		//set up the authsessionid
		$this->authsessionid			= $auth_session_id;
		
		//$this->authcrud = $newObj;
		//$this->d($this);
		
	
		//define which row
		$this->setField('AUTH_SESSION_ID', $this->authsessionid);
		$this->retrieve();
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
		
		if (!is_null($authlevel)) {
			//Form the identifying field name string for the level of authentication for which the user is valid
			if ($authlevel == 0) {
				$fieldname = "USER";
			} else {
				$fieldname = "LEVEL_".$authlevel;
			}
			
			//set the response
			$this->setField($fieldname."_RESPONSE", $response);
			fb($this);
			$this->update();
			
			//TODO: Workflow - email the appropriate recipiants
			
		} else {
			throw new LightException("Specified user is not an authoriser");
		}
		
	}
	

	public function userIsAuthoriser(TmnCrudUser $user) {
		$returndata = null;
		
		$authorisers = array(
			0	=> $this->getField("AUTH_USER"),
			1	=> $this->getField("AUTH_LEVEL_1"),
			2	=> $this->getField("AUTH_LEVEL_2"),
			3	=> $this->getField("AUTH_LEVEL_3")
		);
		for ($i = 0; $i <= 3; $i++) {
			if ($user->getGuid() == $authorisers[$i]) {
				$returndata = $i;
			}
		}
		
		fb("userIsAuthoriser returning value:".$returndata);
		
		//check if user
		return $returndata;
		
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