<?php
include_once('php/classes/Tmn.php');
include_once('php/classes/TmnDatabase.php');
include_once('php/classes/email.php');
include_once('php/classes/TmnConstants.php');
$constants = getConstants(array("VERSIONNUMBER"));
$LOGFILE		= 'php/logs/index.log';
$DEBUG			= 1;
$NEWVERSION		= 1;
$VERSIONNUMBER	= $constants['VERSIONNUMBER'];//"2-1-1";

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

try {
	//authenticate
	Tmn::authenticate();
	
	//create a tmn helper
	$tmn	= new Tmn($LOGFILE);
	
	//create a db connection
	$db		= TmnDatabase::getInstance($LOGFILE);
	
	//check if they are a valid user (If not show the rego page)
	$stmt	= $db->query("SELECT GUID FROM User_Profiles WHERE GUID='" . $tmn->getAuthenticatedGuid() . "'");
	
	if ($stmt->rowCount() == 1){
		//if the user has a valid email address update email address in the database when they log in
		if (Email::validateAddress($tmn->getEmail())) {
			$db->query("UPDATE `User_Profiles` SET EMAIL='" . $tmn->getEmail() . "' WHERE GUID='". $tmn->getAuthenticatedGuid() ."'");
		}
		
		//if there is a session set, drop it into the webpage as a javascript variable
		if (isset($_REQUEST['session'])) {
			$g_session	= $_REQUEST['session'];
		} else {
			$g_session	= 0;
		}
			
		//ouput tmn page
		echo	'<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
				<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
		
					if ($DEBUG) {
						echo '<link rel="stylesheet" type="text/css" href="lib/resources/css/loading.css'.$force_reload.'" />
						<link rel="stylesheet" type="text/css" href="lib/resources/css/ext-all.css'.$force_reload.'" />
						<link rel="stylesheet" type="text/css" href="lib/resources/css/customstyles.css'.$force_reload.'" />';
					} else {
						echo '<link rel="stylesheet" type="text/css" href="lib/resources/css/tmn-all.css'.$force_reload.'" />';
					}
					
					echo '<title>TMN Viewer</title>
				</head>
				<body>
					<div id="loading-mask"></div>
					<div id="loading">
						<span id="loading-message">Loading. Please wait...</span>
					</div>
					<script type="text/javascript">
						document.getElementById("loading-message").innerHTML = "Loading Ext Library...";
					</script>';
					
					if ($DEBUG) {
						echo '<script type="text/javascript" src="lib/ext-base.js'.$force_reload.'"></script>
						<script type="text/javascript" src="lib/ext-all'.$force_debug.'.js'.$force_reload.'"></script>';
					} else {
						echo '<script type="text/javascript" src="lib/ext.js'.$force_reload.'">';
					}
					
					echo '<script type="text/javascript">
							document.getElementById("loading-message").innerHTML = "Loading Custom Libraries...";
						</script>';
					
					if ($DEBUG) {
						echo '<script type="text/javascript" src="lib/customclasses/Printer-all.js'.$force_reload.'"></script>
						<script type="text/javascript" src="lib/customclasses/Ext.LinkButton.js'.$force_reload.'"></script>';
					} else {
						echo '<script type="text/javascript" src="lib/customclasses/custom-libraries-all.js'.$force_reload.'"></script>';
					}
					
					
					echo '<script type="text/javascript">
						document.getElementById("loading-message").innerHTML = "Loading TMN Viewer...";
					</script>
					<script type="text/javascript">var G_SESSION = ' . $g_session . ';</script>';
					
					if ($DEBUG) {
						echo '<script type="text/javascript" src="ui/AuthorisationViewerControlPanel.js'.$force_reload.'"></script>
						<script type="text/javascript" src="ui/AuthorisationPanel.js'.$force_reload.'"></script>
						<script type="text/javascript" src="ui/SummaryPanel.js'.$force_reload.'"></script>
						<script type="text/javascript" src="ui/authviewer.js'.$force_reload.'"></script>';
					} else {
						echo '<script type="text/javascript" src="ui/tmn-authviewer-all.js'.$force_reload.'"></script>';
					}
					
					echo '<center>
						<div id="tmn-viewer-controls-cont"></div>
						<div id="tmn-reasonpanel-cont"></div>
						<div id="tmn-viewer-cont"></div>
					</center>
				</body>
				</html>';
	} else {
		echo "You don't have permission to access this page. If you think you should be able to access this page, contact <a href=\"mailto:tech.team@ccca.org.au\">tech.team@ccca.org.au</a>";
	}
} catch (Exception $e) {
	echo 'Authentication failed due to Database Error. Please contact <a href="tech.team@ccca.org.au">tech.team@ccca.org.au</a>.';
}

?>
