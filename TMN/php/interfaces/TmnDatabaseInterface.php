<?php

interface TmnDatabaseInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
			
	
	/**
	 * 
	 * This class is responsable for managing database interaction. It is a wrapper around PDO so
	 * if you change your database to something other than mysql it will be easy to change in the code.
	 * It also inherits from Reporter so have a look at ReporterInterface.php more methods
	 * that are available to this class.
	 * 
	 * Note: - This class is a singleton, please use getInstance($logfile) to create it.
	 * 
	 * @param String $logfile - path of the file you want log statements to be ouputed to.
	 * 
	 * Note: Will throw FatalException if it can't complete this task.
	 */
	public static function getInstance($logfile);
	
	
			//////////////////DATABASE FUNCTIONS/////////////////
	
	
	/**
	 * Connects to database using mysql via the PDO wrapper (can do prepared sql statements)
	 */
	public function connect();
	
	/**
	 * Will take an sql string (using any of the PDO prepared statement formats)
	 * and will create a PDO statement object.
	 * @param string $sqlQuery
	 * 
	 * @return PDOStatement
	 * 
	 * Note: will throw exception if it can't complete this task.
	 */
	public function prepare($sqlQuery);
	
	/**
	 * Runs a non-prepared query on the database (only use for queries that return 1 result).
	 * Note: Is just a wrapper for PDO::exec
	 * 
	 * @param string $sqlQuery
	 * 
	 * @return int - number of rows affected
	 * 
	 * Note: will throw exception if it can't complete this task.
	 */
	public function exec($sqlQuery);
	
	/**
	 * Runs a non-prepared query on the database (can return a set of results).
	 * Note: Is just a wrapper for PDO::query($query)
	 * 
	 * @param string $sqlQuery
	 * 
	 * @return PDOStatement
	 * 
	 * Note: will throw exception if it can't complete this task.
	 */
	public function query($sqlQuery);
	
	//returns a bool to tell you if the string you passed it is sql or not
	/**
	 * Returns a bool to tell you if the string you passed it is sql or not
	 * Note: Very primative, only checks $query for spaces.
	 * 
	 * @param string $sqlString
	 * 
	 * @return bool
	 * 
	 * Note: will throw exception if it can't complete this task.
	 */
	public function isSql($sqlString);
	
	//returns the id of the last inserted row
	/**
	 * Returns the id of the last inserted row.
	 * Note: will only be useful if you have just performed an INSERT on a table of the database
	 * 
	 * @return mixed
	 * 
	 * Note: will throw exception if it can't complete this task.
	 */
	public function lastInsertId();
	
	/**
	 * Disconnect from database by destroying PDO connection object if it exists
	 */
	public function disconnect();
	
}

?>