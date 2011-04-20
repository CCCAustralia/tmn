<?php
if (file_exists('../classes/TmnCrud.php')) {
	include_once('../interfaces/TmnAuthorisationProcessorInterface.php');
	include_once('../classes/email.php');
	include_once('../classes/TmnCrud.php');
	include_once('../classes/TmnAuthenticator.php');
	include_once('../classes/TmnConstants.php');
} elseif (file_exists('classes/TmnCrud.php')) {
	include_once('interfaces/TmnAuthorisationProcessorInterface.php');
	include_once('classes/email.php');
	include_once('classes/TmnCrud.php');
	include_once('classes/TmnAuthenticator.php');
	include_once('classes/TmnConstants.php');
} else {
	include_once('php/interfaces/TmnAuthorisationProcessorInterface.php');
	include_once('php/classes/email.php');
	include_once('php/classes/TmnCrud.php');
	include_once('php/classes/TmnAuthenticator.php');
	include_once('php/classes/TmnConstants.php');
}

class TmnAuthorisationProcessor extends TmnCrud implements TmnAuthorisationProcessorInterface {
	
			////Instance Variables////
	//private $logfile;
	private $authsessionid;
	private $financeguid;
	//private $authcrud;
	private $level_users	= array();
	
	
	
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
				'auth_user_reasons'		=> "s",
				'auth_level_1_reasons'	=> "s",
				'auth_level_2_reasons' 	=> "s",
				'auth_level_3_reasons'	=> "s",
				'auth_finance_reasons'	=> "s",
				'user_timestamp' 		=> "s",
				'level_1_timestamp'		=> "s",
				'level_2_timestamp' 	=> "s",
				'level_3_timestamp'		=> "s",
				'finance_timestamp'		=> "s"
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
		
		//get the finance user guid
		$this->financeguid = getConstants(array('FINANCE_USER'));
		$this->financeguid = $this->financeguid['FINANCE_USER'];
		
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
	
	private function getFinanceGuid() {
		$constants = getConstants(array("FINANCE_USER"));
		return $constants['FINANCE_USER'];
	}
	
	private function getUserForLevel($level) {
		//if this levels user object has not yet been grabbed then grab it (while doing conversion from number to bad naming system)
		if (!isset($this->level_users[$level])){
			if ($level == 0) {
				if ($this->getField('auth_user') != "" && $this->getField('auth_user') != null) {
					$this->level_users[$level]	= new TmnCrudUser($this->getLogfile(), $this->getField('auth_user'));
				} else {
					unset($this->level_users[$level]);
					return null;
				}
			} elseif ($level == 4) {
				$this->level_users[$level]	= new TmnCrudUser($this->getLogfile(), $this->getFinanceGuid());
			} else {
				if ($this->getField('auth_level_'.$level) != "" && $this->getField('auth_level_'.$level) != null) {
					$this->level_users[$level]	= new TmnCrudUser($this->getLogfile(), $this->getField('auth_level_' . $level));
				} else {
					unset($this->level_users[$level]);
					return null;
				}
			}
		}
		
		//return the object for that level
		return $this->level_users[$level];
	}
	
	
	
			///////////////////ACTION FUNCTIONS/////////////////////

	public function authorise(TmnCrudUser $user, $response, $session_id) {
		
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
		$this->d("AUTHORISE: userauthlevel=".$userauthlevel);
		
		if (!is_null($userauthlevel)) {
			//Form the identifying field name string for the level of authentication for which the user is valid
			if ($userauthlevel == 0) {
				$fieldname = "user";
			} elseif ($userauthlevel == 4) {
				$fieldname = "finance";
			} else {
				$fieldname = "level_".$userauthlevel;
			}
			
			//set the response
			$this->setField($fieldname."_response", $response);
			$this->d("RESPONSE FOR LVL ".$userauthlevel." UPDATED TO ".$response);
			//$this->d($this);
			
			$now = getdate();
			
			$this->setField($fieldname."_timestamp", date( 'Y-m-d H:i:s', $now[0]));
			$this->d($this->getField($fieldname."_timestamp"));
			
			$this->update();
			
			//email the appropriate recipiants
			if ($response == "Yes") {
				//$authlevel = current user's authlevel
				$nextauthlevel = $this->getNextAuthLevel($userauthlevel);	//calculate the next authlevel
				//$nextauthlevel = 3; //FOR DEBUGGING
				$this->notifyOfSubmission($nextauthlevel, $session_id);							//notify the calculated level
			}
			if ($response == "No") {
				$this->notifyUserOfRejection($userauthlevel, $session_id);
			}
			
		} else {
			throw new LightException("Specified user is not an authoriser");
		}
		
	}
	
