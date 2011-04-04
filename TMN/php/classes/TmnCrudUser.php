<?php
if(file_exists('../classes/TmnCrud.php')) {
	include_once('../interfaces/TmnCrudUserInterface.php');
	include_once('../classes/TmnCrud.php');
}
if(file_exists('classes/TmnCrud.php')) {
	include_once('interfaces/TmnCrudUserInterface.php');
	include_once('classes/TmnCrud.php');
}
if(file_exists('php/classes/TmnCrud.php')) {
	include_once('php/interfaces/TmnCrudUserInterface.php');
	include_once('php/classes/TmnCrud.php');
}

class TmnCrudUser extends TmnCrud implements TmnCrudUserInterface {
	
	public function __construct($logfile, $guid=null) {
		
		parent::__construct(
			$logfile,						//path of logfile
			"User_Profiles",				//name of table
			"guid",							//name of table's primary key
			array(							//an assoc array of private field names and there types
				'guid'			=>	"s"
			),
			array(							//an assoc array of public field names and there types
				'firstname'		=>	"s",
				'surname'		=>	"s",
				'email'			=>	"s",
				'spouse_guid'	=>	"s",
				'ministry'		=>	"s",
				'ft_pt_os'		=>	"i",
				'days_per_week'	=>	"i",
				'fin_acc_num'	=>	"i",
				'mpd'			=>	"i",
				'm_guid'		=>	"s",
				'admin_tab'		=>	"i"
			)
		);
		
		try {
			if (isset($guid)) {
				$this->setGuid($guid);
			}
		} catch (Exception $e) {
			throw new FatalException(__CLASS__ . " Exception: " . $e->getMessage());
		}
		
	}
	
	
			///////////////////ACCESSOR FUNCTIONS/////////////////////
	
	
	public function getGuid() {
		return $this->getField('guid');
	}
	
	public function setGuid($guid) {
		
		$tempGuid = $this->getGuid();
		
		$this->setField('guid', $guid);
		
		try {
			$this->retrieve();
		} catch (LightException $e) {
			$this->setField('guid', $tempGuid);
			$this->exceptionHandler(new LightException(__CLASS__ . " Exception: Cannot Load User with guid=" . substr($this->guid, 0, -12) . "************ . The previous guid was restored. The following Exception was thrown when load was attempted:" . $e->getMessage()));
		}
	}
	
	public function getFan() {
		return $this->getField('fin_acc_num');
	}
	
	public function getSpouse() {
		if ($this->getSpouseGuid() != null) {
			if ($this->spouse == null) {
				$this->spouse = new TmnCrudUser($this->logfile, $this->getSpouseGuid());
			}
			
			return $this->spouse;
		} else {
			//if no guid set then make sure spouse is null (data may have been wiped by parent in mean time so
			//if reset has been done then apply it here too) and return false
			$this->spouse = null;
			return false;
		}
	}
	
	public function getSpouseGuid() {
		return $this->getField('spouse_guid');
	}
	
	public function setSpouseGuid($guid) {
		if ($this->doesUserExist($guid)) {
			$this->setField('spouse_guid', $guid);
		} else {
			throw new LightException(__CLASS__ . " Exception: Spouse couldn't be found.");
		}
	}
	
	public function setSpouseWithName($firstname, $surname) {
		$guid = $this->findUserWithName($firstname, $surname);
		
		if ($guid != null) {
			$this->setField('spouse_guid', $guid);
		} else {
			throw new LightException(__CLASS__ . " Exception: User with name: " . $firstname . " " . $surname . " not found.");
		}
	}
	
	public function getMpdGuid() {
		return $this->getField('m_guid');
	}
	
	public function setMpdGuid($guid) {
		if ($this->doesUserExist($guid)) {
			$this->setField('m_guid', $guid);
		} else {
			throw new LightException(__CLASS__ . " Exception: MDP Supervisor couldn't be found.");
		}
	}
	
	public function setMpdWithName($firstname, $surname) {
		$guid = $this->findUserWithName($firstname, $surname);
		
		if ($guid != null) {
			$this->setField('m_guid', $guid);
		} else {
			throw new LightException(__CLASS__ . " Exception: User with name: " . $firstname . " " . $surname . " not found.");
		}
	}
	
	public function isAdmin() {
		if ($this->getField('admin_tab') == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	private function findUserWithName($firstname, $surname) {
		
		//if there is something to find, run the query and return the user's guid
		if ($firstname != null && $surname != null) {
			$sql	= "SELECT `GUID` FROM `" . $this->table_name . "` WHERE `FIRSTNAME` = :firstname AND `SURNAME` = :surname";
			$values = array(":firstname"=>$firstname, ":surname"=>$surname);
			
			try {
				//prepare and execute the query
				$stmt		= $this->db->prepare($sql);
				$stmt->execute($values);
				
				//if it's found return the guid
				if ($stmt->rowCount() == 1) {
					$user = $stmt->fetch(PDO::FETCH_ASSOC);
					return $user['GUID'];
				} else {
					//if not found then return null
					return null;
				}
				
			} catch (PDOException $e) {
				//if not found then return null
				return null;
			}
		} else {
			//if there is nothing to find return null
			return null;
		}
	}
	
	private function doesUserExist($guid=null) {
		if ($guid == null) {
			return false;
		} else {
			$sql	= "SELECT `GUID` FROM `" . $this->table_name . "` WHERE `GUID` = :guid";
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
	
	//alias for setGuid($guid)
	public function loadUserWithGuid($guid) {
		$this->setGuid($guid);
	}
	
	public function loadUserWithName($firstname, $surname) {
		$guid = $this->findUserWithName($firstname, $surname);
		
		if ($guid != null) {
			$this->setField('guid', $guid);
			$this->retrieve();
		} else {
			throw new LightException(__CLASS__ . "Exception: User with name: " . $firstname . " " . $surname . " not found.");
		}
	}
}

?>