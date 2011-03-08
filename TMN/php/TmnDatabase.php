<?php

include_once('Reporter.php');
require_once("../lib/FirePHPCore/fb.php");

class TmnDatabase extends Reporter {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	// Hold an instance of the class
    private static $instance;
	
	private $connection;
	private $db;
	private $dbi;
	private static $db_name		= "mportal_tmn";
	private static $db_server	= "localhost";
	private static $db_username	= "mportal";
	private static $db_password	= "***REMOVED***";
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	private function __construct($logfile) {
		
		$this->connection	= null;
		$this->db			= null;
		$this->dbi			= null;
		
		$this->connectToDatabase();
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
        $this->d("Database Error: Clone not allowed");
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
			$this->d($this->dbi);
			if(mysqli_connect_errno()) {
				$this->failWithError('Connection Error: ' . mysqli_connect_errno());
			}
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
	
	public function preparedSelect($sql, $values, $types, $resultTypes) {
		if ($this->dbi != null) {
			
			$results = array(strlen($resultTypes));
			
			$queryStmt	= $this->dbi->prepare($sql); 
			call_user_func_array(array($queryStmt, "bind_param"), array_merge($types, $values));
			//call_user_func_array('mysqli_stmt_bind_param', array_merge (array($queryStmt, $type), $param);
			$queryStmt->execute();
			call_user_func_array(array($queryStmt, "bind_result"), array_merge($resultTypes, $results));
			$queryStmt->fetch();
			$queryStmt->close();
			
			return $results;
		} else {
			return null;
		}
	}
	
	public function preparedQuery($sql, $values, $types) {
		if ($this->dbi != null) {
			
			$queryStmt	= $this->dbi->prepare($sql); 
			call_user_func_array(array($queryStmt, "bind_param"), array_merge($types, $values));
			//call_user_func_array('mysqli_stmt_bind_param', array_merge (array($queryStmt, $type), $param); 
			$queryStmt->execute();
			$create_id	= $queryStmt->insert_id;
			$queryStmt->close();
			
			return $create_id;
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

	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>