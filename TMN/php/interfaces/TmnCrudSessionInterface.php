<?php

interface TmnCrudSessionInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
			
	
	/**
	 * Creates a session object that will load data into itself from JSON, Assoc Arrays or the Database.
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
	 * @param String		$session_id	- session ID for the session you want to load into this class
	 * 
	 * Note: Method will throw FatalException if it can't complete construction.
	 */
	public function __construct($logfile, $session_id);
	
	
			/////////////////ACCESSOR FUNCTIONS////////////////
	
	
	/**
	 * Return a TmnCrudUser object that is filled with the data of the user that owns/created this session.
	 * 
	 * @return TmnCrudUser
	 */
	public function getOwner();
	
	/**
	 * Sets the owner to be TmnCrudUser object that is filled with the data of the user that owns/created this session.
	 * 
	 * @param TmnCrudUser $owner - the user object that represents the owner of the session
	 * 
	 * @example setOwner(); will set the owner to null
	 */
	public function setOwner(TmnCrudUser $owner);
	
	/**
	 * Returns the currently set Global User ID for the owner
	 * 
	 * @return string
	 */
	public function getOwnerGuid();
	
	/**
	 * Will load the owner's data into the session's owner object before setting the value of the owner's guid to the passed value.
	 * If the user can't be found, the guid and associated data will be left as it was.
	 * 
	 * Note: will throw LightException if it can't complete this task.
	 */
	public function setOwnerGuid($guid);
	
	
			////////////////AUTHORISATION METHODS///////////////
			
	
	
	/**
	 * Will create and initiate the authorisation process for this session.
	 * 
	 * @param TmnCrudUser $user				- the user that created the session
	 * @param TmnCrudUser $level1Authoriser	- the level 1 authoristor for that user (ie ministry overseer)
	 * @param TmnCrudUser $level2Authoriser - the level 2 authoristor for that user (ie national ministry overseer)
	 * @param TmnCrudUser $level3Authoriser - the level 3 authoristor for that user (ie national director)
	 * @param Assoc Array $data				- the data for the session that is to be submitted
	 * 
	 * @return bool - will return true if completed sucessfully
	 * 
	 * Note: will throw Exception if it can't complete this task.
	 */
	public function submit( TmnCrudUser $user, TmnCrudUser $level1Authoriser, TmnCrudUser $level2Authoriser, TmnCrudUser $level3Authoriser,  $data );
	
	/**
	 * Checks whether the user passed to it is one of the people on the list of authorisers for this session.
	 * Note: If the session has not yet been submitted this method will return false.
	 * 
	 * @param TmnCrudUser $user	- the user that created the session
	 * 
	 * @return bool - indicates success
	 */
	public function userIsAuthoriser(TmnCrudUser $user);
	
	/**
	 * Sets a response for the an authoriser of this session.
	 * 
	 * @param int $level		- the level of the authoriser
	 * @param string $response	- the response of that authoriser
	 */
	public function authorise($level, $response);
	
}

?>