<?php
include_once('lib/FirePHPCore/fb.php');

include_once('php/classes/Tmn.php');
include_once('php/classes/TmnDatabase.php');
include_once('php/classes/email.php');
include_once('php/classes/TmnConstants.php');
$constants = getVersionNumberAsArray();
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
			$stmt = $db->query("SELECT low.CURRENT_SESSION_ID AS SESSION_ID, sessions.SESSION_NAME, sessions.FIRSTNAME, sessions.SURNAME, low.FIN_ACC_NUM AS FAN, low.TMN_EFFECTIVE_DATE AS DATE_MODIFIED FROM (SELECT * FROM Low_Account WHERE CURRENT_SESSION_ID IS NOT NULL AND FIN_ACC_NUM >= 1010000 AND FIN_ACC_NUM < 1020000 ) AS low LEFT JOIN Tmn_Sessions AS sessions ON low.CURRENT_SESSION_ID = sessions.SESSION_ID");
			
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

		////get all users guids and emails
		$stmt = $db->query("SELECT FIRSTNAME, SURNAME, EMAIL, GUID, SPOUSE_GUID FROM User_Profiles WHERE IS_TEST_USER = 0 AND INACTIVE = 0");
		$allusers_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
	////Lazy Missios list (emails of users with no processed tmn submitted in the last 6 months (178 days) for them or their spouse)
		//$sql =  "SELECT EMAIL FROM `User_Profiles` WHERE GUID NOT IN (SELECT `User_Profiles`.SPOUSE_GUID FROM `User_Profiles` WHERE `User_Profiles`.GUID IN (SELECT `Tmn_Sessions`.GUID FROM `Tmn_Sessions` WHERE `Tmn_Sessions`.AUTH_SESSION_ID IN (SELECT AUTH_SESSION_ID FROM `Auth_Table` WHERE `Auth_Table`.FINANCE_RESPONSE = 'Yes') && DATEDIFF(`Tmn_Sessions`.DATE_MODIFIED, CURRENT_DATE()) < 178)) && GUID NOT IN (SELECT `Tmn_Sessions`.GUID FROM `Tmn_Sessions` WHERE `Tmn_Sessions`.AUTH_SESSION_ID IN (SELECT AUTH_SESSION_ID FROM `Auth_Table` WHERE `Auth_Table`.FINANCE_RESPONSE = 'Yes') && DATEDIFF(`Tmn_Sessions`.DATE_MODIFIED, CURRENT_DATE()) < 178) && `User_Profiles`.IS_TEST_USER = 0";
		
		
		//$stmt = $db->query("SELECT `Tmn_Sessions`.GUID FROM `Tmn_Sessions` WHERE `Tmn_Sessions`.AUTH_SESSION_ID IN (SELECT AUTH_SESSION_ID FROM `Auth_Table` WHERE `Auth_Table`.FINANCE_RESPONSE = 'Yes') && DATEDIFF(`Tmn_Sessions`.DATE_MODIFIED, CURRENT_DATE()) < 178");
		//select the guids of any user that doesn't have a current session or has a current session older than 6 months
		$stmt = $db->query("SELECT * FROM (SELECT * FROM User_Profiles WHERE IS_TEST_USER = 0 AND INACTIVE = 0) AS users LEFT JOIN Low_Account AS low ON users.FIN_ACC_NUM = low.FIN_ACC_NUM WHERE low.CURRENT_SESSION_ID IS NULL OR DATEDIFF(CURRENT_DATE(), low.TMN_EFFECTIVE_DATE) > 180");
		$lazyusers_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		//produce a list without users with tmns
		$lazyusers = array();
		foreach ($lazyusers_raw as $user) {
			$lazyusers[$user['GUID']] = array('name' => $user['FIRSTNAME'].' '.$user['SURNAME'], 'email' => $user['EMAIL']);
		}
		//fb($lazyusers);
		
		//produce the bcc - names and emails of lazy users
		$lazy_m_email_bcc = '"';
		foreach ($lazyusers as $guid => $userdetails) {
			$lazy_m_email_bcc .= "'".$userdetails['name']."' <".$userdetails['email'].">, ";
		}
		$lazy_m_email_bcc = substr($lazy_m_email_bcc, 0, strlen($lazy_m_email_bcc) - 2);
		$lazy_m_email_bcc .= '"';
		//if ($DEBUG) {fb($lazy_m_email_bcc);}
		
		//Lazy email to
		$lazy_m_email_to = '""';
		//lazy email from
		$lazy_m_email_from = '"mc_admin@ccca.org.au"';
		//lazy email subject
		$lazy_m_email_subject = '"You need to do a TMN!"';
		//lazy email body
		$lazy_m_email_body = '"Our records show that you havent done a TMN in the last 6 months. We require all missionaries to complete one for each financial year.%0A%0aPlease follow the link below to complete your TMN online.%0Ahttp://mportal.ccca.org.au/TMN%0A%0aThanks,%0AMember Care"';
		
	////Lazy Authorisers
		$stmt = $db->query("SELECT GUID, FIRSTNAME, SURNAME, EMAIL FROM `User_Profiles` WHERE GUID IN (SELECT AUTH_LEVEL_1 FROM `Auth_Table` WHERE (AUTH_LEVEL_1 != '') && (LEVEL_1_RESPONSE = 'Pending') && (DATEDIFF(CURRENT_DATE(), USER_TIMESTAMP) > 14)) OR GUID IN (SELECT AUTH_LEVEL_2 FROM `Auth_Table` WHERE (AUTH_LEVEL_2 != '') && (LEVEL_2_RESPONSE = 'Pending') && (DATEDIFF(CURRENT_DATE(), USER_TIMESTAMP) > 14)) OR GUID IN (SELECT AUTH_LEVEL_3 FROM `Auth_Table` WHERE (AUTH_LEVEL_3 != '') && (LEVEL_3_RESPONSE = 'Pending') && (DATEDIFF(CURRENT_DATE(), USER_TIMESTAMP) > 14))");
		$lazyauth_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
		//if ($DEBUG){fb($lazyauth_raw);}
		
		//process the assoc array, and remove duplicates
		foreach ($lazyauth_raw as $user) {
			$lazyauth[$userdetails['GUID']] = array('name' => $user['FIRSTNAME'].' '.$user['SURNAME'], 'email' => $user['EMAIL']);	//copy every user
		}
		
		//produce the bcc - names and emails of lazy authorisers
		$lazy_a_email_bcc .= '"';
		foreach ($lazyauth as $userdetails) {
			$lazy_a_email_bcc .= "'".$userdetails['name']."' <".$userdetails['email'].">, ";
		}
		$lazy_a_email_bcc = substr($lazy_a_email_bcc, 0, strlen($lazy_a_email_bcc) - 2);
		$lazy_a_email_bcc .= '"';
		//if ($DEBUG){fb($lazy_a_email_bcc);}
		
		//Lazy email to
		$lazy_a_email_to = '""';
		//lazy email from
		$lazy_a_email_from = '"mc_admin@ccca.org.au"';
		//lazy email subject
		$lazy_a_email_subject = '"You need to approve a TMN!"';
		//lazy email body
		$lazy_a_email_body = '"Our records show that there has been a TMN waiting for your approval for longer than 2 weeks. %0A%0aPlease follow the link below to approve or reject it, so it can be processed.%0Ahttp://mportal.ccca.org.au/TMN/tmn-authviewer.php%0A%0aThanks,%0AMember Care"';
		
		
	
		
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
						var G_LAZY_M_EMAIL_TO = ' . 		$lazy_m_email_to . ';
						var G_LAZY_M_EMAIL_BCC = ' . 		$lazy_m_email_bcc . ';
						var G_LAZY_M_EMAIL_FROM = ' . 		$lazy_m_email_from . ';
						var G_LAZY_M_EMAIL_SUBJECT = ' . 	$lazy_m_email_subject . ';
						var G_LAZY_M_EMAIL_BODY = ' . 		$lazy_m_email_body . ';
						
						var G_LAZY_A_EMAIL_TO = ' . 		$lazy_a_email_to . ';
						var G_LAZY_A_EMAIL_BCC = ' . 		$lazy_a_email_bcc . ';
						var G_LAZY_A_EMAIL_FROM = ' . 		$lazy_a_email_from . ';
						var G_LAZY_A_EMAIL_SUBJECT = ' . 	$lazy_a_email_subject . ';
						var G_LAZY_A_EMAIL_BODY = ' . 		$lazy_a_email_body . ';
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
