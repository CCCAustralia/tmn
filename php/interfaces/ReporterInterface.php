<?php

interface ReporterInterface {
	
	
			///////////////////CONSTRUCTOR/////////////////////
			
	/**
	 * 
	 * Creates and initialises an instance of Reporter.
	 * Note: This should be used instead of __construct because __construct
	 * is protected so that singleton classes can inherit from it.
	 * 
	 * @param String $logfile - the path of the file to be logged to
	 * 
	 * @return Reporter - an instance of self that has been initialised
	 */
	public static function newInstance($logfile);
	
	
			///////////////////EXCEPTION FUNCTIONS/////////////////////
	
	
	/**
	 * 
	 * Takes an exception and will output appropriate messages to the document and to
	 * the console (if in debug mode). Using this exception handler will stop the extjs
	 * interface from hanging when an exception happens
	 * 
	 * @param Exception $exception - an exception object to be dealt with
	 */
	public function exceptionHandler($exception);
	
	
			///////////////////FAIL FUNCTIONS/////////////////
	
	
	/**
	 * 
	 * kills script and returns failure message to front end
	 */
	public function fail();
	
	/**
	 * 
	 * Outputs message to console if debugging is on then kills script and returns failure message to front end
	 * 
	 * @param String $message - a message to be outputed to the console if in debug mode
	 */
	public function failWithMsg($message);
	
	
			///////////////////DEBUG FUNCTIONS/////////////////////
	
	
	/**
	 * 
	 * returns debug status
	 */
	public function getDebug();
	
	/**
	 * 
	 * Tells the object to ouput debug messages
	 */
	public function startDebug();
	
	/**
	 * 
	 * Tells the object not to ouput debug messages
	 */
	public function stopDebug();
	
	/**
	 * 
	 * Outputs a message to the console if debugging is on
	 * 
	 * @param String $message - a message to be outputed to the console if in debug mode
	 */
	public function d($message);
	
	
	
			///////////////////LOGGING FUNCTIONS/////////////////////
	
	
	/**
	 * 
	 * Returns the logger's set path
	 */
	public function getLogfile();
	
	/**
	 * 
	 * Sets a new path for the classes log file
	 * 
	 * @param String $fname - path of the new file that log statements will be outputed to
	 */
	public function setLogfile($fname);
	
	/**
	 * 
	 * Logs a message to the log file with a timestamp
	 * Note: If debugging has been started it will also output the log statement to the
	 * console.
	 * 
	 * @param String $msg - a message to be outputed to the log file
	 */
	public function logToFile($msg);
	
	/**
	 * 
	 * Returns the contents of the log
	 * 
	 * @return String - a string with the contents of the object's log file
	 */
	public function printLog();
	
}

?>