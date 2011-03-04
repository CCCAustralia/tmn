<?php

include_once('Tmn.php');

class TmnComboLoader extends Tmn {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	
	protected $table;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile, $tablename) {
		
		parent::__construct($logfile);
		
		if (!$this->validRequest($tablename)) {
				$this->failWithMsg("Not a valid Request");
		}
		
		$this->table	= $tablename;
	}
	
	
			///////////////////CHECK FUNCTIONS/////////////////
	
	
	private function validRequest($tablename) {
		if ($tablename == 'User_Profiles' || $tablename == 'Sessions' || $tablename == 'Authorising' || $this->isSql($tablename)) {
			return false;
		} else {
			return true;
		}
	}
	
	
			///////////////////LOADER FUNCTIONS/////////////////
	
	
	public function produceJson() {
		
		//form the sql statement
		$sql			= "SELECT * FROM ".$this->table;
		
		$j				= $this->jsonFromQuery($sql);
		
		return $j;
	}
	
	public function jsonFromQuery($sql) {
		
		$query	= $this->query($sql);
		
		//form the returned json with the sql result:
		//iterate through each returned row
		for ($i = 0; $i < $query->num_rows; $i++) {
			$r = $query->fetch_assoc();
			$returndata .= "{";
			//iterate through each field in the row
			foreach ($r as $k=>$v) {
				$returndata .= "\"".$k."\": \"".$r[$k]."\",";
			}
			$returndata = trim($returndata, ",");
			$returndata .= "},";
		}
		
		$query->free();
		
		//trim
		$returndata = trim($returndata,",");
		
		//return
		return '{	'.$this->table.':['.$returndata.'] }';
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>