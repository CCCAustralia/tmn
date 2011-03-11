<?php

include_once('Tmn.php');

class TmnUser extends Tmn {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	private static 	$table_name 	= "User_Profiles";
	private 		$user_id		= null;
	protected 		$user			= array(
			'firstname'		=>	null,
			'surname'		=>	null,
			'spouse_guid'	=>	null,
			'ministry'		=>	null,
			'ft_pt_os'		=>	null,
			'days_per_week'	=>	null,
			'fin_acc_num'	=>	null,
			'mpd'			=>	null,
			'm_guid'		=>	null,
			'admin_tab'		=>	null
	);
	protected 		$user_types		= array(
			'firstname'		=>	"s",
			'surname'		=>	"s",
			'spouse_guid'	=>	"s",
			'ministry'		=>	"s",
			'ft_pt_os'		=>	"i",
			'days_per_week'	=>	"i",
			'fin_acc_num'	=>	"i",
			'mpd'			=>	"i",
			'm_guid'		=>	"s",
			'admin_tab'		=>	"i"
	);
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile, $guid=null) {
		
		parent::__construct($logfile);
		
		if (isset($guid)) {
			parent::setGuid($guid);
		}
		
		try {
			$this->retrieveUser();
		} catch (LightException $e) {
			throw new FatalException("User Exception: Couldn't Load User's Profile due to error; " . $e->getMessage());
		}
	}
	
	
			////////////////ACCESSOR FUNCTIONS////////////////
	
	
	public function setGuid($guid) {
		
		$tempGuid = $this->getGuid();
		parent::setGuid($guid);
		
		try {
			$this->retrieveUser();
		} catch (LightException $e) {
			parent::setGuid($tempGuid);
			$this->exceptionHandler(new LightException("User Exception: Cannot Load User with guid=" . $guid . ". The previous guid was restored. The following Exception was thrown when load was attempted:" . $e->getMessage()));
		}
	}
	
	public function getFan() {
		return $this->user['fin_acc_num'];
	}
	
	public function getSpouseGuid() {
		return $this->user['spouse_guid'];
	}
	
	public function setSpouseGuid($guid) {
		if ($this->doesUserExist($guid)) {
			$this->user['spouse_guid'] = $guid;
		} else {
			throw new LightException("User Exception: Spouse couldn't be found.");
		}
	}
	
	public function getMpdGuid() {
		return $this->user['m_guid'];
	}
	
	public function setMpdGuid($guid) {
		if ($this->doesUserExist($guid)) {
			$this->user['m_guid'] = $guid;
		} else {
			throw new LightException("User Exception: MDP Supervisor couldn't be found.");
		}
	}
	
	public function isAdmin() {
		if ($this->user['admin_tab'] == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	private function doesUserExist($guid=null) {
		if ($guid == null) {
			return false;
		} else {
			$sql	= "SELECT `GUID` FROM `" . self::$table_name . "` WHERE `GUID` = :guid";
			try {
				$stmt		= $this->db->prepare($sql);
				$stmt->execute(array(":guid" => $guid));
				if ($stmt->rowCount() == 1) {
					return true;
				} else {
					return false;
				}
			} catch (PDOException $e) {
				return false;
			}
		}
	}
	
	public function resetUser() {
		foreach ($this->user as $key=>$value) {
			$this->user[$key] = null;
		}
	}
	
	
			///////////////////USER QUERY/////////////////////
			
	
	//alias for setGuid($guid)
	public function loadUserWithGuid($guid) {
		$this->setGuid($guid);
	}
	
	public function createUser() {
		
		//init variables for generating query
		$sql		= "INSERT INTO `" . self::$table_name . "` (`GUID`, ";
		$values		= array(":guid" => $this->getGuid());
		
		//add the sql query the fields to be INSERTed into database
		foreach ($this->user as $key=>$value) {
			if ($value != NULL) {
				$sql					.=	"`" . strtoupper($key) . "`, ";
			}
		}
		
		$sql = trim($sql, ", ") . ") VALUES (:guid, ";
		
		//check and add the values to the query
		foreach ($this->user as $key=>$value) {
			
			if ($value != NULL) {
				
				try {
					$this->checkType($key);
				} catch (LightException $e) {
					$this->exceptionHandler($e);
				}
				
				$variableName			 =	":" . $key;
				$sql					.=	$variableName . ", ";
				$values[$variableName]	 =	$this->user[$key];
			}
		}

		$sql = trim($sql, ", ") . ")";
		
		//run the query
		try {
			$stmt		= $this->db->prepare($sql);
			$stmt->execute($values);
			$this->user_id	= $this->db->lastInsertId();
		} catch (PDOException $e) {
			throw new LightException("User Exception: " . $e->getMessage());
		}
	}
	
	public function retrieveUser() {
		
		//init variables for generating query
		$sql		= "SELECT ";
		$values		= array();
		
		//create the sql SELECT query
		foreach ($this->user as $key=>$value) {
			$sql	.=	"`" . strtoupper($key) . "`, ";
		}
		
		$sql			= trim($sql, ", ") . " FROM `" . self::$table_name . "` WHERE `GUID` = :guid";
		$values[":guid"]	= $this->getGuid();

		//run the query
		try {
			$stmt		= $this->db->prepare($sql);
			$stmt->execute($values);
			
			$results		= $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($stmt->rowCount() == 0) {
				throw new LightException("User Exception: On Retrieve, User Not Found");
			} elseif ($stmt->rowCount() == 1) {
				//copy results into instance variables
				foreach ($this->user as $key=>$value) {
					if (isset($results[strtoupper($key)])) {
						$result = $results[strtoupper($key)];
						
						if ($this->user_types[$key] == "i") {$result = (int)$result;}
						$this->user[$key]	= $result;
					}
				}
			} else {
				throw new LightException("User Exception: User Conflict");
			}
			
		} catch (PDOException $e) {
			throw new LightException("User Exception: " . $e->getMessage());
		} catch (LightException $e) {
			throw $e;
		}
	}
	
	public function updateUser() {
		
		//init variables for generating query
		$sql				= "UPDATE `" . self::$table_name . "` SET ";
		$values				= array();
		
		//check and add the values to the query
		foreach ($this->user as $key=>$value) {
			
			if ($value != NULL) {
				
				try {
					$this->checkType($key);
				} catch (LightException $e) {
					$this->exceptionHandler($e);
				}
				
				$variableName			 =	":" . $key;
				$sql					.= "`" . strtoupper($key) . "` = " . $variableName . ", ";
				$values[$variableName]	 =	$this->user[$key];
			}
		}
		
		$sql				 = trim($sql, ", ");
		$sql				.= " WHERE `GUID` = :guid";
		$values[":guid"]	 = $this->getGuid();
		
		//run the query
		try {
			$stmt			 = $this->db->prepare($sql);
			$stmt->execute($values);
		} catch (PDOException $e) {
			throw new LightException("User Exception: " . $e->getMessage());
		}
	}
	
	public function deleteUser() {
		
		//init query
		$sql					= "DELETE FROM `" . self::$table_name . "` WHERE `GUID` = :guid";
		$values					= array(":guid" => $this->getGuid());
		
		//run the query
		try {
			$stmt				= $this->db->prepare($sql);
			$stmt->execute($values);
		} catch (PDOException $e) {
			throw new LightException("User Exception: " . $e->getMessage());
		}
	}
	
	//type checks the fields for the user and throws an exception if anything is wrong
	public function checkType($key) {
		
		switch ($this->user_types[$key]) {
			case 's':
				if (!is_string($this->user[$key])) {
					throw new LightException("User Exception: Type mismatch. " . $key . "=" . $this->user[$key] . ". It should be of type: String");
				}
			break;
			case 'i':
				if (!is_int($this->user[$key])) {
					throw new LightException("User Exception: Type mismatch. " . $key . "=" . $this->user[$key] . ". It should be of type: Integer");
				}
			break;
			case 'n':
				if (!is_null($this->user[$key])) {
					throw new LightException("User Exception: Type mismatch. " . $key . "=" . $this->user[$key] . ". It should be of type: NULL");
				}
			break;
			case 'b':
				if (!is_bool($this->user[$key])) {
					throw new LightException("User Exception: Type mismatch. " . $key . "=" . $this->user[$key] . ". It should be of type: Bool");
				}
			break;
			case 'l':
			break;
			
			default:
				throw new LightException("User Exception: Unable to check type; Type not known for " . $key . "=" . $this->user[$key] . ".");
			break;
		}
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>