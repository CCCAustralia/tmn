<?php

include_once('../lib/cas/cas.php');
require_once("../lib/FirePHPCore/fb.php");

//initialise phpCAS if hasn't happened yet (is done here so that it isn't repeated everytime an object is created)
if ( !isset($_CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');
	$_CAS_CLIENT_CALLED = 1;
}

class Tmn {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	
	private $connection;
	private $db;
	private $dbi;
	private $db_name;
	private $db_server;
	private $db_username;
	private $db_password;
	
	private $filename;
	private $DEBUG;
	private $guid;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile) {
		
		$this->db_name		="mportal_tmn";
		$this->db_server	="localhost";
		$this->db_username	="mportal";
		$this->db_password	="***REMOVED***";
		
		$this->filename		= $logfile;
		$this->DEBUG		= 1;
		$this->guid			= null;
		$this->connection	= null;
		$this->db			= null;
		$this->dbi			= null;
		
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
		
		//connect to the tmn database
		$this->connectToDatabase();
	}
	
	
			///////////////////CONTROL FUNCTIONS/////////////////
	
	
	public function fail() {
		die('{success: false}');
	}
	
	public function failWithMsg($message) {
		$this->d($message);
		$this->fail();
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
	
	
			//////////////////DATABASE FUNCTIONS/////////////////
	
	
	public function connectToDatabaseOriginal() {
		
		if ($this->db == null) {
			$this->connection	= @mysql_connect($this->db_server, $this->db_username, $this->db_password)	or $this->failWithMsg(mysql_error());
			$this->db			= @mysql_select_db($this->db_name,$this->connection)						or $this->failWithMsg(mysql_error());
		} else {
			$this->d("Tmn Database Error: Tried to connect to database that is already connected.");
		}
		
	}
	
	public function connectToDatabase() {
		
		if ($this->dbi == null) {
			$this->dbi = new mysqli($this->db_server, $this->db_username, $this->db_password, $this->db_name);
		} else {
			$this->d("Tmn Database Error: Tried to connect to database that is already connected.");
		}
		
	}
	
	public function isSql($string) {
		if (strstr($string, ' ')) {
			return true;
		} else {
			return false;
		}
	}
	
	public function newStmt() {
		return $this->dbi->stmt_init();
	}
	
	public function query($sqlQuery) {
		if ($this->dbi != null) {
			return $this->dbi->query($sqlQuery);
		} else {
			return null;
		}
	}
	
	public function disconnectFromDatabaseOriginal() {
		if ($this->db != null) {
			mysql_close($this->connection);
			$this->connection	= null;
			$this->db			= null;
		} else {
			$this->d("Tmn Database Error: Tried to disconnect from a database that isn't yet connected.");
		}
	}
	
	public function disconnectFromDatabase() {
		if ($this->dbi != null) {
			mysqli_close($this->dbi);
			$this->dbi = null;
		} else {
			$this->d("Tmn Database Error: Tried to disconnect from a database that isn't yet connected.");
		}
	}
	
	
			///////////////////DEBUG FUNCTIONS/////////////////////
	
	
	
	public function getDebug() {
		return $this->debug;
	}
	
	public function setDebug($dbug) {
		$this->DEBUG	= $dbug;
	}
	
	public function d($message) {
		if($this->DEBUG) {
			fb($message);
		}
	}
	
	
	
			///////////////////LOGGING FUNCTIONS/////////////////////
	
	

	public function getFilename() {
		return $this->debug;
	}
	
	public function setFilename($fname) {
		$this->filename	= $fname;
	}
	
	//logs a message to the file with a timestamp
	public function logToFile($msg) { 
		// open file
		$fd = fopen($this->filename, "a");
		
		// append message to date/time
		$str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg;
		
		// write string
		fwrite($fd, $str . "\n");
		
		// output the logged string if debug mode
		$this->d($str);
		
		// close file
		fclose($fd);
	}
	
	//returns the logger's set path
	public function getLogPath() {
		return $this->filename;
	}
	
	//returns the contents of the log
	public function printLog() {
		//open file
		$fd = fopen($this->filename, "r");
		
		//read contents of file
		$filedata = fread($fd, filesize($this->filename));
		fclose($fd);
		$fd = fopen($this->filename, "a");
		fwrite($fd, "FILE READ");
		fclose($fd);
		
		//return the data
		return $filedata;
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		
	}
	
}

?>