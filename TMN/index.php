<?php
include_once('php/classes/TmnConstants.php');
$constants = getConstants(array("VERSIONNUMBER"));
$DEBUG			= 1;
$NEWVERSION		= 1;
$VERSIONNUMBER	= $constants['VERSIONNUMBER'];//"2-1-1";
$LOGFILE		= 'php/logs/index.log';

if ($DEBUG) {
	$force_debug = "-debug";
} else {
	$force_debug = "";
}

if ($NEWVERSION && $DEBUG == 1){
	$force_reload = "?" . time();
} else if ($NEWVERSION && $DEBUG == 0) {
	$force_reload = "?" . $VERSIONNUMBER;
} else {
	$force_reload = "";
}

//do the Authentication stuff
include_once('php/classes/Tmn.php');

try {
	//do the CAS/myGCX/theKey authentication
	Tmn::authenticate();
	fb($_SESSION);
	//will check if the user is properly authenticated and will give us a handy object to do auth stuff with
	//if user isn't properly logged in an exception will be thrown
	$tmn	= new Tmn($LOGFILE);
	
	//will try to get user, if user isn't in our Database
	//an exception will be thrown and we skip to the catch statement
	$tmn->getUser();
	
	//update the database with user's latest data
	//$tmn->updateUserData();
	
	//if it gets past the Authentication then print the TMN Page
	echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
	
	if ($DEBUG) {
		echo '<link rel="stylesheet" type="text/css" href="lib/resources/css/loading.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/resources/css/ext-all.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/statusbar/css/statusbar.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/resources/css/customstyles.css'.$force_reload.'" />';
	} else {
		echo '<link rel="stylesheet" type="text/css" href="lib/resources/css/tmn-all.css'.$force_reload.'" />';
	}
	
	echo '<title>TMN</title></head><body><div id="loading-mask"></div><div id="loading"><span id="loading-message">Loading. Please wait...</span></div><script type="text/javascript">document.getElementById("loading-message").innerHTML = "Loading Ext Library...";</script>';
	
	if ($DEBUG) {
		echo '<script type="text/javascript" src="lib/ext-base.js'.$force_reload.'"></script><script type="text/javascript" src="lib/ext-all'.$force_debug.'.js'.$force_reload.'"></script>';
	} else {
		echo '<script type="text/javascript" src="lib/ext.js'.$force_reload.'">';
	}
	
	echo '<script type="text/javascript">document.getElementById("loading-message").innerHTML = "Loading Custom Libraries...";</script>';
	
	if ($DEBUG) {
		echo '<script type="text/javascript" src="lib/DateRangeValidationType.js'.$force_reload.'"></script><script type="text/javascript" src="lib/statusbar/StatusBar.js'.$force_reload.'"></script><script type="text/javascript" src="lib/statusbar/ValidationStatus.js'.$force_reload.'"></script><script type="text/javascript" src="lib/Printer-all.js'.$force_reload.'"></script><script type="text/javascript" src="lib/iconcombo/Ext.ux.IconCombo.js'.$force_reload.'"></script>';
	} else {
		echo '<script type="text/javascript" src="lib/custom-libraries-all.js'.$force_reload.'"></script>';
	}
	
	echo '<script type="text/javascript">document.getElementById("loading-message").innerHTML = "Loading TMN Form...";</script>';
	
	if ($DEBUG) {
		echo '<script type="text/javascript" src="ui/AuthorisationPanel.js'.$force_reload.'"></script><script type="text/javascript" src="ui/SummaryPanel.js'.$force_reload.'"></script><script type="text/javascript" src="ui/PrintForm.js'.$force_reload.'"></script><script type="text/javascript" src="ui/InternalTransfers.js'.$force_reload.'"></script><script type="text/javascript" src="ui/FinancialDetailsForm.js'.$force_reload.'"></script><script type="text/javascript" src="ui/PersonalDetailsForm.js'.$force_reload.'"></script><script type="text/javascript" src="ui/TmnView.js'.$force_reload.'"></script><script type="text/javascript" src="ui/TmnController.js'.$force_reload.'"></script>';
	} else {
		echo '<script type="text/javascript" src="ui/tmn-all.js'.$force_reload.'"></script>';
	}
	
	echo '<center><div id="tmn-cont"></div></center><!-- Fields required for history management --><form id="history-form" class="x-hidden"><input type="hidden" id="x-history-field" /><iframe id="x-history-frame"></iframe></form></body></html>';
	
	
} catch (Exception $e) {
	//if something went wrong redirect to the rego page
	$ticket	= "";
	if ((isset($_REQUEST['ticket']) && $_REQUEST['ticket'] != '')) {
		$ticket	= "?ticket=" + $_REQUEST['ticket'];
	}
	header("Location: rego.php" + $ticket);
}

?>
