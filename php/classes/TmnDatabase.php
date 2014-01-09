<?php
if (file_exists('../interfaces/TmnDatabaseInterface.php')) {
	include_once('../interfaces/TmnDatabaseInterface.php');
	include_once('../classes/Reporter.php');
}
if (file_exists('interfaces/TmnDatabaseInterface.php')) {
	include_once('interfaces/TmnDatabaseInterface.php');
	include_once('classes/Reporter.php');
}
if (file_exists('php/interfaces/TmnDatabaseInterface.php')) {
	include_once('php/interfaces/TmnDatabaseInterface.php');
	include_once('php/classes/Reporter.php');
}
class TmnDatabase extends Reporter implements TmnDatabaseInterface {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	// Hold an instance of the class
    private static $instance;
	
	private $db;
	private static $db_name;
	private static $db_server;
	private static $db_username;
	private static $db_password;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	protected function __construct($logfile) {
		
		$this->db			= null;

        $configString   = "";

        if (file_exists('config.json')) {
            $configString = file_get_contents("config.json");
        } elseif (file_exists(file_exists('../config.json'))) {
            $configString = file_get_contents("../config.json");
        } elseif (file_exists(file_exists('../../config.json'))) {
            $configString = file_get_contents("../../config.json");
        } else {
            $configString = file_get_contents("../../../config.json");
        }

        $config = json_decode($configString,true);

        self::$db_name       = $config["db_name"];
        self::$db_server     = $config["db_server"];
        self::$db_username   = $config["db_username"];
        self::$db_password   = $config["db_password"];
		
		try {
			$this->connect();
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	
			///////////////////CONTROL FUNCTIONS/////////////////
	
	
    // The singleton method
    public static function getInstance($logfile) 
    {
        if (!isset(self::$instance)) {
            self::$instance = new TmnDatabase($logfile);
        }

        return self::$instance;
    }
    
    // Prevent users to clone the instance
    public function __clone()
    {
        throw new LightException("Database Exception: TmnDatabase Cannot be cloned");
    }
	
	
			//////////////////DATABASE FUNCTIONS/////////////////
	
	
	//connects to database using mysql via the PDO wrapper (can do prepared sql statements)
	public function connect() {
		
		if ($this->db == null) {
			try {
				$this->db = new PDO("mysql:host=" . self::$db_server . ";dbname=" . self::$db_name, self::$db_username, self::$db_password);
				$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
			catch(PDOException $e)
			{
				throw new FatalException("Database Exception: " . $e->getMessage());
			}
		} else {
			throw new LightException("Database Exception: Tried to connect to database that is already connected.");
		}
		
	}
	
	//calls beginTransaction on the database connection
	public function beginTransaction() {
		return $this->db->beginTransaction();
	}
	
	//calls rollback on the database connection
	public function rollback() {
		return $this->db->rollBack();
	}
	
	//calls commit on the database connection
	public function commit() {
		return $this->db->commit();
	}
	
	//returns a statment object so that someone could create a custom prepared statment to be run on this database
	public function prepare($sqlQuery) {
		if ($this->db != null) {
			return $this->db->prepare($sqlQuery);
		} else {
			return null;
		}
	}
	
	//run a non-prepared query on the database (only use for queries that return 1 result)
	public function exec($sqlQuery) {
		if ($this->db != null) {
			return $this->db->exec($sqlQuery);
		} else {
			return null;
		}
	}
	
	//run a non-prepared query on the database  (can return a set of results)
	public function query($sqlQuery) {
		if ($this->db != null) {
			return $this->db->query($sqlQuery);
		} else {
			return null;
		}
	}
	
	//returns a bool to tell you if the string you passed it is sql or not
	public function isSql($sqlString) {
		if (!strstr($sqlString, ' ')) {
			return false;
		} else {
			return true;
		}
	}
	
	//returns the id of the last inserted row
	public function lastInsertId() {
		return $this->db->lastInsertId();
	}
	
	//disconnect from database by destroying PDO object connection if it exists
	public function disconnect() {
		if ($this->db != null) {
			$this->db = null;
		} else {
			throw new LightException("TmnDatabase Error: Tried to disconnect from a database that isn't yet connected.");
		}
	}

	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>