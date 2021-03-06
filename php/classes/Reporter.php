<?php

if (file_exists('../interfaces/ReporterInterface.php')) {
	include_once('../interfaces/ReporterInterface.php');
	require_once("../../lib/FirePHPCore/fb.php");
}
if (file_exists('php/interfaces/ReporterInterface.php')) {
	include_once('php/interfaces/ReporterInterface.php');
	require_once("lib/FirePHPCore/fb.php");
}
if (file_exists('interfaces/ReporterInterface.php')) {
	include_once('interfaces/ReporterInterface.php');
	require_once("../lib/FirePHPCore/fb.php");
}

//defines the two types of exceptions used in this project
class FatalException extends Exception {}
class LightException extends Exception {}

class Reporter implements ReporterInterface {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////

	
	private $logfile;
	private $DEBUG;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	//is protected so singleton classes can extend this class
	protected function __construct($logfile) {
		
		$this->setLogfile($logfile);
		$this->DEBUG = 1;
	}
	
	//interface for creating an instance of this class
	public static function newInstance($logfile) {
		return new Reporter($logfile);
	}
	
	
			///////////////////EXCEPTION FUNCTIONS/////////////////////
	
	
	//Handles exceptions based on their type
	public function exceptionHandler($exception) {
		
		//for a fatal exception
		if ($exception instanceof FatalException) {
			
			//construct the message
			$msg = $exception->getFile() . "; ln " . $exception->getLine() . "; Fatal Exception; " . $exception->getMessage() . "\n" . $exception->getTraceAsString();
			
			//switch to the exception log file, log the exception and switch back
			//$tempPath = $this->getLogfile();
			//$this->setLogfile("../logs/exceptions.log");
			$this->logToFile($msg);
			//$this->setLogfile($tempPath);
			
			//kill the script leaving the message on the console if in debug mode
			$this->failWithMsg($msg);
		
		//for a light exception
		} elseif ($exception instanceof LightException) {
			
			//construct the message
			$msg = $exception->getFile() . "; ln " . $exception->getLine() . "; Light Exception; " . $exception->getMessage() . "\n" . $exception->getTraceAsString();
			
			//switch to the exception log file, log the exception and switch back
			//$tempPath = $this->getLogfile();
			//$this->setLogfile("../logs/exceptions.log");
			$this->logToFile($msg);
			//$this->setLogfile($tempPath);
			
			//leave the message on the console if in debug mode and continue with the script
			$this->d($msg);
			
		//for an unknown exception
		} else {
			
			//construct the message
			$msg = $exception->getFile() . "; ln " . $exception->getLine() . "; Unknown Exception; " . $exception->getMessage() . "\n" . $exception->getTraceAsString();
			
			//switch to the exception log file, log the exception and switch back
			//$tempPath = $this->getLogfile();
			//$this->setLogfile("../logs/exceptions.log");
			$this->logToFile($msg);
			//$this->setLogfile($tempPath);
			
			//leave the message on the console if in debug mode and continue with the script
			$this->d($msg);
			
		}
		
	}
	
	
			///////////////////FAIL FUNCTIONS/////////////////
	
	
	//kills script and returns failure message to front end
	public function fail() {
		die(json_encode(array("success"=>false)));
	}
	
	//outputs message to console if debugging is on then kills script and returns failure message to front end
	public function failWithMsg($message) {
		$this->d($message);
		$this->fail();
	}
	
	
			///////////////////DEBUG FUNCTIONS/////////////////////
	
	
	//returns debug status
	public function getDebug() {
		return $this->DEBUG;
	}
	
	public function startDebug() {
		$this->DEBUG	= 1;
	}
	
	public function stopDebug() {
		$this->DEBUG	= 0;
	}
	
	//outputs a message to the console if debugging is on
	public function d($message) {
		if($this->DEBUG) {
			fb($message);
		}
	}
	
	
	
			///////////////////LOGGING FUNCTIONS/////////////////////
	
	//returns the logger's set path
	public function getLogfile() {
		return $this->logfile;
	}
	
	//returns the logger's new path
	public function setLogfile($fname) {
		$this->logfile	= $fname;
		if (!file_exists($this->logfile) && $this->logfile !== null) {
			$log = fopen($this->logfile, "c");
			fclose($log);
		}
	}
	
	//logs a message to the file with a timestamp
	public function logToFile($msg) { 
		// open file
		$fd = fopen($this->logfile, "a");
		
		// append message to date/time
		$str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg;
		
		// write string
		fwrite($fd, $str . "\n");
		
		// output the logged string if debug mode
		$this->d($str);
		
		// close file
		fclose($fd);
	}
	
	//returns the contents of the log
	public function printLog() {
		//open file
		$fd = fopen($this->logfile, "r");
		
		//read contents of file
		$filedata = fread($fd, filesize($this->logfile));
		fclose($fd);
		$fd = fopen($this->logfile, "a");
		fwrite($fd, "FILE READ\n");
		fclose($fd);
		
		//return the data
		return $filedata;
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		
	}
	
}

?>