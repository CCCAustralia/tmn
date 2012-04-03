<?php
if(file_exists('../interfaces/TmnUsersGroupInterface.php')) {
	include_once('../classes/TmnDatabase.php');
	include_once('../interfaces/TmnUsersGroupInterface.php');
}
if(file_exists('interfaces/TmnUsersGroupInterface.php')) {
	include_once('classes/TmnDatabase.php');
	include_once('interfaces/TmnUsersGroupInterface.php');
}
if(file_exists('php/interfaces/TmnUsersGroupInterface.php')) {
	include_once('php/classes/TmnDatabase.php');
	include_once('php/interfaces/TmnUsersGroupInterface.php');
}

class TmnUsersGroup implements TmnUsersGroupInterface {
	
	protected 	$db					= null;
	protected	$userArray			= array();
	protected	$tableName			= "";
	protected	$positionFieldName	= "";
	protected	$position			= "";
	
	public function __construct($table_name, $position_field_name, $position) {
		
		$this->tableName			= $table_name;
		$this->positionFieldName	= $position_field_name;
		
		try {
			
			$this->loadUsersWithPosition($position);
			
		} catch (Exception $e) {
			throw new FatalException(__CLASS__ . " Exception: " . $e->getMessage());
		}
		
		try {
			//grab an instance of the TmnDatabase
			$this->db	= TmnDatabase::getInstance($logfile);
			
		} catch (LightException $e) {
			//if there is a problem with the Database kill the object
			throw new FatalException(__CLASS__ . " Exception: Couldn't Connect to Database due to error; " . $e->getMessage());
		}
		
	}
	
	
			///////////////////ACCESSOR FUNCTIONS/////////////////////
	
	
	public function loadUsersWithPosition($position) {
		
		$this->position	= $position;
		
		$positionSql	= "SELECT User_Profiles.* FROM " . $this->tableName . " LEFT JOIN User_Profiles ON " . $this->tableName . ".GUID = User_Profiles.GUID WHERE " . $this->tableName . "." . $this->positionFieldName . " = :position";
		$values			= array(":position" => $this->position);
		$stmt 			= $this->db->prepare($positionSql);
		$stmt->execute($values);
		$positionResult	= $stmt->fetchAll(PDO::FETCH_ASSOC);
		$users			= array();
		
		foreach ($positionResult as $key => $row) {
			
			$users[$row["GUID"]] = $row;
			
		}
		
		$this->userArray	= $users;
		
	}
	
	public function getArrayOfUsers() {
		return $this->adminArray;
	}
	
	public function containsUser($guid) {
		
		if ( !is_null( $this->userArray[$guid] ) ) {
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
	public function getEmailsAsString() {
		
		$emails	= "";
		
		foreach ($this->userArray as $guid => $user) {
			
			$emails	.= $user['EMAIL'] . ",";
			
		}
		
		return trim($emails, ",");
		
	}

}

?>