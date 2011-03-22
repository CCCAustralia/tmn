<?php

/*******************************************                                                        
# Test Code                 
*******************************************/

include_once('../classes/Reporter.php');
$LOGFILE	= "../logs/ReporterClass-test.log";
$DEBUG = 1;

	//Constructor test

fb("Constructor Test");
$reporterObj	= Reporter::newInstance($LOGFILE);

/*
 * Expected output
 * 
 * Console Output:
 * Constructor Test
 * 
 * Screen Output:
 * 
 */

	//Exception test

fb("Exception Test");
fb("Light Exception:");
try {
	throw new LightException("Test LightException: Hello World!");
} catch (LightException $e) {
	$reporterObj->exceptionHandler($e);
}

fb("Exception:");
try {
	throw new Exception("Test Exception: Hello World!");
} catch (Exception $e) {
	$reporterObj->exceptionHandler($e);
}

fb("Fatal Exception:");
try {
	throw new FatalException("Test FatalException: Hello World!");
} catch (FatalException $e) {
	$reporterObj->exceptionHandler($e);
}

/*
 * Expected output
 * 
 * Console Output:
 * Exception Test
 * Light Exception:
 * ReporterClass-test.php; ln 31; Test LightException: Hello World!
 * Exception:
 * ReporterClass-test.php; ln 38; Test Exception: Hello World!
 * Fatal Exception:
 * ReporterClass-test.php; ln 45; Test FatalException: Hello World!
 * 
 * Screen Output:
 * {success:false}
 * 
 */

	//Debug test

fb("Debug Test");
fb("getDebug(): " . $reporterObj->getDebug());
fb("d('debugging is on')"); $reporterObj->d('debugging is on');
fb("stopDebug()"); $reporterObj->stopDebug();
fb("getDebug(): " . $reporterObj->getDebug());
fb("d('debugging is off')"); $reporterObj->d('debugging is off');
fb("startDebug()"); $reporterObj->startDebug();
fb("getDebug(): " . $reporterObj->getDebug());
fb("d('debugging is back on')"); $reporterObj->d('debugging is back on');

/*
 * Expected output
 * 
 * Console Output:
 * Debug Test
 * getDebug(): 1
 * d('debugging is on')
 * debugging is on
 * stopDebug()
 * getDebug(): 0
 * startDebug()
 * getDebug(): 1
 * d('debugging is back on')
 * debugging is back on
 * 
 * Screen Output:
 * 
 */

	//Logging test

fb("Logging Test");
fb("getFilename(): " . $reporterObj->getFilename());
fb("logToFile('logging is on')"); $reporterObj->logToFile('logging is on');
fb("stopDebug()"); $reporterObj->stopDebug();
fb("setFilename('ReporterClass-test-otherlog.log')"); $reporterObj->setFilename('ReporterClass-test-otherlog.log');
fb("logToFile('logging to a new file')"); $reporterObj->logToFile('logging to a new file');
fb("printLog()"); fb($reporterObj->printLog());
fb("printLog()"); fb($reporterObj->printLog());

/*
 * Expected output
 * 
 * Console Output:
 * Logging Test
 * getFilename(): ReporterClass-test.log
 * logToFile('logging is on')
 * [<timestamp of call>] logging is on
 * stopDebug()
 * setFilename('ReporterClass-test-otherlog.log')
 * getFilename(): ReporterClass-test-otherlog.log
 * logToFile('logging to a new file')
 * printLog()
 * [<timestamp of call>] logging to a new file
 * printLog()
 * [<timestamp of call>] logging to a new file
 * [<timestamp of call>] FILE READ
 * 
 * Screen Output:
 * 
 * ReporterClass-test.log Output
 * [<timestamp of call>] logging is on
 * 
 * ReporterClass-test-otherlog.log Output
 * [<timestamp of call>] logging to a new file
 * [<timestamp of call>] FILE READ
 * 
 */

	//Fail test

fb("Fail Test");
fb("startDebug()"); $reporterObj->startDebug();
fb("fail()"); $reporterObj->fail();
fb("failWithMsg('hello')"); $reporterObj->failWithMsg('hello');

/*
 * Expected output (when fail() line commented out)
 * 
 * Console Output:
 * Fail Test
 * startDebug()
 * failWithMsg('hello')
 * hello
 * 
 * Screen Output:
 * {success: false}
 */

/*
 * Expected output (with no commenting)
 * 
 * Console Output
 * Fail Test
 * startDebug()
 * fail()
 * 
 * Screen Output:
 * {success: false}
 */

?>
