<?php

include_once('Reporter.php');
include_once('TmnAuth.php');
include_once('TmnDatabase.php');

class Tmn extends Reporter {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	
	protected $db;
	private $auth;
	private $guid;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile) {
		
		parent::__construct($logfile);
		
		$this->db			= TmnDatabase::getInstance($logfile);
		$this->auth			= TmnAuth::getInstance($logfile);
		$this->guid			= $this->auth->getGuid();
	}
	
	
			////////////////AUTHENTICATION FUNCTIONS//////////////
	
	
	public function isAuthenticated() {
		return $this->auth->isAuthenticated();
	}
	
	public function getAuthGuid() {
		//return "691EC152-0565-CEF4-B5D8-99286252652B";
		return $this->auth->getGuid();
	}
	
	public function getGuid() {
		return $this->guid;
	}
	
	public function setGuid($g) {
		$this->guid = $g;
	}
	
	public function getEmail() {
		return $this->auth->getEmail();
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>