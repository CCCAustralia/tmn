<?php

include_once('../interfaces/TmnInterface.php');

include_once('../classes/Reporter.php');
include_once('../classes/TmnCrudUser.php');
include_once('../classes/TmnAuthenticator.php');

class Tmn extends Reporter implements TmnInterface {
	
	
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
	
	public function authenticate() {
		$this->authenticator->authenticate();
	}
	
	public function logout() {
		$this->authenticator->logout();
	}
	
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
		//if the user hasn't be created yet then make it
		if ($this->user == null) {
			$this->user = TmnCrudUser::make($this->logfile, $this->getAuthenticatedGuid());
		}
		
		//return the user object
		return $this->user;
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>