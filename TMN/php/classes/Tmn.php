<?php

if (file_exists('../interfaces/TmnInterface.php')) {
	include_once('../interfaces/TmnInterface.php');
	include_once('../classes/Reporter.php');
	include_once('../classes/TmnCrudUser.php');
	include_once('../classes/TmnAuthenticator.php');
}
if (file_exists('php/interfaces/TmnInterface.php')) {
	include_once('php/interfaces/TmnInterface.php');
	include_once('php/classes/Reporter.php');
	include_once('php/classes/TmnCrudUser.php');
	include_once('php/classes/TmnAuthenticator.php');
}
if (file_exists('interfaces/TmnInterface.php')) {
	include_once('interfaces/TmnInterface.php');
	include_once('classes/Reporter.php');
	include_once('classes/TmnCrudUser.php');
	include_once('classes/TmnAuthenticator.php');
}
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
		TmnAuthenticator::authenticate();
	}
	
	public function logout() {
		$this->authenticator->logout();
	}
	
	public function isAuthenticated() {
		return $this->authenticator->isAuthenticated();
	}
	
	public function getAuthenticatedGuid() {
		return $this->authenticator->getGuid();
	}
	
	public function getEmail() {
		return $this->authenticator->getEmail();
	}
	
	public function getUser() {
		//if the user hasn't be created yet then make it
		if ($this->user == null) {
			$this->user = new TmnCrudUser($this->logfile, $this->getAuthenticatedGuid());
		}
		
		//return the user object
		return $this->user;
	}
	
	public function updateUserData() {
		//make sure we have a user to update
		if ($this->user == null) {
			$this->getUser();
		}
		
		$this->user->setField('email', $this->getEmail());
		
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>