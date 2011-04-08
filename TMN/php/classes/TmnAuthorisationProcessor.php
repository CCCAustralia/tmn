<?php
if (file_exists('../classes/TmnCrud.php')) {
	include_once('../interfaces/TmnAuthorisationProcessorInterface.php');
	include_once('../classes/email.php');
	include_once('../classes/TmnCrud.php');
	include_once('../classes/TmnAuthenticator.php');
} else {
	include_once('interfaces/TmnAuthorisationProcessorInterface.php');
	include_once('classes/email.php');
	include_once('classes/TmnCrud.php');
	include_once('classes/TmnAuthenticator.php');
}

class TmnAuthorisationProcessor extends TmnCrud implements TmnAuthorisationProcessorInterface {
	
			////Instance Variables////
	//private $logfile;
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
	public function __construct($logfile, $auth_session_id = null) {
		//$this->logfile = $logfile;

		parent::__construct(		//construct a new object with crud 
			$logfile,
			"Auth_Table", 
			"auth_session_id", 
			array(
				'auth_session_id'		=> "i",
				'auth_user' 			=> "s",
				'auth_level_1' 			=> "s",
				'auth_level_2'			=> "s",
				'auth_level_3'			=> "s",
				'auth_level_1_reasons'	=> "s",
				'auth_level_2_reasons' 	=> "s",
				'auth_level_3_reasons'	=> "s"
			),
			array(
				'user_response'			=> "s",
				'level_1_response'		=> "s",
				'level_2_response'		=> "s",
				'level_3_response'		=> "s",
				'finance_response'		=> "s"
			)
		);

		//set up the authsessionid
		$this->authsessionid			= $auth_session_id;
		
		//$this->authcrud = $newObj;
		//$this->d($this);
		
	
		//define which row
		if ($this->authsessionid) {
			$this->setField('auth_session_id', $this->authsessionid);
			$this->retrieve();
		}
	}
	
	
			///////////////////ACCESSOR FUNCTIONS/////////////////////
			
	
	public function getReasonsArray($auth_level) {
		
		//if its a valid level, grab reasons string for that level
		if ($auth_level == 1 || $auth_level == 2 || $auth_level == 3) {
			$reason	= $this->getField("auth_level_" . $auth_level . "_reasons");
		} else {
			$reason	= null;
		}
		
		//if a string was returned decode it and return it
		if ($reason != null) {
			return json_decode($reason, true);
		} else {
			return array();
		}
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
		
		//fetch the user's authlevel
		$userauthlevel = $this->userIsAuthoriser($user);
		$fieldname = "";	//initialise
		
		if (!is_null($userauthlevel)) {
			//Form the identifying field name string for the level of authentication for which the user is valid
			if ($userauthlevel == 0) {
				$fieldname = "USER";
			} else {
				$fieldname = "level_".$userauthlevel;
			}
			
			//set the response
			$this->setField($fieldname."_response", $response);
			fb("RESPONSE FOR LVL ".$userauthlevel." UPDATED TO ".$response);
			//fb($this);
			$this->update();
			//TODO: update the timestamp
			
			//TODO: Workflow - email the appropriate recipiants
			if ($response == "Yes") {
				//$authlevel = current user's authlevel
				$nextauthlevel = $this->getNextAuthLevel($userauthlevel);	//calculate the next authlevel
				//$nextauthlevel = 3; //FOR DEBUGGING
				$this->notifyOfSubmission($nextauthlevel);							//notify the calculated level
			}
			if ($response == "No") {
				$this->notifyUserOfRejection($userauthlevel);
			}
			
		} else {
			throw new LightException("Specified user is not an authoriser");
		}
		
	}
	
