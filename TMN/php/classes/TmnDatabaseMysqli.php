<?php

include_once('../classes/Reporter.php');

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
	
	
	protected function __construct($logfile) {
		
		$this->connection	= null;
		$this->db			= null;
		$this->dbi			= null;
		
		try {
			$this->connectToDatabase();
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
	
	
    //connects to database using the original mysql functions (suseptable to sql injection)
	public function connectToDatabaseOriginal() {
		
		if ($this->db == null) {
			$this->connection	= @mysql_connect(self::$db_server, self::$db_username, self::$db_password);
			if (!$this->connection) {
				$this->connection	= null;
				throw new FatalException('Database Exception: Cannot Connect; Error no.: ' . mysql_error());
			}
			$this->db			= @mysql_select_db(self::$db_name,$this->connection);
			if (!$this->db) {
				$this->db	= null;
				throw new FatalException('Database Exception: Cannot Connect; Error no.: ' . mysql_error());
			}
		} else {
			throw new LightException("Database Exception: Tried to connect to database that is already connected.");
		}
		
	}
	
	//connects to database using the mysql improved class (can do prepared sql statements)
	public function connectToDatabase() {
		
		if ($this->dbi == null) {
			$this->dbi = new mysqli(self::$db_server, self::$db_username, self::$db_password, self::$db_name);
			$this->d($this->dbi);
			if(mysqli_connect_errno()) {
				throw new FatalException('Database Exception: Cannot Connect; Error no.: ' . mysqli_connect_errno());
			}
		} else {
			throw new LightException("Database Exception: Tried to connect to database that is already connected.");
		}
		
	}
	
	//very primative check to see if a parameter includes sql (if is has spaces in it)
	public function isSql($string) {
		if (strstr($string, ' ')) {
			return true;
		} else {
			return false;
		}
	}
	
	//returns a statment object so that someone could create a custom prepared statment to be run on this database
	public function newStmt() {
		return $this->dbi->stmt_init();
	}
	
	//run a non-prepared query on the database
	public function query($sqlQuery) {
		if ($this->dbi != null) {
			return $this->dbi->query($sqlQuery);
		} else {
			return null;
		}
	}
	
	//takes an array $arr and returns an array of references to the $arr elements ie $refs[0] = &$arr[0]
	public function refValues($arr){
		
		if (strnatcmp(phpversion(),'5.3') >= 0) {//Reference is required for PHP 5.3+
			$refs = array();
			
			foreach($arr as $key => $value) {
				$refs[$key] = &$arr[$key];
			}
			
			return $refs;
		}
		
		return $arr;
	}
	
	
	//takes a prepared sql statement, an array of values, a string of the types for the first array, a string of the types of the returned parameters
	//and uses them to execute a prepared select statement
	public function preparedSelect($sql, $values, $types, $resultTypes) {
		if ($this->dbi != null) {
			
			$results = array();
			if (!is_array($values))			{$values = array($values);}
			if (!is_array($types))			{$types = array($types);}
			for ($resultCount=0; $resultCount < strlen($resultTypes); $resultCount++) {
				$results[$resultCount] = null;
			}
			
			$queryStmt	= $this->dbi->prepare($sql);
			call_user_func_array(array($queryStmt, "bind_param"), array_merge($types, $this->refValues($values)));
			$queryStmt->execute();
			call_user_func_array(array($queryStmt, "bind_result"), $this->refValues($results));
			$queryStmt->fetch();
			$queryStmt->close();
			
			return $results;
		} else {
			return null;
		}
	}
	
	//takes a prepared sql statement, an array of values, a string of the types for the first array
	//and uses them to execute a prepared sql statement
	public function preparedQuery($sql, $values, $types) {
		if ($this->dbi != null) {
			
			if (!is_array($values)) {
				$values = array($values);
			}
			
			if (!is_array($types)) {
				$types = array($types);
			}
			
			$queryStmt	= $this->dbi->prepare($sql);
			call_user_func_array(array($queryStmt, "bind_param"), array_merge($types, $this->refValues($values)));
			$queryStmt->execute();
			$create_id	= $queryStmt->insert_id;
			$queryStmt->close();
			
			return $create_id;
		} else {
			return null;
		}
	}
	
	//disconnect standard mysql database connection if it exists
	public function disconnectFromDatabaseOriginal() {
		if ($this->db != null) {
			mysql_close($this->connection);
			$this->connection	= null;
			$this->db			= null;
		} else {
			throw new LightException("TmnDatabase Error: Tried to disconnect from a database that isn't yet connected.");
		}
	}
	
	//disconnect improved mysql database connection if it exists
	public function disconnectFromDatabase() {
		if ($this->dbi != null) {
			mysqli_close($this->dbi);
			$this->dbi = null;
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