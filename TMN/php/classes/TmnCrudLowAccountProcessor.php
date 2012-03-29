<?php
if(file_exists('../classes/TmnCrud.php')) {
	include_once('../interfaces/TmnCrudLowAccountProcessorInterface.php');
	include_once('../classes/TmnCrud.php');
	include_once('../classes/email.php');
}
if(file_exists('classes/TmnCrud.php')) {
	include_once('interfaces/TmnCrudLowAccountProcessorInterface.php');
	include_once('classes/TmnCrud.php');
	include_once('classes/email.php');
}
if(file_exists('php/classes/TmnCrud.php')) {
	include_once('php/interfaces/TmnCrudLowAccountProcessorInterface.php');
	include_once('php/classes/TmnCrud.php');
	include_once('php/classes/email.php');
}

class TmnCrudLowAccountProcessor extends TmnCrud implements TmnCrudLowAccountProcessorInterface {
	
	public function __construct($logfile, $financial_account_number=null) {
		
		parent::__construct(
			$logfile,						//path of logfile
			"Low_Account",				//name of table
			"fin_acc_num",							//name of table's primary key
			array(							//an assoc array of private field names and there types
				'id'					=>	"i"
			),
			array(							//an assoc array of public field names and there types
				'fin_acc_num'			=>	"i",
				'current_session_id'	=>	"i",
				'tmn_effective_date'	=>	"s",
				'consecutive_low_months'=>	"i",
				'pinkslip_exemption'	=>	"i",
				'mpd_plan'				=>	"i",
				'restrict_mfbmmr'		=>	"i"
			)
		);
		
		try {
			if (isset($financial_account_number)) {
				
				$this->loadRowWithFan((int)$financial_account_number);
				
			}
		} catch (Exception $e) {
			throw new FatalException(__CLASS__ . " Exception: " . $e->getMessage());
		}
		
	}
	
	
			///////////////////ACCESSOR FUNCTIONS/////////////////////
	
	
	public function loadRowWithFan($financial_account_number) {
		
		$tempFan = $this->getFan();
		
		$this->setField('fin_acc_num', $financial_account_number);
		
		try {
			$this->retrieve();
		} catch (LightException $e) {
			$this->setField('fin_acc_num', $tempFan);
			$this->exceptionHandler(new LightException(__CLASS__ . " Exception: Cannot load Low Account Row with FAN=" . $financial_account_number . "************ . The previous FAN was restored. The following Exception was thrown when load was attempted:" . $e->getMessage()));
		}
	}
	
	public function getFan() {
		return $this->getField('fin_acc_num');
	}
	
	public function getCurrentSessionID() {
		return $this->getField('current_session_id');
	}
	
	public function getCurrentSession() {
		
		if(file_exists('../classes/TmnCrudSession.php'))	{ include_once('../classes/TmnCrudSession.php'); }
		if(file_exists('classes/TmnCrudSession.php'))		{ include_once('classes/TmnCrudSession.php'); }
		if(file_exists('php/classes/TmnCrudSession.php'))	{ include_once('php/classes/TmnCrudSession.php'); }
		
		if ($this->getCurrentSessionID() != null) {
			if ($this->current_session == null) {
				$this->current_session = new TmnCrudSession($this->logfile, $this->getCurrentSessionID());
			}
			
			return $this->current_session;
		} else {
			//if no id set then make sure current_session is null (data may have been wiped by parent in mean time so
			//if reset has been done then apply it here too) and return false
			$this->current_session = null;
			return false;
		}
	}
	
	public function getEffectiveDateForCurrentSession() {
		return $this->getField('tmn_effective_date');
	}
	
	public function updateCurrentSession($session_id, $date_made_effective) {
		
		$tempSessionId	= $this->getField("current_session_id");
		$tempDate		= $this->getField("tmn_effective_date");
		
		$this->setField("current_session_id", $session_id);
		$this->setField("tmn_effective_date", $date_made_effective);
		
		try {

			$this->update();
			
		} catch (Exception $e) {
			
			$this->setField("current_session_id", $tempSessionId);
			$this->setField("tmn_effective_date", $tempDate);
			
			$this->exceptionHandler(new LightException(__CLASS__ . " Exception: Cannot update Low Account Row with CURRENT_SESSION_ID=" . $session_id . " and TMN_EFFECTIVE_DATE=" . $date_made_effective . "************ . The previous values were restored. The following Exception was thrown when update was attempted:" . $e->getMessage()));
			
			return false;
		}
		
		return true;
		
	}
	
	
			///////////////////LOW ACCOUNT PROCESSING FUNCTIONS/////////////////////
	
	
	static public function compareAllAccounts() {
		
		$currentBalances	= TmnCrudLowAccountProcessor::getAllCurrentBalances();
		$requiredBalances	= TmnCrudLowAccountProcessor::getAllRequiredBalances();
		
		foreach ($requiredBalances as $financial_account_number => $required_balance) {
			
			$usersLowAccountProfile = new TmnCrudLowAccountProcessor($logfile, $financial_account_number);

			//if your account is lower than your required buffer then update that
			if ($currentBalances[$financial_account_number] < $required_balance) {
						
				$usersLowAccountProfile->accountIsLowThisMonth();
				
			//if the account is above the buffer
			} else {
				
				
				//if the user is in low account make sure they are above the 200% tmn before letting them leave low account
				if ( $this->getField("consecutive_low_months") > 0 ) {

					$session = $usersLowAccountProfile->getCurrentSession();
					
						//if the user isn't above 200% then they are low again
					if ($currentBalances[$financial_account_number] < $session->getField("tmn") * 2 ) {
					
						$usersLowAccountProfile->accountIsLowThisMonth();

						//otherwise they are leaving low account
					} else {
						
						$usersLowAccountProfile->actOnLeavingLowAccount();
						
					}
					
				}
			
			}
			
		}
		
	}
	