	private function notifyOfSubmission($notifylevel) {
		fb("TmnAuthorisationProcessor - notifying level: $notifylevel of approval");
		$emailbody = "";
		$emailaddress = "";
		$emailsubject = "";
		
	////get guids<0-4> and responses <1-3>, storing them in authguids and authresponses
		$authguids 		= array();		//guids array
		$authresponses 	= array();		//responses array
		
		//GUIDS
		$authguids[0] = $this->getField("auth_user");		//store the user's guid
		for ($i = 1; $i <= 3; $i++) {						//loop through each authlevel<1-3>
			$fieldname = "auth_level_".(string)$i;				//get the authuser's guid
			if ($this->getField($fieldname) != "") {			//if the guid exists
				$authguids[$i] = $this->getField($fieldname);		//store it
			}
		}
		
		//RESPONSES
		$authresponses[0] = $this->getField("user_response");	//store the user's response
		for ($i = 1; $i <= 3; $i++) {							//loop through each authlevel<1-3>
			if (isset($authguids[$i])) {						//if the guid exists
				$authresponses[$i] = $this->getField("level_".((string)$i)."_response");	//store the response
			}
		}
		$authresponses[4] = $this->getField("finance_response");//store finance's response
		
	////calculate tmn-authviewer address
			$curpageurl = TmnAuthenticator::curPageURL();
			$curpageurl = split("/", $curpageurl);
			unset($curpageurl[count($curpageurl) -1]);	//take off page name
			unset($curpageurl[count($curpageurl) -1]);	//take off php/
			$curpageurl = join("/",	$curpageurl);
			$authviewerurl = $curpageurl."/tmn-authviewer.php?".$this->authsessionid;
		
		//DEBUG OUTPUT
		fb("authguids: "); fb($authguids);
		fb("authresponses:"); fb($authresponses);
		
		//notify user
		if ($notifylevel == 0) {
			//prepare the email
			$emailsubject = "TMN: Submitted";
			$emailaddress = $this->getEmailFromGuid($authguids[0]);	//get the user's email
			
			$emailbody = "Your TMN has been submitted for approval!\n";
			$emailbody .= "Your Ministry Overseer: ".$this->getNameFromGuid($authguids[1])." has been emailed, requesting authorisation for your TMN.\n";
			$emailbody .= "\nYou can view or cancel your submission at the following link:\n";
			$emailbody .= $authviewerurl;
			$emailbody .= "\n\n-The TMN Development Team";
			
		}
		
		//notify auth<1-3>
		if (1 <= $notifylevel && $notifylevel <= 3) {
			$emailsubject = "TMN: Awaiting your approval";
			$emailaddress = $this->getEmailFromGuid($authguids[$notifylevel]);	//get the approver's email
			
			//get previous responses, forward names and responses to auth<1-3>
			$emailbody = "Hi ".$this->getFirstNameFromGuid($authguids[$notifylevel])."\n";
			$emailbody .= "\nYou are required to authorise a TMN for : ".$this->getNameFromGuid($authguids[0])."\n";
			if ($notifylevel != 1) {
				$emailbody .= "\nTheir TMN submission has already been authorised by the following people:\n";
			
				foreach ($authresponses as $k => $v) {
					if ($v == "Yes" && $k != 0) {
						$emailbody .= $this->getNameFromGuid($authguids[$k])."\n";
					}
				}
			}
			$emailbody .= "\nPlease go to the following link to review and confirm or reject this TMN. Thankyou.\n";
			$emailbody .= $authviewerurl;
			$emailbody .= "\n\n-The TMN Development Team";
		}
			
		//notify finance:
		if ($notifylevel == 4){
			//get all previous responses, forward names and responses to finance
			$emailsubject = "TMN: Ready for processing";
			$emailaddress = "payroll@ccca.org.au";
			
			//get previous responses, forward names and responses to auth<1-3>
			$emailbody = "Hi ".$emailaddress."!\n";
			$emailbody .= "\nThe TMN for : ".$this->getNameFromGuid($authguids[0])." has been approved and is ready for processing.\n";
			if ($notifylevel != 1) {
				$emailbody .= "\nTheir TMN submission has already been authorised by the following people:\n";
			
				foreach ($authresponses as $k => $v) {
					if ($v == "Yes" && $k != 0) {
						$emailbody .= $this->getNameFromGuid($authguids[$k])."\n";
					}
				}
			}
			$emailbody .= "\nPlease go to the following link to review and confirm or reject this TMN. Thankyou.\n";
			$emailbody .= $authviewerurl;
			$emailbody .= "\n\n-The TMN Development Team";
			
			$this->notifyOfSubmission(5);	//notify user of processing
		}
		
		//notify user:
		if ($notifylevel == 5) {
			fb("Notify user: finance response=".$authresponses[4]);
			//set address to user
			$emailaddress = $this->getEmailFromGuid($authguids[0]);
			
		////notify of processing
			if ($authresponses[4] == "Pending") {
				$emailsubject = "TMN: Processing";
				$emailbody = "Your TMN has been approved and sent to finance for processing.\n";

		////notify of completion	
			} elseif ($authresponses[4] = "Yes") {
				$emailsubject = "TMN: Processed";
				$emailbody = "Your TMN has been processed!\n";
			}
				
			//Output approvals
			foreach ($authguids as $k => $v) {
				fb("Level ".$k." user's name: ".$this->getNameFromGuid($v));
				//$emailbody .= "\n".$k.": ".$this->getNameFromGuid($v)." responsed: ".$authresponses[$k]."\n";
			}
			$emailbody .= "\n\n-The TMN Development Team";
		}
		//TODO: REMOVE AFTER DEBUGGING
		$emailbody .="\n\nDEBUG: target email=".$emailaddress;
		$emailaddress = "tom.flynn@ccca.org.au";
		$notifyemail = new Email($emailaddress, $emailsubject, $emailbody, "CCCA TMN <noreply@ccca.org.au>\r\nReply-To: noreply@ccca.org.au");
		fb($notifyemail);
		$notifyemail->send();
		
		return $emailaddress;
	}
	
