<?php
include_once('lib/FirePHPCore/fb.php');

include_once('php/classes/Tmn.php');
include_once('php/classes/TmnDatabase.php');
include_once('php/classes/email.php');
$LOGFILE		= 'php/logs/index.log';
$DEBUG			= 1;
$NEWVERSION		= 1;
$BUILDNUMBER	= "current_build_number_will_be_inserted_by_upload_script";

if ($DEBUG) {
	$force_debug = "-debug";
} else {
	$force_debug = "";
}

if ($NEWVERSION && $DEBUG == 1){
	$force_reload = "?" . time();
} else if ($NEWVERSION && $DEBUG == 0) {
	$force_reload = "?" . $BUILDNUMBER;
} else {
	$force_reload = "";
}

//authenticate
if ($_POST['mode'] != "load") {
	Tmn::authenticate();
}

try {
	//create a tmn helper
	$tmn	= new Tmn($LOGFILE);
	
	//create a db connection
	$db		= TmnDatabase::getInstance($LOGFILE);
	
	if ($_POST['mode'] == 'load') {
		
		if ($tmn->isAuthenticated()) {
			
			//All Sessions awaiting approval by someone
			//$stmt = $db->query("SELECT `Tmn_Sessions`.SESSION_ID, `Tmn_Sessions`.SESSION_NAME, `Tmn_Sessions`.FIRSTNAME, `Tmn_Sessions`.SURNAME FROM `Tmn_Sessions` WHERE `Tmn_Sessions`.AUTH_SESSION_ID IN (SELECT AUTH_SESSION_ID FROM `Auth_Table` WHERE (FINANCE_RESPONSE = 'Pending' && AUTH_LEVEL_1 != '' && LEVEL_1_RESPONSE = 'Pending') || (FINANCE_RESPONSE = 'Pending' && AUTH_LEVEL_2 != '' && LEVEL_1_RESPONSE = 'Yes' && LEVEL_2_RESPONSE = 'Pending') || (FINANCE_RESPONSE = 'Pending' && AUTH_LEVEL_3 != '' && LEVEL_1_RESPONSE = 'Yes' && LEVEL_2_RESPONSE = 'Yes' && LEVEL_3_RESPONSE = 'Pending'))");
			
			//All sessions approved by finance
			//$stmt = $db->query("SELECT `Tmn_Sessions`.SESSION_ID, `Tmn_Sessions`.SESSION_NAME, `Tmn_Sessions`.FIRSTNAME, `Tmn_Sessions`.SURNAME, `Tmn_Sessions`.FAN, `Tmn_Sessions`.DATE_MODIFIED FROM `Tmn_Sessions` WHERE `Tmn_Sessions`.AUTH_SESSION_ID IN (SELECT AUTH_SESSION_ID FROM `Auth_Table` WHERE FINANCE_RESPONSE = 'Yes') && `Tmn_Sessions`.INTERNATIONAL_ASSIGNMENT_SESSION_ID IS NULL");
			
			//All current sessions approved by finance (removes test users and other non users by only selecting finacial account numbers that are valid, between 1010000 and 1020000)
			$stmt = $db->query("SELECT l.CURRENT_SESSION_ID AS SESSION_ID, s.SESSION_NAME, s.FIRSTNAME, s.SURNAME, l.FIN_ACC_NUM AS FAN, l.TMN_EFFECTIVE_DATE AS DATE_MODIFIED FROM (SELECT low.* FROM (SELECT DISTINCT FIN_ACC_NUM FROM User_Profiles WHERE IS_TEST_USER = 0 AND INACTIVE = 0 AND EXEMPT_FROM_TMN = 0) AS users LEFT JOIN Low_Account AS low ON users.FIN_ACC_NUM = low.FIN_ACC_NUM WHERE low.CURRENT_SESSION_ID IS NOT NULL) AS l LEFT JOIN Tmn_Sessions AS s ON l.CURRENT_SESSION_ID = s.SESSION_ID");
			
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$returndata = array();
			$returndata['data'] = $data;
			//fb($returndata);
			echo (json_encode($returndata));
			
			die();	//Don't spit out the adminviewer page
		} else {
			die("{success: false}");
		}
	}
	
	
	//check if they are a valid user (If not show the rego page)
	$stmt	= $db->query("SELECT GUID FROM User_Profiles WHERE GUID='" . $tmn->getAuthenticatedGuid() . "' AND ADMIN_TAB=1");
	
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
					<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
					<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />';
		
					if ($DEBUG) {
						echo '<link rel="stylesheet" type="text/css" href="lib/resources/css/loading.css'.$force_reload.'" />
						<link rel="stylesheet" type="text/css" href="lib/resources/css/ext-all.css'.$force_reload.'" />
						<link rel="stylesheet" type="text/css" href="lib/resources/css/customstyles.css'.$force_reload.'" />';
					} else {
						echo '<link rel="stylesheet" type="text/css" href="lib/resources/css/tmn-all.css'.$force_reload.'" />';
					}
					
					echo '<title>TMN Admin Viewer</title>
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
					<script type="text/javascript">
						var G_SESSION = ' . $g_session . ';
					</script>';
					if ($DEBUG) {
						echo '<script type="text/javascript" src="ui/AdminViewerControlPanel.js'.$force_reload.'"></script>
						<script type="text/javascript" src="ui/AuthorisationPanel.js'.$force_reload.'"></script>
						<script type="text/javascript" src="ui/SummaryPanel.js'.$force_reload.'"></script>
						<script type="text/javascript" src="ui/adminviewer.js'.$force_reload.'"></script>';
					} else {
						echo '<script type="text/javascript" src="ui/tmn-adminviewer-all.js'.$force_reload.'"></script>';
					}
					
					echo '<center>
						<div id="tmn-viewer-controls-cont"></div>
						<div id="tmn-level-1-reasonpanel-cont"></div>
						<div id="tmn-level-2-reasonpanel-cont"></div>
						<div id="tmn-level-3-reasonpanel-cont"></div>
						<div id="tmn-level-4-reasonpanel-cont"></div>
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
