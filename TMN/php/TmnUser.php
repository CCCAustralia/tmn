<?php

include_once('Tmn.php');

class TmnUser extends Tmn {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	
	protected $firstname;
	protected $surname;
	protected $spouse_guid;
	protected $ministry;
	protected $ft_pt_os;
	protected $days_per_week;
	protected $fan;
	protected $mpd;
	protected $mpd_guid;
	protected $admin_tab;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile) {
		
		parent::__construct($logfile);
		
		$this->loadUserWithGuid($this->getGuid());
	}
	
	
			///////////////////USER QUERY/////////////////////
	
	
	public function loadUserWithGuid($guid) {
		
		$userStmt	= $this->newStmt();
		$userStmt->prepare("SELECT `FIRSTNAME`, `SURNAME`, `SPOUSE_GUID`, `MINISTRY`, `FT_PT_OS`, `DAYS_PER_WEEK`, `FIN_ACC_NUM`, `MPD`, `M_GUID`, `ADMIN_TAB` FROM `User_Profiles` WHERE `GUID` = ?");
		$userStmt->bind_param('s', $guid);
		$userStmt->execute();
		
		if ($userStmt->num_rows == 1) {
			
			$userStmt->bind_result(
				$this->firstname,
				$this->surname,
				$this->spouse_guid,
				$this->ministry,
				$this->ft_pt_os,
				$this->days_per_week,
				$this->fan,
				$this->mpd,
				$this->mpd_guid,
				$this->admin_tab
			);
			
			$userStmt->fetch();
		
		} else {
			$this->failWithMsg("User Conflict: guid = " . $guid);
		}
		
		$userStmt->close();
	}
	
	public function updateUserWithGuid($guid) {
		
		$userStmt	= $this->newStmt();
		$userStmt->prepare("UPDATE `User_Profiles` SET `FIRSTNAME`=?, `SURNAME`=?, `SPOUSE_GUID`=?, `MINISTRY`=?, `FT_PT_OS`=?, `DAYS_PER_WEEK`=?, `FIN_ACC_NUM`=?, `MPD`=?, `M_GUID`=?, `ADMIN_TAB`=? WHERE `GUID` = ?");
		$userStmt->bind_param(
			'ssssiiiisis',
			$this->firstname,
			$this->surname,
			$this->spouse_guid,
			$this->ministry,
			$this->ft_pt_os,
			$this->days_per_week,
			$this->fan,
			$this->mpd,
			$this->mpd_guid,
			$this->admin_tab,
			$guid
		);
		
		$userStmt->execute();
		
		$userStmt->close();
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>