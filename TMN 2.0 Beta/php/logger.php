<?php

function logToFile($filename, $msg)
{ 
	//$filename = "submitlog.log";
	
	// open file
	$fd = fopen($filename, "a");
	
	// append date/time to message
	$str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg;
	
	// write string
	fwrite($fd, $str . "\n");
	
	// close file
	fclose($fd);
}

?>