	private function notifyUserOfRejection($rejectedbylevel) {
		fb("TmnAuthorisationProcessor - notifying user of rejection by level: $rejectedbylevel");
		$emailbody = "";
		$emailaddress = "";
		$emailsubject = "";
		
	////get guids<0-4> and responses <1-3>, storing them in authguids and authresponses
		$authguids 		= array();		//guids array
		$authresponses 	= array();		//responses array
		
		//GUIDS
		$authguids[0] = $this->getField("auth_user");		//store the user's guid
		for ($i = 1; $i <= 3; $i++) {						//loop through each authlevel<1-3>
			$fieldname = "auth_level_".(string)$i;				//get the authuser's guid
			if ($this->getField($fieldname) != "") {			//if the guid exists
				$authguids[$i] = $this->getField($fieldname);		//store it
			}
		}
		
		//RESPONSES
		$authresponses[0] = $this->getField("user_response");	//store the user's response
		for ($i = 1; $i <= 3; $i++) {							//loop through each authlevel<1-3>
			if (isset($authguids[$i])) {						//if the guid exists
				$authresponses[$i] = $this->getField("level_".((string)$i)."_response");	//store the response
			}
		}
		
		//DEBUG OUTPUT
		fb("authguids: "); fb($authguids);
		fb("authresponses:"); fb($authresponses);
		
		
		
		if ($rejectedbylevel == 0) {			
	////REJECTED BY USER
			//prepare the email
			$emailsubject 	= "TMN: Cancellation";
			$emailaddress 	= $this->getEmailFromGuid($authguids[0]);	//get the user's email
			$emailbody 		= "You have cancelled your TMN submission!\n";
		} elseif ($rejectedbylevel == 4) {		
	////REJECTED BY FINANCE
			$emailsubject 	= "TMN: Rejection";
			$emailbody		= "Your TMN submission was rejected by Finance.\n";
			$emailbody		= "To contact them about this, email payroll@ccca.org.au\n";
		} else {								
	////REJECTED BY AUTHLEVEL<1-3>
			$emailsubject 	= "TMN: Rejection";
			$emailbody		= "Your TMN submission was rejected by ".$this->getNameFromGuid($authguids[$rejectedbylevel]).".\n";
			$emailbody		.= "To contact them about this, email ".$this->getEmailFromGuid($authguids[$rejectedbylevel]).".\n";
		}

	////list responses
		$emailbody .= "\nYour submission had the following responses before cancellation:\n";
		foreach ( $authresponses as $level =>$response) {
			if ($level != 0) {
				$emailbody .= $this->getNameFromGuid($authguids[$level])." -- ".$response."\n";
			}
		}			
		
		
		$authviewerurl = TmnAuthenticator::curPageURL();
		$authviewerurl = split("/", $authviewerurl);
		unset($authviewerurl[count($authviewerurl) -1]);	//take off page name
		unset($authviewerurl[count($authviewerurl) -1]);
		unset($authviewerurl[count($authviewerurl) -1]);	//take off php/
		$authviewerurl = join("/",	$authviewerurl);
		$emailbody .= "\nClick the following link to start a new TMN session:\n";
		$emailbody .= $authviewerurl;
		$emailbody .= "\n\n-The TMN Development Team";	
		
		
		fb($emailbody);

		//TODO: REMOVE AFTER DEBUGGING
		$emailbody .="\n\nDEBUG: target email=".$emailaddress;
		$emailaddress = "tom.flynn@ccca.org.au";
		$rejectionemail = new Email($emailaddress, $emailsubject, $emailbody, "CCCA TMN <noreply@ccca.org.au>\r\nReply-To: noreply@ccca.org.au");
		fb($rejectionemail);
		$rejectionemail->send();
		
		return $emailaddress;
	}
	