	//needs to return an associative array of required balances that are indexed by their financial account number eg array(1010045 => 20000, 1010001 => 5000, <financial account number 7>, <required balance for financial account number 7>)
	static private function getAllRequiredBalances() {
		
		$db				= TmnDatabase::getInstance($LOGFILE);
		$balanceSql		= "SELECT low.FIN_ACC_NUM, sessions.BUFFER FROM ( SELECT low_acc.* FROM (SELECT DISTINCT FIN_ACC_NUM FROM User_Profiles WHERE IS_TEST_USER = 0 AND INACTIVE = 0) AS valid_users LEFT JOIN Low_Account AS low_acc ON low_acc.FIN_ACC_NUM = valid_users.FIN_ACC_NUM ) AS low LEFT JOIN Tmn_Sessions AS sessions ON low.CURRENT_SESSION_ID = sessions.SESSION_ID AND low.CURRENT_SESSION_ID IS NOT NULL";
		$stmt 			= $db->prepare($sessionSql);
		$balanceResult	= $stmt->fetchAll(PDO::FETCH_ASSOC);
		$returnArray	= array();
		
		foreach ($balanceResult as $key => $row) {
			
			$returnArray[$row["FIN_ACC_NUM"]] = $row["BUFFER"];
			
		}
		
		return $returnArray;
	}
	
	//needs to return an associative array of balances that are indexed by their financial account number eg array(1010000 => 15670, 1010001 => 7430, <financial account number 3>, <balance for financial account number 3>)
	static private function getAllCurrentBalances() {
		
		//Kent, Do soap calls here
		
	}
	
	public function accountIsLowThisMonth() {
		
		$low_months = $this->getField("consecutive_low_months");
		$low_months++;
		$this->setField("consecutive_low_months", $low_months);
		
		$this->actOnLowMonthsReaching($low_months);
		
		$this->update();
		
	}
	
	public function actOnLeavingLowAccount() {

		//Kent, apply leaving lwo account policies here
		
		//the following are some examples of things you will need to do in applying these policies
		
		//example for setting fields and then updating the database with your changes
		$this->setField("consecutive_low_months", 0);
		$this->setField("pinkslip_exemption", 0);
		$this->setField("mpd_plan", 0);
		$this->setField("restrict_mfbmmr", 0);
		$this->update();//puts the data in the data base
			
		//example for emailing the user and spouse (if spouse exists) for the session
		$session	= $this->getCurrentSession();
		$user		= $session->getOwner();
		$spouse		= $user->getSpouse();
		$userEmail	= $user->getEmail();
		
		// example of constructing an email
		$email_cc		= "";
		$email_to		= $userEmail . ($spouse ? "," . $spouse->getEmail() : "") . "," . $email_cc;
		$email_from		= "CCCA TMN <noreply@ccca.org.au>\r\nReply-To: noreply@ccca.org.au";
		$email_subject	= "My Subject";
		$email_body		= "This is the body";
		
		//example of sending an email
		$notifyemail = new Email($email_to, $email_subject, $email_body, $email_from);
		$notifyemail->send();
		
		
	}
	
	private function actOnLowMonthsReaching($low_months) {
		
		//Kent look at actOnLeavingLowAccount for email example
		
		//Kent, apply policies in here
		
		switch ((int)$low_months) {
			
			//apply policies for one month in low account
			case 1:
				
				break;
			
			//apply policies for two months in low account
			case 2:
				
				break;
				
			//apply policies for three months in low account
			case 3:
				
				break;
				
			//apply policies for four months in low account
			case 4:
				
				break;
				
			//apply policies for five months in low account
			case 5:
				
				break;
				
			//apply policies for six months in low account
			case 6:
				
				break;
				
			//apply policies for seven months in low account
			case 7:
				
				break;
				
			//apply policies for eight months in low account
			case 8:
				
				break;
				
			//apply policies for nine months in low account
			case 9:
				
				break;
				
			default:
				return;
		}
		
	}

}

?>