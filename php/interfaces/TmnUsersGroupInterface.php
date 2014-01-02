<?php

interface TmnUsersGroupInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	/**
	 * 
	 * Creates a UsersGroup object that contains data about the users who hold the position that
	 * has been given to this object. The object can be used to see if users belong to the group
	 * to get email addresses for everyone in the group, etc.
	 * 
	 * @param string		$table_name	- The Name of the database table that holds the mapping between user and position
	 * @param string		$position	- Position of the Users you want to load into this users group
	 * 
	 * @example $financeAdminsUserGroup = new TmnUsersGroup("Admins", "FINANCE_USER");	will create an object with all the users who are FINANCE_USERs according to the Admins table
	 * 
	 * Note: Method will throw FatalException if it can't complete construction.
	 */
	//this line is commented out to avoid conflicts with sub-classes, please leave commented
	//public function __construct($table_name, $position);
	
	
			///////////////////ACCESSOR FUNCTIONS/////////////////////
	
	
	public function loadUsersWithPosition($position);
	
	public function getArrayOfUsers();
	
	public function containsUser($guid);
	
	public function getEmailsAsString();
	
}

?>