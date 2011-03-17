<?php

include_once('Reporter.php');
include_once('../lib/cas/cas.php');

//initialise phpCAS if hasn't happened yet (is done here so that it isn't repeated everytime an object is created)
if ( !isset($_CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');
	$_CAS_CLIENT_CALLED = 1;
}

class TmnAuthenticator extends Reporter {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	// Hold an instance of the class
    private static $instance;
	
    private $guid;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	protected function __construct($logfile) {
		parent::__construct($logfile);
		
		$this->guid			= null;
		
		//check if the user has been authenticated via the Key using phpCAS
		if (!phpCAS::isAuthenticated()) { //if your not logged into gcx quit
			throw new FatalException('Authentication Exception: User Not Authenticated');
		}
		
		//grab user's guid if its available
		if (isset($_SESSION['phpCAS'])) {
			$xmlstr			= str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
			$xmlobject		= new SimpleXmlElement($xmlstr);
			$this->guid		= $xmlobject->authenticationSuccess->attributes->ssoGuid;
			$this->logToFile("User Authenticated: guid = " . substr($this->guid, 0, -12) . "************");
		} else {
			throw new FatalException("Authentication Exception: User's GUID Not Found");
		}
	}
	

			///////////////////CONTROL FUNCTIONS/////////////////
	
    // The singleton method
    public static function getInstance($logfile) {
        if (!isset(self::$instance)) {
            self::$instance = new TmnAuthenticator($logfile);
        }

        self::$instance->setFilename($logfile);
        return self::$instance;
    }
    
    // Prevent users to clone the instance
    public function __clone()
    {
        throw new LightException("Authentication Exception: TmnAuthenticator Cannot be cloned");
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