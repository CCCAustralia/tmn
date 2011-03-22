<?php

include_once('../classes/Reporter.php');
include_once('../classes/TmnUser.php');
include_once('../classes/TmnAuthenticator.php');

class Tmn extends Reporter {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	
	private $authenticator;
	private $guid;
	private $user;
	private $logfile;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile) {
		
		parent::__construct($logfile);
		
		$this->authenticator= TmnAuthenticator::getInstance($logfile);
		$this->user			= null;
		$this->logfile		= $logfile;
	}
	
	
			////////////////AUTHENTICATION FUNCTIONS//////////////
	
	
	public function isAuthenticated() {
		return $this->authenticator->isAuthenticated();
	}
	
	public function getAuthenticatedGuid() {
		//return "691EC152-0565-CEF4-B5D8-99286252652B";
		return $this->authenticator->getGuid();
	}
	
	public function getEmail() {
		return $this->authenticator->getEmail();
	}
	
	public function getUser() {
		if ($this->user == null) {
			$this->user = new TmnUser($this->logfile, $this->getAuthenticatedGuid());
		}
		
		return $this->user;
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>