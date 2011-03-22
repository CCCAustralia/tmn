<?php

include_once('../classes/TmnUser.php');
include_once('../classes/TmnAuthorisor.php');

class TmnSession extends TmnUser {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	private static $table_name	= "Tmn_Sessions";
	protected $session_id		= null;
	private $session_guid		= null;
	private $session_fan		= null;
	private $auth_session_id	= null;
	
	private $session_authorisor	= null;
	
	//private $firstname = null;
	protected $session = array(
			'home_assignment_session_id'			=>	null,
			'international_assignment_session_id'	=>	null,
			'date_modified'							=>	null,
			'os_assignment_start_date'				=>	null,
			'os_assignment_end_date'				=>	null,
			'os_resident_for_tax_purposes'			=>	null,
			'net_stipend'							=>	null,
			'tax'									=>	null,
			'additional_tax'						=>	null,
			'post_tax_super'						=>	null,
			'taxable_income'						=>	null,
			'pre_tax_super'							=>	null,
			'additional_life_cover'					=>	null,
			'mfb'									=>	null,
			'additional_housing_allowance'			=>	null,
			'os_overseas_housing_allowance'			=>	null,
			'financial_package'						=>	null,
			'employer_super'						=>	null,
			'mmr'									=>	null,
			'stipend'								=>	null,
			'housing_stipend'						=>	null,
			'housing_mfb'							=>	null,
			'mfb_rate'								=>	null,
			'claimable_mfb'							=>	null,
			'total_super'							=>	null,
			'resc'									=>	null,
			'super_fund'							=>	null,
			'income_protection_cover_source'		=>	null,
			's_net_stipend'							=>	null,
			's_tax'									=>	null,
			's_additional_tax'						=>	null,
			's_post_tax_super'						=>	null,
			's_taxable_income'						=>	null,
			's_pre_tax_super'						=>	null,
			's_additional_life_cover'				=>	null,
			's_mfb'									=>	null,
			's_additional_housing_allowance'		=>	null,
			's_os_overseas_housing_allowance'		=>	null,
			's_financial_package'					=>	null,
			's_employer_super'						=>	null,
			's_mmr'									=>	null,
			's_stipend'								=>	null,
			's_housing_stipend'						=>	null,
			's_housing_mfb'							=>	null,
			's_mfb_rate'							=>	null,
			's_claimable_mfb'						=>	null,
			's_total_super'							=>	null,
			's_resc'								=>	null,
			's_super_fund'							=>	null,
			's_income_protection_cover_source'		=>	null,
			'joint_financial_package'				=>	null,
			'total_transfers'						=>	null,
			'workers_comp'							=>	null,
			'ccca_levy'								=>	null,
			'tmn'									=>	null,
			'buffer'								=>	null,
			'international_donations'				=>	null,
			'additional_housing'					=>	null,
			'monthly_housing'						=>	null,
			'housing'								=>	null,
			'housing_frequency'						=>	null
	);
	
