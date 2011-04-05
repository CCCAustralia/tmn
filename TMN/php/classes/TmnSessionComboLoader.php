<?php

include_once('../classes/TmnComboLoader.php');

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
			
	
		//overrides TmnComboLoader version
	public function produceJson() {
		
		try {
			
			$values	= array(":fan" => $this->user->getFan());
			
			//form the sql statement
			if ($this->aussie_form) {
					$sql	= "SELECT `SESSION_ID`, `SESSION_NAME`, `AUTH_SESSION_ID`, `DATE_MODIFIED` FROM `Tmn_Sessions` WHERE `FAN` = :fan AND `HOME_ASSIGNMENT_SESSION_ID` IS NULL AND `INTERNATIONAL_ASSIGNMENT_SESSION_ID` IS NULL";
			} elseif ($this->overseas_form) {
				if ($this->home_assignment) {
					$sql	= "SELECT `SESSION_ID`, `SESSION_NAME`, `AUTH_SESSION_ID`, `DATE_MODIFIED` FROM `Tmn_Sessions` WHERE `FAN` = :fan AND `HOME_ASSIGNMENT_SESSION_ID` IS NULL AND `INTERNATIONAL_ASSIGNMENT_SESSION_ID` IS NOT NULL";
				} else {
					$sql	= "SELECT `SESSION_ID`, `SESSION_NAME`, `AUTH_SESSION_ID`, `DATE_MODIFIED` FROM `Tmn_Sessions` WHERE `FAN` = :fan AND `HOME_ASSIGNMENT_SESSION_ID` IS NOT NULL AND `INTERNATIONAL_ASSIGNMENT_SESSION_ID` IS NULL";
				}
			} else {
				throw new FatalException('SessionComboLoader Exception: Form not Aussie or Overseas');
			}
			
			//execute the statement
			$stmt			= $this->db->prepare($sql);
			$stmt->execute($values);
			
			//grab the result as an array
			$table_array				= parent::arrayFromStmt($stmt);
			$array						= $table_array[$this->table];
			
			//change having an auth session id into a locked flag
			//go through each row
			for ($rowCount = 0; $rowCount < count($array); $rowCount++) {
				
				//create an empty row
				$row	= array();
				
				//reform the row with locked instead of auth_session_id
					foreach ($array[$rowCount] as $key=>$value) {
						
						//if the field is auth_session_id make a field in the new row called locked
						if ($key == 'AUTH_SESSION_ID') {
							//if auth_session_id is not set then the session is not locked
							if ($value == null) {
								$row['LOCKED'] = false;
							//if auth_session_id is set then lock the session
							} else {
								$row['LOCKED'] = true;
							}
						//if its a regular field just copy it into the new row
						} elseif ($key == 'DATE_MODIFIED') {
							if ($value == '2010-03-01 12:00:00') {
								$row['LOCKED'] = true;
							} else {
								$row['LOCKED'] = false;
							}
						} else {
							$row[$key] = $value;
						}
					}
					
				//put the row back into array
				$array[$rowCount]	= $row;
			}
			
			$table_array[$this->table]	= $array;
			
			return json_encode($table_array);
			
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