<?php

include_once('TmnComboLoader.php');

class TmnSessionComboLoader extends TmnComboLoader {
	
	
			////////////////INSTANCE VARIABLES//////////////////
	
	
	private $user;
	private $aussie_form;
	private $overseas_form;
	private $home_assignment;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile, $user, $tablename, $aussie_form, $overseas_form, $home_assignment) {
		parent::__construct($logfile, $tablename);
		
		$this->user				= $user;
		$this->aussie_form		= $aussie_form;
		$this->overseas_form	= $overseas_form;
		$this->home_assignment	= $home_assignment;
	}
	
	
			///////////////////LOADER FUNCTIONS/////////////////
			
	
	public function getUserFan() {
		$sql 		= "SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID = :guid";
		$values		= array(":guid" => $this->user->getGuid());
		
		try {
			$userQuery = $this->db->prepare($sql);
			$userQuery->execute($values);
			
			if ($userQuery->rowCount() == 1) {
				$userrow = $userQuery->fetch(PDO::FETCH_ASSOC);
				return $userrow['FIN_ACC_NUM'];
			} else {
				throw new FatalException("User Conflict.");
			}
			
		} catch (Exception $e) {
			throw new FatalException("SessionComboLoader Exception: Can't find User due to error; " . $e->getMessage());
		}
		
	}	
	
		//overrides TmnComboLoader version
	public function produceJson() {
		
		try {
			
			$values	= array(":fan" => $this->user->getFan());
			
			//form the sql statement
			if ($this->aussie_form) {
					$sql	= "SELECT `SESSION_ID`, `SESSION_NAME` FROM `Tmn_Sessions` WHERE `FAN` = :fan AND `HOME_ASSIGNMENT_SESSION_ID` IS NULL AND `INTERNATIONAL_ASSIGNMENT_SESSION_ID` IS NULL";
			} elseif ($this->overseas_form) {
				if ($this->home_assignment) {
					$sql	= "SELECT `SESSION_ID`, `SESSION_NAME` FROM `Tmn_Sessions` WHERE `FAN` = :fan AND `HOME_ASSIGNMENT_SESSION_ID` IS NULL AND `INTERNATIONAL_ASSIGNMENT_SESSION_ID` IS NOT NULL";
				} else {
					$sql	= "SELECT `SESSION_ID`, `SESSION_NAME` FROM `Tmn_Sessions` WHERE `FAN` = :fan AND `HOME_ASSIGNMENT_SESSION_ID` IS NOT NULL AND `INTERNATIONAL_ASSIGNMENT_SESSION_ID` IS NULL";
				}
			} else {
				throw new FatalException('SessionComboLoader Exception: Form not Aussie or Overseas');
			}
			
			$stmt			= $this->db->prepare($sql);
			$stmt->execute($values);
			
			$j				= parent::jsonFromStmt($stmt);
			
			return $j;
			
		} catch (Exception $e) {
			throw new FatalException($e->getMessage());
		}
	}
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>