	/**
	 * 
	 * getNextAuthLevel 				-Processes and returns the next level of authorisation in the queue
	 * @param $authlevel
	 * @return integer					-The level of the next authoriser in the queue (4 is finance, 5 is user)
	 */
	private function getNextAuthLevel($authlevel) {
		$checklevel = $authlevel;
		do {
			$checklevel = $checklevel + 1;
			//fb("getNextAuthLevel - checking level: $checklevel");
			
			switch ($checklevel) {
				case 5:		//checklevel is 5: all authlevels and finance have passed and the owner should be notified
					//$nextauthlevel = 0; 											//break iteration loop, continue to notify user
					return 5;
					break;
				case 4:		//checklevel is 4: all authlevels have passed and finance should be next
					if ($this->getField("finance_response") == "Pending") {			//if finance has not confirmed/denied
						//$nextauthlevel = 4;												//break iteration loop, continue to notify finance
						return 4;
					} 																//else loop and iterate
					break;
				default:	//minimum authlevel is 1, so for checklevel = 1-3, determine if the authlevel's guid is set, if so, check if they have responded (!= Pending)
					$checklevelguid = $this->getField("auth_level_".(string)$checklevel);
					//fb("getNextAuthLevel - level $checklevel guid: $checklevelguid");
					
					if ($checklevelguid != "") {	//if auth_level_<1-3> is required
						$checklevelresponse = $this->getField("level_".(string)$checklevel."_response");
						//fb("getNextAuthLevel - level $checklevel response: $checklevelresponse");
						if ($checklevelresponse == "Pending") {		//if they have not confirmed/denied
							//$nextauthlevel = $checklevel;									//break iteration loop, continue to notify auth_level_<1-3>
							return $checklevel;
						} 																//else loop and iterate
					} 																//else loop and iterate
					break;	
			}
		} while (true);	//loop infinitely - function will complete when a value is returned.
}
	
	private function getFirstNameFromGuid($guid) {
		$this->d("Attempting FirstName from ".$guid);
		$user = new TmnCrudUser($this->logfile, $guid);
		$user->retrieve();
		return $user->getField("firstname");
	}
	
	private function getNameFromGuid($guid) {
		$this->d("Attempting name from ".$guid);
		$user = new TmnCrudUser($this->logfile, $guid);
		$user->retrieve();
		return $user->getField("firstname")." ".$user->getField("surname");
	}
	
	private function getEmailFromGuid($guid) {
		$this->d("Attempting email from ".$guid);
		$user = new TmnCrudUser($this->logfile, $guid);
		$user->retrieve();
		return $user->getField("email");
	}
	

	public function userIsAuthoriser(TmnCrudUser $user) {
		$returndata = null;
		
		$authorisers = array(
			0	=> $this->getField("auth_user"),
			1	=> $this->getField("auth_level_1"),
			2	=> $this->getField("auth_level_2"),
			3	=> $this->getField("auth_level_3")
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
	public function submit( TmnCrudUser $auth_user, TmnCrudUser $auth_level_1, $auth_level_1_reasons = null, TmnCrudUser $auth_level_2 = null, $auth_level_2_reasons = null, TmnCrudUser $auth_level_3 = null, $auth_level_3_reasons = null) {
			$this->authsessionid = 	$this->create();
			
									$this->setField('auth_session_id', 		$this->authsessionid);
									$this->setField("auth_user", 			$auth_user->getGuid());
									$this->setField("user_response", 		"Yes");
		if ($auth_level_1){ 		$this->setField("auth_level_1", 		$auth_level_1->getGuid()); }
		if ($auth_level_1_reasons){	$this->setField("auth_level_1_reasons",	$auth_level_1_reasons);	}
		if ($auth_level_2){			$this->setField("auth_level_2",			$auth_level_2->getGuid()); }
		if ($auth_level_2_reasons){	$this->setField("auth_level_2_reasons", $auth_level_2_reasons);	}
		if ($auth_level_3){			$this->setField("auth_level_3",			$auth_level_3->getGuid());	}
		if ($auth_level_3_reasons){	$this->setField("auth_level_3_reasons", $auth_level_3_reasons);	}
									//$this->setField("user_TIMESTAMP", 		now());
		
		$this->update();
		$this->retrieve();
		fb($this);
		$useremailaddress = $this->notifyOfSubmission(0);	//nofify the user (0)
		$notifyemailaddress = $this->notifyOfSubmission($this->getNextAuthLevel(0));	//notify the next authoriser after the user (0)
			
		return array("authsessionid" => $this->authsessionid, "useremailaddress" => $useremailaddress);
	}
	
	
	
}

?>