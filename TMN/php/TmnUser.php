<?php

include_once('Tmn.php');

class TmnUser extends Tmn {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	private static $table_name = "`User_Profiles`";
	protected $user = array(
			firstname		=>	null,
			surname			=>	null,
			spouse_guid		=>	null,
			ministry		=>	null,
			ft_pt_os		=>	null,
			days_per_week	=>	null,
			fan				=>	null,
			mpd				=>	null,
			mpd_guid		=>	null,
			admin_tab		=>	null
	);
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile, $guid) {
		
		parent::__construct($logfile);
		
		if (isset($guid)) {
			$this->setGuid($guid);
		}
		
		$this->loadUserWithGuid($this->getGuid());
	}
	
	
			////////////////ACCESSOR FUNCTIONS////////////////
	
	
	public function getFan() {
		return $this->user['fan'];
	}
	
	public function getSpouseGuid() {
		return $this->user['spouse_guid'];
	}
	
	public function setSpouseGuid($guid) {
		$this->user['spouse_guid'] = $guid;
	}
	
	public function getMpdGuid() {
		return $this->user['mpd_guid'];
	}
	
	public function isAdmin() {
		if ($this->user['admin_tab'] == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	public function resetUser() {
		foreach ($this->user as $key=>$value) {
			$this->user[$key] = null;
		}
	}
	
	
			///////////////////USER QUERY/////////////////////
	
	
	public function loadUserWithGuid($guid) {
		
		$userStmt	= $this->newStmt();
		$userStmt->prepare("SELECT `FIRSTNAME`, `SURNAME`, `SPOUSE_GUID`, `MINISTRY`, `FT_PT_OS`, `DAYS_PER_WEEK`, `FIN_ACC_NUM`, `MPD`, `M_GUID`, `ADMIN_TAB` FROM " . $this->table_name . " WHERE `GUID` = ?");
		$userStmt->bind_param('s', $guid);
		$userStmt->execute();
		
		$userStmt->bind_result(
			$this->user['firstname'],
			$this->user['surname'],
			$this->user['spouse_guid'],
			$this->user['ministry'],
			$this->user['ft_pt_os'],
			$this->user['days_per_week'],
			$this->user['fan'],
			$this->user['mpd'],
			$this->user['mpd_guid'],
			$this->user['admin_tab']
		);
		
		$userStmt->fetch();
		
		if (mysqli_connect_errno()) {
			$this->d("Database Error: " . mysqli_connect_errno());
			$this->resetUser();
		}
		
		$userStmt->close();
	}
	
	public function updateUserWithGuid($guid) {
		
		$userStmt	= $this->newStmt();
		$userStmt->prepare("UPDATE " . $this->table_name . " SET `FIRSTNAME`=?, `SURNAME`=?, `SPOUSE_GUID`=?, `MINISTRY`=?, `FT_PT_OS`=?, `DAYS_PER_WEEK`=?, `FIN_ACC_NUM`=?, `MPD`=?, `M_GUID`=?, `ADMIN_TAB`=? WHERE `GUID` = ?");
		$userStmt->bind_param(
			'ssssiiiisis',
			$this->user['firstname'],
			$this->user['surname'],
			$this->user['spouse_guid'],
			$this->user['ministry'],
			$this->user['ft_pt_os'],
			$this->user['days_per_week'],
			$this->user['fan'],
			$this->user['mpd'],
			$this->user['mpd_guid'],
			$this->user['admin_tab'],
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