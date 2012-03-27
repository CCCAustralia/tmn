<?php

interface TmnCrudLowAccountProcessorInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	/**
	 * 
	 * Creates a LowAccountProcessor object that will load data into itself from JSON, Assoc Arrays or the Database.
	 * You can then manipulate it before you output it to the database or a JSON String.
	 * The object can be loaded with data from JSON strings or Assoc Arrays.
	 * 
	 * It also has CRUD methods available so that you can push data from the object
	 * into the table or pull data from the table into the object.
	 * 
	 * It also inherits from TmnCrud so have a look at TmnCrudInterface.php more methods
	 * that are available to this class.
	 * 
	 * @param String		$logfile					- path of the file used to log any exceptions or interactions
	 * @param string		$financial_account_number	- Global User ID for user you want to load into this class
	 * 
	 * @example $user = new TmnCrudUser("logfile.log");				will create an empty object
	 * @example $user = new TmnCrudUser("logfile.log", 1019999);	will create an object filled with the data associated with 1019999
	 * 
	 * Note: Method will throw FatalException if it can't complete construction.
	 */
	//this line is commented out to avoid conflicts with sub-classes, please leave commented
	//public function __construct($logfile, $financial_account_number);
	
	
			///////////////////ACCESSOR FUNCTIONS/////////////////////
			
	/**
	 * Fills the object with data from the DB, associated with the financial account number passed to it.
	 * 
	 * @param int $financial_account_number
	 */
	public function loadRowWithFan($financial_account_number);
	
	/**
	 * Returns the currently set Financial Account Number
	 * 
	 * @return int
	 */
	public function getFan();
	
	/**
	 * 
	 * Return the id of the session that is currently active for this Financial Account Number.
	 * 
	 * @return int
	 */
	public function getCurrentSessionID();
	
	/**
	 * 
	 * Return a TmnCrudSession object that if filled with the data of the session that is currently active for this Financial Account Number.
	 * 
	 * @return TmnCrudSession
	 */
	public function getCurrentSession();
	
	/**
	 * 
	 * Return the date of the session that is currently active for this Financial Account Number.
	 * 
	 * @return string
	 */
	public function getEffectiveDateForCurrentSession();
	
	
			///////////////////ACTION FUNCTIONS/////////////////////
	
	/**
	 * 
	 * Saves the new current session and date it was made effective to the DB.
	 * 
	 * @param int		$session_id
	 * @param string	$date
	 * 
	 * @return bool - on success/failure
	 */
	public function updateCurrentSession($session_id, $date_made_effective);
	
}

?>