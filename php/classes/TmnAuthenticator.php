<?php
if (file_exists('../interfaces/TmnAuthenticatorInterface.php')) {
	include_once('../interfaces/TmnAuthenticatorInterface.php');
	include_once('../classes/Reporter.php');
	include_once('../../lib/cas/cas.php');
}
if (file_exists('interfaces/TmnAuthenticatorInterface.php')) {
	include_once('interfaces/TmnAuthenticatorInterface.php');
	include_once('classes/Reporter.php');
	include_once('../lib/cas/cas.php');
}
if (file_exists('php/interfaces/TmnAuthenticatorInterface.php')) {
	include_once('php/interfaces/TmnAuthenticatorInterface.php');
	include_once('php/classes/Reporter.php');
	include_once('lib/cas/cas.php');
}

class TmnAuthenticator extends Reporter implements TmnAuthenticatorInterface {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	// Hold an instance of the class
    private static $instance;
	
    private $guid;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	protected function __construct($logfile) {
		parent::__construct($logfile);
		
		$this->guid			= null;
		
		//initialise phpCAS if hasn't happened yet (is done here so that it isn't repeated everytime an object is created)
		if ( !isset($GLOBALS['CAS_CLIENT_CALLED']) ) {
			phpCAS::client(CAS_VERSION_2_0,'thekey.me',443,'cas');
			$GLOBALS['CAS_CLIENT_CALLED'] = 1;
		}
		
		//check if the user has been authenticated via the Key using phpCAS
		if (!phpCAS::isAuthenticated()) { //if your not logged into gcx quit
			throw new FatalException('Authentication Exception: User Not Authenticated');
		}
		
		//grab user's guid if its available
		if (isset($_SESSION['phpCAS'])) {
			$xmlstr			= str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
			$xmlobject		= new SimpleXmlElement($xmlstr);
			$this->guid		= (string) $xmlobject->authenticationSuccess->attributes->ssoGuid;
			//$this->logToFile("User Authenticated: guid = " . substr($this->guid, 0, -12) . "************");
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

        self::$instance->setLogfile($logfile);
        return self::$instance;
    }
    
    // Prevent users to clone the instance
    public function __clone()
    {
        throw new LightException("Authentication Exception: TmnAuthenticator Cannot be cloned");
    }
	
    
			////////////////AUTHENTICATION FUNCTIONS//////////////
	
	
	//GCX login
	public function authenticate() {

    	//include the CAS module if it's not already there
		//include_once('../../lib/cas/cas.php');
		
		//initialise phpCAS if hasn't happened yet (is done here so that it isn't repeated everytime an object is created)
		if ( !isset($GLOBALS['CAS_CLIENT_CALLED']) ) {
			phpCAS::client(CAS_VERSION_2_0,'thekey.me',443,'cas');
			$GLOBALS['CAS_CLIENT_CALLED'] = 1;
		}
		
		phpCAS::setNoCasServerValidation();	//no SSL validation for the CAS server
		phpCAS::forceAuthentication();		//require the user to log in to CAS
		
		if (isset($_REQUEST['logout'])) {
			phpCAS::logout();
		}
		
		//user is now authenticated by the CAS server and the user's login name can be read with phpCAS::getUser()
		
		//fetch a ticket if absent
		if ($_REQUEST['ticket'] == '' && $_REQUEST['id'] == '')
		{
		    header("Location: https://thekey.me/cas/login?service=". self::curPageURL());
		}
    }
    
    //constructs the url of this file based on the server settings found in $_SERVER
	public function curPageURL() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	
	public function logout() {
		phpCAS::logout();
	}
    
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