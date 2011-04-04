<?php
if (file_exists('../classes/TmnCrud.php')) {
	include_once('../interfaces/TmnAuthorisationProcessorInterface.php');
	include_once('../classes/email.php');
	include_once('../classes/TmnCrud.php');
} else {
	include_once('interfaces/TmnAuthorisationProcessorInterface.php');
	include_once('classes/email.php');
	include_once('classes/TmnCrud.php');
}

class TmnAuthorisationProcessor extends TmnCrud implements TmnAuthorisationProcessorInterface {
	
			////Instance Variables////
	private $logfile;
	private $authsessionid;
	//private $authcrud;
	
	
	
	
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
				'AUTH_LEVEL_3'			=> "s",
				'AUTH_LEVEL_1_REASONS'	=> "s",
				'AUTH_LEVEL_2_REASONS' 	=> "s",
				'AUTH_LEVEL_3_REASONS'	=> "s"
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
		if ($this->authsessionid) {
			$this->setField('AUTH_SESSION_ID', $this->authsessionid);
			$this->retrieve();
		} else {
			$this->authsessionid = $this->create();
			$this->setField('AUTH_SESSION_ID', $this->authsessionid);
			$this->retrieve();
		}
	}

	
	public function make($auth_user, $auth_level_1 = null, $auth_level_1_reasons = null, $auth_level_2 = null, $auth_level_2_reasons = null, $auth_level_3 = null, $auth_level_3_reasons = null) {
									$this->setField("AUTH_USER", 			$auth_user);
		if ($auth_level_1){ 		$this->setField("AUTH_LEVEL_1", 		$auth_level_1); 		}
		if ($auth_level_1_reasons){	$this->setField("AUTH_LEVEL_1_REASONS",	$auth_level_1_reasons);	}
		if ($auth_level_2){			$this->setField("AUTH_LEVEL_2",			$auth_level_2); 		}
		if ($auth_level_2_reasons){	$this->setField("AUTH_LEVEL_2_REASONS", $auth_level_2_reasons);	}
		if ($auth_level_3){			$this->setField("AUTH_LEVEL_3",			$auth_level_3);			}
		if ($auth_level_3_reasons){	$this->setField("AUTH_LEVEL_3_REASONS", $auth_level_3_reasons);	}
		
		$this->update();
		
		
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
		$userauthlevel = $this->userIsAuthoriser($user);
		$fieldname = "";	//initialise
		
		if (!is_null($userauthlevel)) {
			//Form the identifying field name string for the level of authentication for which the user is valid
			if ($userauthlevel == 0) {
				$fieldname = "USER";
			} else {
				$fieldname = "LEVEL_".$userauthlevel;
			}
			
			//set the response
			$this->setField($fieldname."_RESPONSE", $response);
			fb("RESPONSE FOR LVL ".$userauthlevel." UPDATED");
			fb($this);
			$this->update();
			//TODO: update the timestamp
			
			//TODO: Workflow - email the appropriate recipiants
			//$authlevel = current user's authlevel
			$nextauthlevel = $this->getNextAuthLevel($userauthlevel);	//calculate the next authlevel
		//$nextauthlevel = 3; //FOR DEBUGGING
			$this->notify($nextauthlevel);							//notify the calculated level
			
		} else {
			throw new LightException("Specified user is not an authoriser");
		}
		
	}
	
	private function notify($notifylevel) {
		$emailbody = "";
		
		//get responses <1-3>, storing them in authguids and authresponses
		$authguids 		= array();		//guids array
		$authresponses 	= array();		//responses array
		
		//GUIDS
		$authguids[0] = $this->getField("AUTH_USER");		//store the user's guid
		for ($i = 1; $i <= 3; $i++) {						//loop through each authlevel<1-3>
			$fieldname = "AUTH_LEVEL_".(string)$i;				//get the authuser's guid
			if ($this->getField($fieldname) != "") {			//if the guid exists
				$authguids[$i] = $this->getField($fieldname);		//store it
			}
		}
		
		//RESPONSES
		$authresponses[0] = $this->getField("USER_RESPONSE");	//store the user's response
		for ($i = 1; $i <= 3; $i++) {							//loop through each authlevel<1-3>
			if (isset($authguids[$i])) {						//if the guid exists
				$authresponses[$i] = $this->getField("LEVEL_".((string)$i)."_RESPONSE");	//store the response
			}
		}
		
		//DEBUG OUTPUT
		fb("authguids: "); fb($authguids);
		fb("authresponses:"); fb($authresponses);
		
		
		
		//notify auth<1-3>
		if (1 <= $notifylevel && $notifylevel <= 3) {
			//get previous responses, forward names and responses to auth<1-3>
			$emailbody = "Hi ".$this->getNameFromGuid($authguids[$notifylevel])."\nYou are required to authorise a TMN for : ".$this->getNameFromGuid($authguids[0])."\n\n";
			$emailbody .= "It has already been authorised by the following people:\n";
			foreach ($authresponses as $k => $v) {
				if ($v == "Yes" && $k != 0) {
					$emailbody .= $this->getNameFromGuid($authguids[$k])."\n";
				}
			}
			$emailbody .= "\nPlease go to the following link to review and confirm or reject this TMN. Thankyou.";
		}
			
		//notify finance:
			//get all previous responses, forward names and responses to finance
		
		//notify user:
		if ($notifylevel == 5) {
			//get all responses, forward names and responses to user
			
			$emailbody = "Your TMN had been processed!\n\n";
			
			foreach ($authguids as $k => $v) {
				fb("Level ".$k." user's name: ".$this->getNameFromGuid($v));
				$emailbody .= $this->getNameFromGuid($v)."'s response is: ".$authresponses[$k]."\n";
			}
		}
		
		$addr = "tom.flynn@ccca.org.au";//$nextauthuser->getEmail();
		$notifyemail = new Email($addr, "SUBJECT", $emailbody, "Tom Flynn <tom.moose@gmail.com>\r\nReply-To: noreply@ccca.org.au");
		fb($notifyemail);
		$notifyemail->send();
		
	}
	
	/**
	 * 
	 * getNextAuthLevel 				-Processes and returns the next level of authorisation in the queue
	 * @param unknown_type $authlevel 	-The level at which the user is currently authorising.
	 * @return integer					-The level of the next authoriser in the queue (4 is finance, 5 is user)
	 */
	private function getNextAuthLevel($authlevel) {
		do {
			$authlevel = $authlevel + 1;
			
			switch ($authlevel) {
				case 5:		//checklevel is 5: all authlevels and finance have passed and the owner should be notified
					//$nextauthlevel = 0; 											//break iteration loop, continue to notify user
					return 5;
					break;
				case 4:		//checklevel is 4: all authlevels have passed and finance should be next
					if ($this->getField("FINANCE_RESPONSE") == "Pending") {			//if finance has not confirmed/denied
						//$nextauthlevel = 4;												//break iteration loop, continue to notify finance
						return 4;
					} 																//else loop and iterate
					break;
				default:	//minimum authlevel is 1, so for checklevel = 1-3, determine if the authlevel's guid is set, if so, check if they have responded (!= Pending)
					if ($this->getField("AUTH_LEVEL_".(string)$checklevel) != "") {	//if auth_level_<1-3> is required
						$fieldname = "LEVEL_".(string)$checklevel."_RESPONSE";			//get their response
						if ($this->getField($fieldname) == "Pending") {					//if they have not confirmed/denied
							//$nextauthlevel = $checklevel;									//break iteration loop, continue to notify auth_level_<1-3>
							return $checklevel;
						} 																//else loop and iterate
					} 																//else loop and iterate
					break;	
			}
		} while ($nextauthlevel == null);	//loop until a nextauthlevel has been confirmed.
}
	
	private function getNameFromGuid($guid) {
		$this->d("Attempting name from ".$guid);
		$user = new TmnCrudUser($this->logfile, $guid);
		$user->retrieve();
		return $user->getField("firstname")." ".$user->getField("surname");
	}
	

	public function userIsAuthoriser(TmnCrudUser $user) {
		$returndata = null;
		
		$authorisers = array(
			0	=> $this->getField("AUTH_USER"),
			1	=> $this->getField("AUTH_LEVEL_1"),
			2	=> $this->getField("AUTH_LEVEL_2"),
			3	=> $this->getField("AUTH_LEVEL_3")
		);
		fb("TmnAuthorisationProcessor.php<userIsAuthoriser() - authorisers:"); fb($authorisers);
		
		for ($i = 0; $i <= 3; $i++) {
			if ($user->getGuid() == $authorisers[$i] && $authorisers[$i] != "") {
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