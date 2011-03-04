<?php

include_once('TmnComboLoader.php');

class TmnSessionComboLoader extends TmnComboLoader {
	
	
			////////////////INSTANCE VARIABLES//////////////////
	
	
	private $aussie_form;
	private $overseas_form;
	private $home_assignment;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile, $tablename, $aussie_form, $overseas_form, $home_assignment) {
		parent::__construct($logfile, $tablename);
		
		$this->aussie_form		= $aussie_form;
		$this->overseas_form	= $overseas_form;
		$this->home_assignment	= $home_assignment;
	}
	
	
			///////////////////LOADER FUNCTIONS/////////////////
			
	
	public function getUserFan() {
		$sql = "SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID = '". $this->getGuid() ."'";
		$userQuery = $this->query($sql);
		
		if ($userQuery->num_rows == 1) {
			$userrow = $userQuery->fetch_assoc();
			return $userrow['FIN_ACC_NUM'];
		} else {
			$this->failWithMsg('User Conflict');
		}
	}	
	
		//overrides TmnComboLoader version
	public function produceJson() {
		
		$fan = $this->getUserFan();
			
		//form the sql statement
		if ($this->aussie_form) {
				$sql	= "SELECT SESSION_ID, DATE_MODIFIED FROM Tmn_Sessions WHERE FAN = ". $fan . " AND HOME_ASSIGNMENT_SESSION_ID = NULL AND INTERNATIONAL_ASSIGNMENT_SESSION_ID = NULL";
		} elseif ($this->overseas_form) {
			if ($this->home_assignment) {
				$sql	= "SELECT SESSION_ID, DATE_MODIFIED FROM Tmn_Sessions WHERE FAN = ". $fan . " AND HOME_ASSIGNMENT_SESSION_ID = NULL AND INTERNATIONAL_ASSIGNMENT_SESSION_ID != NULL";
			} else {
				$sql	= "SELECT SESSION_ID, DATE_MODIFIED FROM Tmn_Sessions WHERE FAN = ". $fan . " AND HOME_ASSIGNMENT_SESSION_ID != NULL AND INTERNATIONAL_ASSIGNMENT_SESSION_ID = NULL";
			}
		} else {
			$this->failWithMsg('Form not Aussie or Overseas');
		}
		
		$j				= parent::jsonFromQuery($sql);
		
		return $j;
	}
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>