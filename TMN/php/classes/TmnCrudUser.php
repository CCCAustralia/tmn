<?php

include_once('../classes/TmnCrud.php');

//This is an example of how to subclass TmnCrud
class TmnCrudUser extends TmnCrud {
	
	public function __construct($logfile, $tablename=null, $primarykey=null, $privatetypes=null, $publictypes=null) {
		
		parent::__construct(
			$logfile,						//path of logfile
			"User_Profiles",				//name of table
			"guid",							//name of table's primary key
			array(							//an assoc array of private field names and there types
				'guid'		=>	"s"
			),
			array(							//an assoc array of public field names and there types
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
			)
		);
	}
	
	public function getGuid() {
		return $this->getField('guid');
	}
	
	public function setGuid($guid) {
		
		$tempGuid = $this->guid;
		$this->setField('guid', $guid);
		
		try {
			$this->retrieve();
		} catch (LightException $e) {
			$this->setField('guid', $tempGuid);
			$this->exceptionHandler(new LightException("User Exception: Cannot Load User with guid=" . $guid . ". The previous guid was restored. The following Exception was thrown when load was attempted:" . $e->getMessage()));
		}
	}
	
	public function getFan() {
		return $this->getField('fin_acc_num');
	}
	
	public function getSpouse() {
		if ($this->spouse == null) {
			$this->spouse = new TmnUser($this->logfile, $this->getSpouseGuid());
		}
		
		return $this->spouse;
	}
	
	public function getSpouseGuid() {
		return $this->getField('spouse_guid');
	}
	
	public function setSpouseGuid($guid) {
		if ($this->doesUserExist($guid)) {
			$this->setField('spouse_guid', $guid);
		} else {
			throw new LightException("User Exception: Spouse couldn't be found.");
		}
	}
	
	public function getMpdGuid() {
		return $this->getField('m_guid');
	}
	
	public function setMpdGuid($guid) {
		if ($this->doesUserExist($guid)) {
			$this->setField('m_guid', $guid);
		} else {
			throw new LightException("User Exception: MDP Supervisor couldn't be found.");
		}
	}
	
	public function isAdmin() {
		if ($this->getField('admin_tab') == 1) {
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
	
	//alias for setGuid($guid)
	public function loadUserWithGuid($guid) {
		$this->setGuid($guid);
	}
}

?>