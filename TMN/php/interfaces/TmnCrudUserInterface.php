<?php

interface TmnCrudUserInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	/**
	 * 
	 * Creates a user object that will load data into itself from JSON, Assoc Arrays or the Database.
	 * You can then manipulate it before you output it to the database or a JSON String.
	 * The object can be loaded with data from JSON strings or Assoc Arrays.
	 * 
	 * It also has CRUD methods available so that you can push data from the object
	 * into the table or pull data from the table into the object.
	 * 
	 * It also inherits from TmnCrud so have a look at TmnCrudInterface.php more methods
	 * that are available to this class.
	 * 
	 * @param String		$logfile	- path of the file used to log any exceptions or interactions
	 * @param string		$guid		- Global User ID for user you want to load into this class
	 * 
	 * @example $user = new TmnCrudUser("logfile.log");					will create an empty user
	 * @example $user = new TmnCrudUser("logfile.log", "your_guid");	will create a user filled with the data associated with your_guid
	 * 
	 * Note: Method will throw FatalException if it can't complete construction.
	 */
	//this line is commented out to avoid conflicts with sub-classes, please leave commented
	//public function __construct($logfile, $guid);
	
	
			///////////////////ACCESSOR FUNCTIONS/////////////////////
	
	
	/**
	 * Returns the currently set Global User ID
	 * 
	 * @return string
	 */
	public function getGuid();
	
	/**
	 * Will load the user's data into this object before setting the value of the user's guid to the passed value.
	 * If the user can't be found, the guid and associated data will be left as it was.
	 * 
	 * Note: will throw LightException if it can't complete this task.
	 */
	public function setGuid($guid);
	
	/**
	 * Returns the currently set Financial Account Number
	 * 
	 * @return int
	 */
	public function getFan();
	
	/**
	 * Return a TmnCrudUser object that is filled with the spouse's data.
	 * 
	 * @return TmnCrudUser
	 */
	public function getSpouse();
	
	/**
	 * Returns the currently set spouse guid
	 * 
	 * @return string
	 */
	public function getSpouseGuid();
	
	/**
	 * Check's if the spouse exists before updating the spouse guid to the passed value.
	 * 
	 * @param string $guid - Global User ID of the user's spouse
	 * 
	 * Note: will throw LightException if it can't complete this task.
	 */
	public function setSpouseGuid($guid);
	
	/**
	 * Finds the user with the passed first name and last name before updating the spouse guid to the found value.
	 * 
	 * @param string $firstname - First name of the user's spouse
	 * @param string $surname	- Last name of the user's spouse
	 * 
	 * Note: will throw LightException if it can't complete this task.
	 */
	public function setSpouseWithName($firstname, $surname);
	
	/**
	 * Returns the currently set MPD supervisor guid
	 * 
	 * @return string
	 */
	public function getMpdGuid();
	
	/**
	 * Check's if the mpd supervisor exists before updating the spouse guid to the passed value.
	 * 
	 * @param string $guid - Global User ID of the user's mpd supervisor
	 * 
	 * Note: will throw LightException if it can't complete this task.
	 */
	public function setMpdGuid($guid);
	
	/**
	 * Finds the user with the passed first name and last name before updating the mpd supervisor guid to the found value.
	 * 
	 * @param string $firstname - First name of the user's mdp supervisor
	 * @param string $surname	- Last name of the user's mdp supervisor
	 * 
	 * Note: will throw LightException if it can't complete this task.
	 */
	public function setMpdWithName($firstname, $surname);
	
	
			///////////////////ACTION FUNCTIONS/////////////////////
	
	
	/**
	 * 
	 * Tells you if the user is an administrator
	 * 
	 * @return bool
	 */
	public function isAdmin();
	
	/**
	 * alias for setGuid($guid)
	 * 
	 * @param string $guid - Global User ID for user you want to load into this class
	 */
	public function loadUserWithGuid($guid);
	
	/**
	 * Will find the user based on their first and last name then will load their data into the class.
	 * 
	 * @param string $firstname - user's first name, to be used in the query
	 * @param string $surname	- user's last name, to be used in the query
	 * 
	 * Note: will throw LightException if it can't complete this task.
	 */
	public function loadUserWithName($firstname, $surname);
	
}

?>