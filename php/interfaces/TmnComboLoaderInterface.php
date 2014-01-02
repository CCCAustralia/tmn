<?php

interface TmnComboLoaderInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	/**
	 * Constructor for TmnComboLoader
	 * 
	 * Will create an object that will create a json package from a table that is set up to hold combo
	 * box options. The constructor will check to see if the table passed to it is one of those tables.
	 * 
	 * Note: This class inherits from Reporter, look at ReporterInterface.php to see other methods available
	 * to this class.
	 * 
	 * @param String $logfile	- path of the file used to log any exceptions or interactions
	 * @param String $tablename	- The name of the table you want to interact with
	 * 
	 * Note: Method will throw FatalException if it can't complete construction.
	 */
	//public function __construct($logfile, $tablename);
	
	/**
	 * 
	 * Constructor for TmnSessionComboLoader
	 * 
	 * Will create an object that will create a json package from a table that is set up to hold combo
	 * box options. The constructor will check to see if the table passed to it is one of those tables.
	 * 
	 * Note: This class inherits from Reporter, look at ReporterInterface.php to see other methods available
	 * to this class.
	 * 
	 * @param String $logfile	 	- path of the file used to log any exceptions or interactions
	 * @param TmnCrudUser $user 	- User object that contains the user details of the authenticated user
	 * @param String $tablename		- The name of the table you want to interact with
	 * @param Bool $aussie_form		- Flag to say whether the form requesting the combo list is a form for aussie missionaries
	 * @param Bool $overseas_form	- Flag to say whether the form requesting the combo list is a form for international missionaries
	 * @param Bool $home_assignment	- Flag to say whether the form requesting the combo list is the home assignment form
	 * 
	 * Note: Method will throw FatalException if it can't complete construction.
	 */
	//public function __construct($logfile, $user, $tablename, $aussie_form, $overseas_form, $home_assignment);
	
	
			///////////////////LOADER FUNCTIONS/////////////////
	
	/**
	 * 
	 * Given the settings of the object this method will produce a json string for the requested combo list.
	 */
	public function produceJson();
	
}

?>