<?php

include_once('../classes/Reporter.php');
include_once('../classes/TmnDatabase.php');

class TmnComboLoader extends Reporter {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	
	protected  	$db;
	protected	$table;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile, $tablename) {
		
		parent::__construct($logfile);
		
		$this->db	= TmnDatabase::getInstance($logfile);
		
		if (!$this->validRequest($tablename)) {
				throw new FatalException("ComboLoader Exception: Not a valid Request.");
		}
		
		$this->table	= $tablename;
	}
	
	
			///////////////////CHECK FUNCTIONS/////////////////
	
	
	private function validRequest($tablename) {
		if ($tablename == 'User_Profiles' || $tablename == 'Sessions' || $tablename == 'Authorising' || $this->db->isSql($tablename)) {
			return false;
		} else {
			return true;
		}
	}
	
	
			///////////////////LOADER FUNCTIONS/////////////////
	
	
	public function produceJson() {
		
		try {
			//form the sql statement
			$sql			= "SELECT * FROM " . $this->table;
			
			$stmt			= $this->db->query($sql);
			
			$j				= $this->jsonFromStmt($stmt);
			
			return $j;
		} catch (Exception $e) {
			throw new FatalException("ComboLoader Exception: Can't Load Table data due to error; " . $e->getMessage());
		}
	}
	
	public function jsonFromStmt($stmt) {
		
		$returndata = "";
		
		//form the returned json with the sql result:
		//iterate through each returned row
		for ($i = 0; $i < $stmt->rowCount(); $i++) {
			$r = $stmt->fetch(PDO::FETCH_ASSOC);
			$returndata .= "{";
			//iterate through each field in the row
			foreach ($r as $k=>$v) {
				$returndata .= "\"".$k."\": \"".$r[$k]."\",";
			}
			$returndata = trim($returndata, ",");
			$returndata .= "},";
		}
		
		//trim
		$returndata = trim($returndata,",");
		
		//return
		return '{'.$this->table.':['.$returndata.']}';
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>