		protected $session_types = array(
			'home_assignment_session_id'			=>	"i",
			'international_assignment_session_id'	=>	"i",
			'date_modified'							=>	"s",
			'os_assignment_start_date'				=>	"s",
			'os_assignment_end_date'				=>	"s",
			'os_resident_for_tax_purposes'			=>	"s",
			'net_stipend'							=>	"i",
			'tax'									=>	"i",
			'additional_tax'						=>	"i",
			'post_tax_super'						=>	"i",
			'taxable_income'						=>	"i",
			'pre_tax_super'							=>	"i",
			'additional_life_cover'					=>	"i",
			'mfb'									=>	"i",
			'additional_housing_allowance'			=>	"i",
			'os_overseas_housing_allowance'			=>	"i",
			'financial_package'						=>	"i",
			'employer_super'						=>	"i",
			'mmr'									=>	"i",
			'stipend'								=>	"i",
			'housing_stipend'						=>	"i",
			'housing_mfb'							=>	"i",
			'mfb_rate'								=>	"s",
			'claimable_mfb'							=>	"i",
			'total_super'							=>	"i",
			'resc'									=>	"i",
			'super_fund'							=>	"s",
			'income_protection_cover_source'		=>	"s",
			's_net_stipend'							=>	"i",
			's_tax'									=>	"i",
			's_additional_tax'						=>	"i",
			's_post_tax_super'						=>	"i",
			's_taxable_income'						=>	"i",
			's_pre_tax_super'						=>	"i",
			's_additional_life_cover'				=>	"i",
			's_mfb'									=>	"i",
			's_additional_housing_allowance'		=>	"i",
			's_os_overseas_housing_allowance'		=>	"i",
			's_financial_package'					=>	"i",
			's_employer_super'						=>	"i",
			's_mmr'									=>	"i",
			's_stipend'								=>	"i",
			's_housing_stipend'						=>	"i",
			's_housing_mfb'							=>	"i",
			's_mfb_rate'							=>	"s",
			's_claimable_mfb'						=>	"i",
			's_total_super'							=>	"i",
			's_resc'								=>	"i",
			's_super_fund'							=>	"s",
			's_income_protection_cover_source'		=>	"s",
			'joint_financial_package'				=>	"i",
			'total_transfers'						=>	"i",
			'workers_comp'							=>	"i",
			'ccca_levy'								=>	"i",
			'tmn'									=>	"i",
			'buffer'								=>	"i",
			'international_donations'				=>	"i",
			'additional_housing'					=>	"i",
			'monthly_housing'						=>	"i",
			'housing'								=>	"i",
			'housing_frequency'						=>	"s"
	);
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile, $session_id=null) {
		
		parent::__construct($logfile);
		$this->session_authorisor	= new TmnAuthorisor($logfile);
		
		if ($session_id != null) {
			$this->session_id		= $session_id;
			try {
				$this->retrieveSession();
			} catch (LightException $e) {
				throw new FatalException($e->getMessage());
			}
		}
	}
	
	
			///////////////////ACCESSOR FUNCTIONS/////////////////////
	
			
	public function getSessionID() {
		return $this->session_id;
	}
	
	public function setSessionID($session_id) {
		$this->session_id = $session_id;
	}
	
	public function getHomeAssignmentID() {
		return $this->session['home_assignment_session_id'];
	}
	
	public function setHomeAssignmentID($session_id) {
		$this->session['home_assignment_session_id'] = $session_id;
	}
	
	public function getInternationalAssignmentID() {
		return $this->session['international_assignment_session_id'];
	}
	
	public function setInternationalAssignmentID($session_id) {
		$this->session['international_assignment_session_id'] = $session_id;
	}
	
	public function resetSession() {
		$this->session_id		= null;
		$this->session_guid		= null;
		$this->session_fan		= null;
		$this->auth_session_id	= null;
		
		foreach ($this->session as $key=>$value) {
			$this->session[$key] = null;
		}
	}
	
	/*
	public function getJsonArray() {
		
		$sql	= "SELECT `JSON` FROM `Sessions`";
		$stmt	= $this->db->query($sql);
		$jsonArray	= $stmt->fetchAll(PDO::FETCH_COLUMN);
		
		return $jsonArray;
	}
	
	public function findGuid() {
		
		$sql	= "SELECT `GUID` FROM `User_Profiles` WHERE (`FIN_ACC_NUM` = :fan AND `FIRSTNAME` = :firstname)";
		$values	= array(":fan"=>$this->fan, ":firstname"=>$this->firstname);
		$stmt	= $this->db->prepare($sql);
		$stmt->execute($values);
		$guidArray	= $stmt->fetch(PDO::FETCH_ASSOC);
		
		return $guidArray['GUID'];
	}
	*/
	
			///////////////////CRUD BY SESSION_ID/////////////////////
	
	
	public function createSession() {
		
		//init variables for generating query
		$sql		= "INSERT INTO `" . self::$table_name . "` (`FAN`, `GUID`, ";
		//$values		= array(":fan" => $this->session_fan, ":guid" => $this->session_guid);
		$values		= array(":fan" => $this->getFan(), ":guid" => $this->getGuid());
		
		//add the sql query the fields to be INSERTed into database
		foreach ($this->session as $key=>$value) {
			if ($value != NULL) {
				$sql					.=	"`" . strtoupper($key) . "`, ";
			}
		}
		
		$sql = trim($sql, ", ") . ") VALUES (:fan, :guid, ";
		
		//check and add the values to the query
		foreach ($this->session as $key=>$value) {
			
			if ($value != NULL && $value != "") {
				
				try {
					$this->checkType($key);
				} catch (LightException $e) {
					$this->exceptionHandler($e);
				}
				
				$variableName			 =	":" . $key;
				$sql					.=	$variableName . ", ";
				$values[$variableName]	 =	$this->session[$key];
			}
		}

		$sql = trim($sql, ", ") . ")";
		
		//run the query
		try {
			$stmt		= $this->db->prepare($sql);
			$stmt->execute($values);
			$this->session_id	= (int)$this->db->lastInsertId();
		} catch (PDOException $e) {
			throw new LightException("Session Exception: " . $e->getMessage());
		}
		
		return $this->session_id;
	}
	
	public function retrieveSession() {
		
		//init variables for generating query
		$sql		= "SELECT `AUTH_SESSION_ID`, `GUID`, `FAN`, ";
		$values		= array();
		
		//create the sql SELECT query
		foreach ($this->session as $key=>$value) {
			$sql	.=	"`" . strtoupper($key) . "`, ";
		}
		
		$sql			= trim($sql, ", ") . " FROM `" . self::$table_name . "` WHERE `SESSION_ID` = :session_id";
		$values[":session_id"]	= $this->session_id;

		//run the query
		try {
			$stmt		= $this->db->prepare($sql);
			$stmt->execute($values);
			
			$results		= $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($stmt->rowCount() == 0) {
				throw new LightException("Session Exception: On Retrieve, Session Not Found");
			} elseif ($stmt->rowCount() == 1) {
				//copy results into instance variables
				$this->session_guid	= $results['GUID'];
				$this->session_fan	= $results['FAN'];
				
				$this->auth_session_id		= $results['AUTH_SESSION_ID'];
				$this->session_authorisor->loadAuthSession($this->auth_session_id);
				
				foreach ($this->session as $key=>$value) {
					if (isset($results[strtoupper($key)])) {
						$result = $results[strtoupper($key)];
						
						if ($this->session_types[$key] == "i") {$result = (int)$result;}
						$this->session[$key]	= $result;
					}
				}
			} else {
				throw new LightException("Session Exception: Session Conflict");
			}
			
		} catch (PDOException $e) {
			throw new LightException("Session Exception: " . $e->getMessage());
		} catch (LightException $e) {
			throw $e;
		}
	}
	
	public function updateSession() {
		
		//init variables for generating query
		$sql				= "UPDATE `" . self::$table_name . "` SET ";
		$values				= array();
		
		//check and add the values to the query
		foreach ($this->session as $key=>$value) {
			
			if ($value != NULL && $value != "") {
				
				try {
					$this->checkType($key);
				} catch (LightException $e) {
					$this->exceptionHandler($e);
				}
				
				$variableName			 =	":" . $key;
				$sql					.= "`" . strtoupper($key) . "` = " . $variableName . ", ";
				$values[$variableName]	 =	$this->session[$key];
			}
		}
		
		$sql				 = trim($sql, ", ");
		$sql				.= " WHERE `SESSION_ID` = :session_id";
		$values[":session_id"]	 = $this->session_id;
		
		//run the query
		try {
			$stmt			 = $this->db->prepare($sql);
			$stmt->execute($values);
		} catch (PDOException $e) {
			throw new LightException("Session Exception: " . $e->getMessage());
		}
	}
	
	public function deleteSession() {
		
		//init query
		$sql					= "DELETE FROM `" . self::$table_name . "` WHERE `SESSION_ID` = :session_id";
		$values					= array(":session_id" => $this->session_id);
		
		//run the query
		try {
			$stmt				= $this->db->prepare($sql);
			$stmt->execute($values);
		} catch (PDOException $e) {
			throw new LightException("Session Exception: " . $e->getMessage());
		}
	}
	
	//type checks the fields for the user and throws an exception if anything is wrong
	public function checkType($key) {
		
		switch ($this->session_types[$key]) {
			case 's':
				if (!is_string($this->session[$key])) {
					throw new LightException("Session Exception: Type mismatch. " . $key . "=" . $this->session[$key] . ". It is of type (" . gettype($this->session[$key]) . ") should be of type (string)");
				}
			break;
			case 'i':
				if (!is_int($this->session[$key])) {
					throw new LightException("Session Exception: Type mismatch. " . $key . "=" . $this->session[$key] . ". It is of type (" . gettype($this->session[$key]) . ") should be of type: (int)");
				}
			break;
			case 'n':
				if (!is_null($this->session[$key])) {
					throw new LightException("Session Exception: Type mismatch. " . $key . "=" . $this->session[$key] . ". It is of type (" . gettype($this->session[$key]) . ") should be of type: (NULL)");
				}
			break;
			case 'b':
				if (!is_bool($this->session[$key])) {
					throw new LightException("Session Exception: Type mismatch. " . $key . "=" . $this->session[$key] . ". It is of type (" . gettype($this->session[$key]) . ") should be of type: (bool)");
				}
			break;
			case 'l':
			break;
			
			default:
				throw new LightException("Session Exception: Unable to check type; Type not known for " . $key . "=" . $this->session[$key] . ".");
			break;
		}
	}
	
	
			///////////////////SESSION INTERFACE/////////////////////
			
	
	public function authUserIsAuthorisor() {
		return $this->session_authorisor->userIsAuthorisor($this->getAuthGuid());
	}
	
	public function loadSessionFromJson($jsonObj) {
		
		$this->resetSession();
		
		//$this->fan = $jsonObj['fan'];
		//$this->firstname = $jsonObj['firstname'];
		
		foreach ($this->session as $key=>$value) {
			
			if (isset($jsonObj[$key])) {
				
				if ($this->session_types[$key] == "i") {
					$jsonObj[$key] = (int)$jsonObj[$key];
				}
				
				$this->session[$key] = $jsonObj[$key];
			}
			
		}
		
	}
	
	public function createSessionFromJson($jsonObj) {
		$this->loadSessionFromJson($jsonObj);
		/*
		$this->guid = $this->findGuid();
		if ($this->guid == null){
			$this->guid = "exception-noLongerWithCCCA";
		}
		*/
		return $this->createSession();
	}
	
	public function updateSessionFromJson($jsonObj) {
		$this->loadSessionFromJson($jsonObj);
		$this->updateSession();
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>