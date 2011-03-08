<?php

require_once("../lib/FirePHPCore/fb.php");

class Reporter {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////

	
	private $filename;
	private $DEBUG;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile) {
		
		$this->filename		= $logfile;
		$this->DEBUG		= 1;
	}
	
	
			///////////////////CONTROL FUNCTIONS/////////////////
	
	
	public function fail() {
		die('{success: false}');
	}
	
	public function failWithMsg($message) {
		$this->d($message);
		$this->fail();
	}
	
	
			///////////////////DEBUG FUNCTIONS/////////////////////
	
	
	
	public function getDebug() {
		return $this->DEBUG;
	}
	
	public function setDebug($dbug) {
		$this->DEBUG	= $dbug;
	}
	
	public function d($message) {
		if($this->DEBUG) {
			fb($message);
		}
	}
	
	
	
			///////////////////LOGGING FUNCTIONS/////////////////////
	
	

	public function getFilename() {
		return $this->filename;
	}
	
	public function setFilename($fname) {
		$this->filename	= $fname;
	}
	
	//logs a message to the file with a timestamp
	public function logToFile($msg) { 
		// open file
		$fd = fopen($this->filename, "a");
		
		// append message to date/time
		$str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg;
		
		// write string
		fwrite($fd, $str . "\n");
		
		// output the logged string if debug mode
		$this->d($str);
		
		// close file
		fclose($fd);
	}
	
	//returns the logger's set path
	public function getLogPath() {
		return $this->filename;
	}
	
	//returns the contents of the log
	public function printLog() {
		//open file
		$fd = fopen($this->filename, "r");
		
		//read contents of file
		$filedata = fread($fd, filesize($this->filename));
		fclose($fd);
		$fd = fopen($this->filename, "a");
		fwrite($fd, "FILE READ");
		fclose($fd);
		
		//return the data
		return $filedata;
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		
	}
	
}

?>