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
	//public function __construct($logfile, $session_id);
	
	
			/////////////////ACCESSOR FUNCTIONS////////////////
			
	
	/**
	 * Calcs how may financial years difference there are between now and the time this session was created
	 */
	public function financialYearsSinceSessionCreation();
	
	/**
	 * Adds inflation to values the user has entered. The rate used is 2.5% ie they are multiplied by 1.025.
	 */
	public function applyInflation();
	
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
	public function setOwner(TmnCrudUser $owner=null);
	
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
	
	/**
	 * Returns a TmnCrudSession object that is filled with the data of the session that is linked to this session
	 * (given that this session is an international assignment session).
	 * 
	 * @return TmnCrudSession
	 */
	public function getHomeAssignment();
	
	/**
	 * Sets the owner to be TmnCrudSession object that is filled with the data of the session that is linked to this session.
	 * (given that this session is an international assignment session).
	 * 
	 * @param TmnCrudUser $home_assignment - the session object that represents the home assignment linked to this session
	 * 
	 * @example setHomeAssignment(); will set the homeAssignment to null
	 */
	public function setHomeAssignment(TmnCrudSession $home_assignment = null);
	
	/**
	 * Returns a TmnCrudSession object that is filled with the data of the session that is linked to this session
	 * (given that this session is an home assignment session).
	 * 
	 * @return TmnCrudSession
	 */
	public function getInternationalAssignment();
	
	/**
	 * Sets the owner to be TmnCrudSession object that is filled with the data of the session that is linked to this session.
	 * (given that this session is an home assignment session).
	 * 
	 * @param TmnCrudUser $international_assignment - the session object that represents the home assignment linked to this session
	 * 
	 * @example setInternationalAssignment(); will set the homeAssignment to null
	 */
	public function setInternationalAssignment(TmnCrudSession $international_assignment = null);
	
	
			////////////////////////////JSON METHODS////////////////////////////
			
	
	
	/**
	 * Returns the data in this object as an associative array. It also adds data from other tables.
	 * You can choose if you want to grab the auth data.
	 * 
	 * @param	bool	$add_auth_reasons	- lets you choose whether you want to add auth reasons to the string
	 * 
	 * @return	assoc array					- assoc array representing data in this object
	 */
	public function produceAssocArrayForDisplay($add_auth_reasons=null);
	
	/**
	 * Returns the data in this object as a json string. It also adds data from other tables. You can choose if you
	 * want to grab the auth data.
	 * 
	 * @param	bool	$add_auth_reasons	- lets you choose whether you want to add auth reasons to the string
	 * 
	 * @return	string						- json string representing data in this object
	 */
	public function produceJsonForDisplay($add_auth_reasons=null);
	
	
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
	public function submit( TmnCrudUser $auth_user, TmnCrudUser $auth_level_1, $auth_level_1_reasons = null, TmnCrudUser $auth_level_2 = null, $auth_level_2_reasons = null, TmnCrudUser $auth_level_3 = null, $auth_level_3_reasons = null, $data );
	
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
	 * @param TmnCrudUser $user		- the authoriser
	 * @param string $response	- the response of that authoriser
	 */
	public function authorise(TmnCrudUser $user, $response);
	
	/**
	 * Fetches the current authorisation progress of the session
	 * 
	 * @return an assoc array containing the current response (Yes, No, Pending) and a name (who is responsible for that response)
	 * 			ie {response:<authorisers response>, name: <authorisers full name>, date: <the data of this action>}
	 */
	public function getOverallProgress();
	
	 /**
	  * Gets the details of the authoriser that matches the user passed to this function
	  * 
	  * @param TmnCrudUser $user		- the authoriser
	  * 
	  * @return an assoc array containing the current response (Yes, No, Pending)
	  * 		ie {response:<authorisers response>}
	  */
	public function getAuthoriserDetailsForUser(TmnCrudUser $user);
	
}

?>