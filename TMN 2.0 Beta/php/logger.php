<?php
require_once("../lib/FirePHPCore/fb.php");

class logger {
	private $filename;
	private $DEBUG;
	
	public function __construct($logfile) {
		$this->filename = $logfile;
	}
	
	public function setDebug($dbug) {
		$this->DEBUG = $dbug;
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
		if ($this->DEBUG) fb($str);
		
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
	
}

?>