	private function notifyOfSubmission($notifylevel, $session_id) {
		$this->d("TmnAuthorisationProcessor - notifying level: $notifylevel of approval");
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
		if ($curpageurl[count($curpageurl) - 1] == "php") {
			unset($curpageurl[count($curpageurl) -1]);	//take off php/
		}
		$curpageurl = join("/",	$curpageurl);
		$authviewerurl = $curpageurl."/tmn-authviewer.php?session=".$session_id;
		
		//DEBUG OUTPUT
		$this->d("authguids: "); $this->d($authguids);
		$this->d("authresponses:"); $this->d($authresponses);
		
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
			
			$this->notifyOfSubmission(5, $session_id);	//notify user of processing
		}
		
		//notify user:
		if ($notifylevel == 5) {
			$this->d("Notify user: finance response=".$authresponses[4]);
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
				$this->d("Level ".$k." user's name: ".$this->getNameFromGuid($v));
				//$emailbody .= "\n".$k.": ".$this->getNameFromGuid($v)." responsed: ".$authresponses[$k]."\n";
			}
			$emailbody .= "\n\n-The TMN Development Team";
		}
		//ADD IF DEBUGGING
		//$emailbody .="\n\nDEBUG: target email=".$emailaddress;
		//$emailaddress = "tom.flynn@ccca.org.au";
		$notifyemail = new Email($emailaddress, $emailsubject, $emailbody, "CCCA TMN <noreply@ccca.org.au>\r\nReply-To: noreply@ccca.org.au");
		$this->d($notifyemail);
		$notifyemail->send();
		
		return $emailaddress;
	}
	
	private function notifyUserOfRejection($rejectedbylevel, $session_id) {
		$this->d("TmnAuthorisationProcessor - notifying user of rejection by level: $rejectedbylevel");
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
		$this->d("authguids: "); $this->d($authguids);
		$this->d("authresponses:"); $this->d($authresponses);
		
		$emailaddress 	= $this->getEmailFromGuid($authguids[0]);	//get the user's email
		
		
		if ($rejectedbylevel == 0) {			
	////REJECTED BY USER
			//prepare the email
			$emailsubject 	= "TMN: Cancellation";
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
		
		
		$this->d($emailbody);

		//ADD IF DEBUGGING
		//$emailbody .="\n\nDEBUG: target email=".$emailaddress;
		//$emailaddress = "tom.flynn@ccca.org.au";
		$rejectionemail = new Email($emailaddress, $emailsubject, $emailbody, "CCCA TMN <noreply@ccca.org.au>\r\nReply-To: noreply@ccca.org.au");
		$this->d($rejectionemail);
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
			//$this->d("getNextAuthLevel - checking level: $checklevel");
			
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
					//$this->d("getNextAuthLevel - level $checklevel guid: $checklevelguid");
					
					if ($checklevelguid != "") {	//if auth_level_<1-3> is required
						$checklevelresponse = $this->getField("level_".(string)$checklevel."_response");
						//$this->d("getNextAuthLevel - level $checklevel response: $checklevelresponse");
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
			3	=> $this->getField("auth_level_3"),
			4	=> $this->financeguid
		);
		$this->d("TmnAuthorisationProcessor.php<userIsAuthoriser() - authorisers:"); $this->d($authorisers);
		
		for ($i = 0; $i <= 4; $i++) {
			if ($user->getGuid() == $authorisers[$i] && $authorisers[$i] != "") {
				$returndata = $i;
			}
		}
		
		$this->d("userIsAuthoriser returning value:".$returndata);
		
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
	public function submit( TmnCrudUser $auth_user, $auth_user_reasons = null, TmnCrudUser $auth_level_1, $auth_level_1_reasons = null, TmnCrudUser $auth_level_2 = null, $auth_level_2_reasons = null, TmnCrudUser $auth_level_3 = null, $auth_level_3_reasons = null, $session_id) {
			$this->authsessionid = 	$this->create();
			$now = getdate();
			
									$this->setField('auth_session_id', 		$this->authsessionid);
									$this->setField("auth_user", 			$auth_user->getGuid());
									$this->setField("user_response", 		"Yes");
									$this->setField("auth_user_reasons", 	json_encode($auth_user_reasons));
		if ($auth_level_1){ 		$this->setField("auth_level_1", 		$auth_level_1->getGuid()); 
									$this->setField("auth_level_1_reasons",	json_encode($auth_level_1_reasons));	}
		if ($auth_level_2){			$this->setField("auth_level_2",			$auth_level_2->getGuid());
									$this->setField("auth_level_2_reasons", json_encode($auth_level_2_reasons));	}
		if ($auth_level_3){			$this->setField("auth_level_3",			$auth_level_3->getGuid());
									$this->setField("auth_level_3_reasons", json_encode($auth_level_3_reasons));	}
									$this->setField("user_timestamp", 		date( 'Y-m-d H:i:s', $now[0]));
		
		$this->update();
		$this->retrieve();
		
		$useremailaddress = $this->notifyOfSubmission(0, $session_id);	//nofify the user (0)
		$notifyemailaddress = $this->notifyOfSubmission($this->getNextAuthLevel(0), $session_id);	//notify the next authoriser after the user (0)
			
		return array("success" => true, "authsessionid" => $this->authsessionid, "useremailaddress" => $useremailaddress);
	}
	
	/**
	 * Fetches the current authorisation progress of the session
	 * 
	 * @return an assoc array containing the current response (Yes, No, Pending) and a name (who is responsible for that response)
	 * 			ie {response:<authorisers response>, name: <authorisers full name>, email: <auth email>, date: <the data of this action>}
	 */
	public function getOverallProgress() {
		
		$returnArray	= array("response" => "", "name" => "", "email" => "", "date" => "");
		
		//finance is the last response
		if ($this->getField('finance_response') == 'Pending') {
			
			//check if the user has withdrawn the submition
			if ($this->getField('user_response') == 'No') {
				//grab the user object for user
				$user						= $this->getUserForLevel(0);
				
				//set all the data we have access to
				$returnArray['response']	= $this->getField('user_response');
				if ($user != null) {
					$returnArray['name']		= $user->getField('firstname') . " " . $user->getField('surname');
					$returnArray['email']		= $user->getField('email');
				}
				$returnArray['date']		= date(" g:i a, j-M-Y", strtotime($this->getField('user_timestamp')));
				
				return $returnArray;
			}			
			
			//run through the other authorisers to see if they have rejected it or we are waiting on them
			$foundNo	= false;
			for ($responseCount = 1; $responseCount <= 3; $responseCount++) {
				if ($this->getField('level_' . $responseCount . '_response') == 'No') {
					$foundNo	= true;
					break;
				}
				
				if ($this->getField('level_' . $responseCount . '_response') == 'Pending') {
					break;
				}
			}
			
			//if it has been rejected by someone
			if ($foundNo) {
				//grab the user object for level 1
				$levelUser					= $this->getUserForLevel($responseCount);
				
				//set all the data we have access to
				$returnArray['response']	= $this->getField('level_' . $responseCount . '_response');
				if ($levelUser != null) {
					$returnArray['name']		= $levelUser->getField('firstname') . " " . $levelUser->getField('surname');
					$returnArray['email']		= $levelUser->getField('email');
				}
				$returnArray['date']			= date(" g:i a, j-M-Y", strtotime($this->getField('level_' . $responseCount . '_timestamp')));
				
				return $returnArray;
				
			} else {
				//if the level that the loop ended on is 3 (this last level) and the response is yes then we are actually waiting on finance
				//OR if the first level where pending is found doesn't have a guid set we are also actually waiting on finance
				if (($responseCount == 3 && $this->getField('level_' . $responseCount . '_response') == 'Yes')
					|| ($this->getField('auth_level_' . $responseCount) == "" || $this->getField('auth_level_' . $responseCount) == null)) {
					$returnArray['response']	= "Pending";
					$returnArray['name']		= "Finance";
					$returnArray['name']		= "payroll@ccca.org.au";
				
					return $returnArray;
				
				//if we aren't waiting on finance return who we are waiting on
				} else {
					//grab the user object for level 1
					$levelUser					= $this->getUserForLevel($responseCount);
					
					//set all the data we have access to
					$returnArray['response']	= $this->getField('level_' . $responseCount . '_response');
					if ($levelUser != null) {
						$returnArray['name']		= $levelUser->getField('firstname') . " " . $levelUser->getField('surname');
						$returnArray['email']		= $levelUser->getField('email');
					}
					$returnArray['date']		= date(" g:i a, j-M-Y", strtotime($this->getField('level_' . $responseCount . '_timestamp')));
				
					return $returnArray;
				}
			}
			
		//if finance has given a response return it
		} else {
			$returnArray['response']	= $this->getField('finance_response');
			$returnArray['name']		= "Finance";
			$returnArray['name']		= "payroll@ccca.org.au";
			$returnArray['date']		= date(" g:i a, j-M-Y", strtotime($this->getField('finance_timestamp')));
		}
		
		return $returnArray;
	}
	
	 /**
	  * Gets the details of the authoriser that matches the user passed to this function
	  * 
	  * @param TmnCrudUser $user		- the authoriser
	  * 
	  * @return an assoc array containing the current response (Yes, No, Pending)
	  * 		ie {response:<authorisers response>}
	  */
	public function getAuthoriserDetailsForUser(TmnCrudUser $user) {
		
		if ($user->getGuid() == $this->getField("auth_user")) {
			$reason	= json_decode($this->getField("auth_user_reasons"), true);
			$total	= 0;
			if (isset($reason['aussie-based'])) {
				$total += count($reason['aussie-based']['reasons']);
			}
			if (isset($reason['home-assignment'])) {
				$total += count($reason['home-assignment']['reasons']);
			}
			if (isset($reason['international-assignment'])) {
				$total += count($reason['international-assignment']['reasons']);
			}
			return array("response" => $this->getField("user_response"), 	"reasons" => $this->getField("auth_user_reasons"), "total" => $total);
			
		} elseif ($user->getGuid() == $this->getField("auth_level_1")) {
			$reason	= json_decode($this->getField("auth_level_1_reasons"), true);
			$total	= 0;
			if (isset($reason['aussie-based'])) {
				$total += count($reason['aussie-based']['reasons']);
			}
			if (isset($reason['home-assignment'])) {
				$total += count($reason['home-assignment']['reasons']);
			}
			if (isset($reason['international-assignment'])) {
				$total += count($reason['international-assignment']['reasons']);
			}
			return array("response" => $this->getField("level_1_response"), "reasons" => $this->getField("auth_level_1_reasons"), "total" => $total);
			
		} elseif ($user->getGuid() == $this->getField("auth_level_2")) {
			$reason	= json_decode($this->getField("auth_level_2_reasons"), true);
			$total	= 0;
			if (isset($reason['aussie-based'])) {
				$total += count($reason['aussie-based']['reasons']);
			}
			if (isset($reason['home-assignment'])) {
				$total += count($reason['home-assignment']['reasons']);
			}
			if (isset($reason['international-assignment'])) {
				$total += count($reason['international-assignment']['reasons']);
			}
			return array("response" => $this->getField("level_2_response"), "reasons" => $this->getField("auth_level_2_reasons"), "total" => $total);
			
		} elseif ($user->getGuid() == $this->getField("auth_level_3")) {
			$reason	= json_decode($this->getField("auth_level_3_reasons"), true);
			$total	= 0;
			if (isset($reason['aussie-based'])) {
				$total += count($reason['aussie-based']['reasons']);
			}
			if (isset($reason['home-assignment'])) {
				$total += count($reason['home-assignment']['reasons']);
			}
			if (isset($reason['international-assignment'])) {
				$total += count($reason['international-assignment']['reasons']);
			}
			return array("response" => $this->getField("level_3_response"), "reasons" => $this->getField("auth_level_3_reasons"), "total" => $total);
			
		} elseif ($user->getGuid() == $this->getFinanceGuid()) {
			$reason	= json_decode($this->getField("auth_finance_reasons"), true);
			$total	= 0;
			if (isset($reason['aussie-based'])) {
				$total += count($reason['aussie-based']['reasons']);
			}
			if (isset($reason['home-assignment'])) {
				$total += count($reason['home-assignment']['reasons']);
			}
			if (isset($reason['international-assignment'])) {
				$total += count($reason['international-assignment']['reasons']);
			}
			return array("response" => $this->getField("finance_response"), "reasons" => $this->getField("auth_finance_reasons"), "total" => $total);
			
		} else {
			
			return array("response" => "Pending", "reasons" => "[]", "total" => 0);
			
		}
	}
	
}

?>