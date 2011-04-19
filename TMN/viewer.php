<?php

$DEBUG = 1;
$NEWVERSION = 0;
$VERSIONNUMBER = "0-0-1";

if ($DEBUG) {
	$force_debug = "-debug";
} else {
	$force_debug = "";
}

if ($NEWVERSION){
	$force_reload = "?" . $VERSIONNUMBER;
} else {
	$force_reload = "";
}

include_once('php/classes/Tmn.php');
include_once('php/classes/TmnCrudSession.php');
$LOGFILE	= "php/logs/viewer.log";

try {
	
	//make sure they are logged in
	Tmn::authenticate();
	
	$tmn	= new Tmn($LOGFILE);
	
	//check authentication has happened
	if ($tmn->isAuthenticated()) {
		
		//if a session has been set to load
		if (isset($_REQUEST['session'])) {
			//load the session
			$session = new TmnCrudSession($LOGFILE,$_REQUEST['session']);
			
			//check that the user logged in is the owner of the session
			if ($tmn->getAuthenticatedGuid() == $session->getField('guid')) {
				
				//check if the session has spouse data
				if ($session->getField("s_firstname") == null || $session->getField("s_firstname") == '') {
					$hasSpouse = "false";
				} else {
					$hasSpouse = "true";
				}
				
				//chekc if its an aussie session
				if ($session->getField("home_assignment_session_id") == null && $session->getField("international_assignment_session_id") == null) {
					
					//put together the data packet for the front end to render
					$response	= array('aussie-based' => $session->produceAssocArrayForDisplay());
					$isOverseas	= "false";
					
				} else {
					
					//grab session details if its an international assignment session
					if ($session->getField("international_assignment_session_id") == null) {
						$ia_session		= $session;
						$ha_session		= $session->getHomeAssignment();
					}
					
					//grab session details if its an home assignment session
					if ($session->getField("home_assignment_session_id") == null) {
						$ia_session		= $session->getInternationalAssignment();
						$ha_session		= $session;
					}
					
					//put together the data packet for the front end to render
					$response	= array(
									'international-assignment'	=> $ia_session->produceAssocArrayForDisplay(),
									'home-assignment'			=> $ha_session->produceAssocArrayForDisplay()
								);
					$isOverseas	= "true";
					
				}
				fb(json_encode($response));
				fb($hasSpouse);
				fb($isOverseas);
				echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
				<html>
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
				
						if ($DEBUG) {
							echo '<link rel="stylesheet" type="text/css" href="lib/resources/css/loading.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/resources/css/ext-all.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/customclasses/statusbar/css/statusbar.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/resources/css/customstyles.css'.$force_reload.'" />';
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
						echo '<script type="text/javascript" src="lib/customclasses/Printer-all.js'.$force_reload.'"></script>';
					} else {
						echo '<script type="text/javascript" src="lib/customclasses/Printer-all.js'.$force_reload.'"></script>';
					}
					
					echo '<script type="text/javascript">
							document.getElementById("loading-message").innerHTML = "Loading TMN Form...";
						</script>
						<script type="text/javascript">
							var G_DATA = ' . json_encode($response) . ',
								G_ISOVERSEAS = ' . $isOverseas . ',
								G_HASSPOUSE = ' . $hasSpouse . ';
						</script>';
					
					if ($DEBUG) {
						echo '<script type="text/javascript" src="ui/SummaryPanel.js'.$force_reload.'"></script>
						<script type="text/javascript" src="ui/viewer.js'.$force_reload.'"></script>';
					} else {
						echo '<script type="text/javascript" src="ui/viewer-all.js'.$force_reload.'"></script>';
					}
			
					echo '<center>
						<div id="tmn-viewer-cont"></div>
					</center>
				</body>
			</html>';
			} else {
				Tmn::showErrorPage("You are not the owner of this session. Please try loading a different session.");
			}
			
		} else {
			Tmn::showErrorPage("No Session ID found. You need to send this page a Session ID to load.");
		}
		
	} else {
		Tmn::showErrorPage("You don't have permission to access this page.");
	}
} catch (Exception $e) {
	Tmn::showErrorPage("A Database error occured for session " . $_REQUEST['session'] . " while trying to load the TMN viewer.");
}

?>