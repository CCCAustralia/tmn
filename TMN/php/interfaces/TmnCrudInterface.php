<?php

interface TmnCrudInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	/**
	 * 
	 * Creates an object that will interact with a Database table on your behalf.
	 * The object can be loaded with data from JSON strings or Assoc Arrays.
	 * 
	 * It also has CRUD methods available so that you can push data from the object
	 * into the table or pull data from the table into the object.
	 * 
	 * Its also inherits from  Reporter so look at ReporterInterface.php to see what
	 * other methods are available to this class.
	 * 
	 * Note: "__" is used to represent null within this class (this lets us differentiate between a field that
	 * doesn't exist and one that is set to null). So if you are subclassing this and are accessing the arrays
	 * directly bare that in mind. If you use any of the methods below they will give and take null as a value,
	 * you will only have an issue if you try to access the array's directly
	 * 
	 * @param String		$logfile - path of the file used to log any exceptions or interactions
	 * @param String		$tablename - The name of the table you want to interact with
	 * @param String		$primarykey - The name of the Primary Key for the above table
	 * @param Assoc Array	$privatetypes - An Assoc array of fields in the array that you
	 * 						need within the class but don't want to be outputed from the object.
	 * 						Will have the form array("<field name>" => "<field type>", ...)
	 * 						<field name> needs to be lowercase here and uppercase in the database
	 * 						<field type> can be "s" - string, "i" - integer, "n" - null, "b" - bool
	 * @param Assoc Array	$publictypes - An Assoc array of fields in the array that you
	 * 						need within the class and will output from the class.
	 * 						Will have the form array("<field name>" => "<field type>", ...)
	 * 						<field name> needs to be lowercase here and uppercase in the database
	 * 						<field type> can be "s" - string, "i" - integer, "n" - null, "b" - bool
	 * 
	 * Note: Method will throw FatalException if it can't complete construction.
	 */
	//this line is commented out to avoid conflicts with sub-classes, please leave commented
	//public function __construct($logfile, $tablename, $primarykey, $privatetypes, $publictypes);
	
	
	
			///////////////////ACCESSOR FUNCTIONS/////////////////////
			
	
	/**
	 * 
	 * Returns the value of a field you give it.
	 * 
	 * @param String $fieldname - name of a field you want to get
	 * 
	 * @return Mixed	- The value of the field or false
	 */
	public function getField($fieldname);
	
	/**
	 * 
	 * Sets the value for a field you give it.
	 * 
	 * @param String $fieldname - name of a field you want to get
	 * @param Mixed $value - the value to be set
	 * 
	 * @return Bool		- true if it worked, false if it didn't
	 */
	public function setField($fieldname, $value);
	
	
			///////////////////CRUD FUNCTIONS/////////////////////
			
	
	/**
	 * 
	 * Once data is loaded into the object this method takes the data and creates a new row
	 * in the table using that data.
	 * 
	 * Note:	- Data can be loaded into the object using loadDataFromAssocArray($array) or loadDataFromJsonString($string)
	 * 			- Method will throw LightException if it can't complete this task.
	 */
	public function create();
	
	/**
	 * 
	 * Once a value for the primary key has been loaded into the object this method will retrieve
	 * values for the private and public fields from the database using that value of the primary key.
	 * 
	 * Note:	- If a value for the primary key is not first set with loadDataFromAssocArray($array) or
	 * loadDataFromJsonString($string) it will throw an exception saying user not found.
	 * 			- Method will throw LightException if it can't complete this task.
	 */
	public function retrieve();
	
	/**
	 * 
	 * Once data is loaded into the object this method takes the data and updates the row with the same
	 * primary key that is stored in the object.
	 * 
	 * Note:	- Data can be loaded into the object using loadDataFromAssocArray($array) or loadDataFromJsonString($string)
	 * 			- Method will throw LightException if it can't complete this task.
	 */
	public function update();
	
	/**
	 * 
	 * Once a value for the primary key has been loaded into the object this method will delete
	 * the row in the table based on that value of the primary key.
	 * 
	 * Note:	- If a value for the primary key is not first set with loadDataFromAssocArray($array) or
	 * loadDataFromJsonString($string) it will throw an exception.
	 * 			- Method will throw LightException if it can't complete this task.
	 */
	public function delete();
	
	
	////////////////////////////JSON FUNCTIONS////////////////////////////
	
	
	/**
	 * 
	 * Takes the values stored in the object and creates a json string from them.
	 * 
	 * @return String - json string representing the data in the object at that time
	 */
	public function produceJson();
	
	/**
	 * 
	 * Takes the values stored in the object and creates an associative array from them.
	 * 
	 * @return Assoc Array - associative array filled with the data in the object at that time
	 */
	public function produceAssocArray();
	
	/**
	 * 
	 * Takes a json string, parses it and loads any values it finds that relate to its fields
	 * @param String		$string - json string that contains data to be loaded
	 * 								$string needs to be in the form { ..., data:{ <field name>: <value>, ... }, ...}
	 * 
	 * Note: Method will throw LightException if it can't complete this task
	 */
	public function loadDataFromJsonString($string);
	
	/**
	 * 
	 * Takes an associative array filled with data index by field names and loads them into the objects instance variables.
	 * @param Assoc Array 	$array - an associative array with keys that match the field names.
	 * 						Will have form: array('<field name in lowercase> => <value>)
	 */
	public function loadDataFromAssocArray($array);
	
	
	////////////////////////////EXTRA FUNCTIONS///////////////////////////
	
	
	/**
	 * 
	 * Will overwrite every field value in the object with null
	 */
	public function reset();
	
	
	//type checks the fields for the user and throws an exception if anything is wrong
	/**
	 * 
	 * Takes a value and a type and checks whether the type of value matches type
	 * 
	 * @param Mixed			$value - value of a field, will have its type checked against type
	 * @param String		$type - a one character string that represents they type this value should be
	 * 						needs to be one of "s" - string, "i" - integer, "n" - null, "b" - bool
	 * 
	 * @return Bool - if it succeeds it returns true otherwise it throws a LightException
	 * 
	 * Note: Method will throw LightException if the type is wrong
	 */
	public function valueMatchesType($value, $type);
	
}

?>