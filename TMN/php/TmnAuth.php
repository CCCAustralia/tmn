<?php

include_once('Reporter.php');
include_once('../lib/cas/cas.php');
require_once("../lib/FirePHPCore/fb.php");

//initialise phpCAS if hasn't happened yet (is done here so that it isn't repeated everytime an object is created)
if ( !isset($_CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');
	$_CAS_CLIENT_CALLED = 1;
}

class TmnAuth extends Reporter {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	// Hold an instance of the class
    private static $instance;
	
    private $guid;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	private function __construct($logfile) {
		
		$this->guid			= null;
		
		//check if the user has been authenticated via the Key using phpCAS
		if (!phpCAS::isAuthenticated()) { //if your not logged into gcx quit
			$this->failWithMsg('Auth failed');
		}
		
		//grab user's guid if its available
		if (isset($_SESSION['phpCAS'])) {
			$xmlstr			= str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
			$xmlobject		= new SimpleXmlElement($xmlstr);
			$this->guid		= $xmlobject->authenticationSuccess->attributes->ssoGuid;
		} else {
			$this->failWithMsg('No Guid');
		}
	}
	
	
			///////////////////CONTROL FUNCTIONS/////////////////
	
    // The singleton method
    public static function getInstance($logfile) 
    {
        if (!isset(self::$instance)) {
            self::$instance = new TmnAuth($logfile);
        }

        return self::$instance;
    }
    
    // Prevent users to clone the instance
    public function __clone()
    {
        $this->d("Authenticator Error: Clone not allowed");
    }
	
    
			////////////////AUTHENTICATION FUNCTIONS//////////////
	
	
	public function isAuthenticated() {
		return phpCAS::isAuthenticated();
	}
	
	public function getGuid() {
		return $this->guid;
	}
	
	public function getEmail() {
		return phpCAS::getUser();
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>