<?php
include_once('php/classes/Tmn.php');
include_once('php/classes/TmnDatabase.php');
include_once('php/classes/email.php');
include_once('php/classes/TmnConstants.php');
$constants = getConstants(array("VERSIONNUMBER"));
$LOGFILE		= 'php/logs/index.log';
$DEBUG			= 1;
$NEWVERSION		= 1;
//todo: version updated by ftp script
$VERSIONNUMBER	= "2-2-7";

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
			
		//ouput tmn page
		echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
				<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />';
		
				if ($DEBUG) {
					echo '<link rel="stylesheet" type="text/css" href="lib/resources/css/loading.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/resources/css/ext-all.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/customclasses/statusbar/css/statusbar.css'.$force_reload.'" /><link rel="stylesheet" type="text/css" href="lib/resources/css/customstyles.css'.$force_reload.'" />';
				} else {
					echo '<link rel="stylesheet" type="text/css" href="lib/resources/css/tmn-all.css'.$force_reload.'" />';
				}
				
				echo '<title>TMN</title>
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
					echo '<script type="text/javascript" src="lib/customclasses/DateRangeValidationType.js'.$force_reload.'"></script>
					<script type="text/javascript" src="lib/customclasses/statusbar/StatusBar.js'.$force_reload.'"></script>
					<script type="text/javascript" src="lib/customclasses/statusbar/ValidationStatus.js'.$force_reload.'"></script>
					<script type="text/javascript" src="lib/customclasses/Printer-all.js'.$force_reload.'"></script>
					<script type="text/javascript" src="lib/customclasses/Ext.ux.IconCombo.js'.$force_reload.'"></script>';
				} else {
					echo '<script type="text/javascript" src="lib/customclasses/custom-libraries-all.js'.$force_reload.'"></script>';
				}
				
				echo '<script type="text/javascript">
						document.getElementById("loading-message").innerHTML = "Loading TMN Form...";
					</script>';
				
				if ($DEBUG) {
					echo '<script type="text/javascript" src="ui/AuthorisationPanel.js'.$force_reload.'"></script>
					<script type="text/javascript" src="ui/SummaryPanel.js'.$force_reload.'"></script>
					<script type="text/javascript" src="ui/PrintForm.js'.$force_reload.'"></script>
					<script type="text/javascript" src="ui/InternalTransfers.js'.$force_reload.'"></script>
					<script type="text/javascript" src="ui/FinancialDetailsForm.js'.$force_reload.'"></script>
					<script type="text/javascript" src="ui/PersonalDetailsForm.js'.$force_reload.'"></script>
					<script type="text/javascript" src="ui/TmnView.js'.$force_reload.'"></script>
					<script type="text/javascript" src="ui/TmnController.js'.$force_reload.'"></script>';
				} else {
					echo '<script type="text/javascript" src="ui/tmn-all.js'.$force_reload.'"></script>';
				}
		
				echo '<center>
					<div id="tmn-cont"></div>
				</center>
				<!-- Fields required for history management -->
				<form id="history-form" class="x-hidden">
					<input type="hidden" id="x-history-field" />
					<iframe id="x-history-frame"></iframe>
				</form>
			</body>
		</html>';
	} else {
		
		$stmt		= $db->query("SELECT GUID, EMAIL FROM `User_Profiles` WHERE EMAIL='" . $tmn->getEmail() . "'");
		$autoRego	= false;
		
		if ($stmt->rowCount() == 1) {
			$result	= $stmt->fetch(PDO::FETCH_ASSOC);
			
			if ($result['GUID'] == null) {
				//generate random unique 13 char varification code
				$vcode	= uniqid();
				//update table with number
				$stmt		= $db->query("UPDATE `User_Profiles` SET GUID='" . $vcode . "' WHERE EMAIL='". $tmn->getEmail() . "'");
				
				//if update worked
				if ($stmt) {
					//send email
					mail($tmn->getEmail(), "TMN Registration Varification", 'To varify your account and start your TMN click this link <a href="http://mportal.ccca.org.au/TMN/security_scan.php?vcode=' . $vcode . '">http://mportal.ccca.org.au/TMN/security_scan.php?vcode=' . $vcode . '</a>.', "From: CCCA Tech Team");
					//tell user what to do
					echo 	'<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
							<html>
								<head>
									<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
									<style type="text/css">
										.body-look{
											padding:10px;
											border-color: #8db2e3;
											background-color: #deecfd;
											font: normal 14px tahoma,arial,helvetica;
											color: #416aa3;
										}
										.title-look{
											padding:6px;
											background-image: url(lib/resources/images/default/panel/top-bottom.gif);
											color:#15428b;
											font:bold 14px tahoma,arial,verdana,sans-serif;
										}
									</style>
									<title>New User!</title>
								</head>
								<body>
									<center>
										<div class="title-look" style="position:relative;left:20px;width:608px;text-align:left;">New User!</div>
										<div class="body-look" style="position:relative;left:20px;width:600px;">We have detected that you are new to our system.<br />
											An email has been sent to ' . $tmn->getEmail() . ' with a varification link.<br />
											Click on that link and you will be able to do your TMN.
										</div>
									</center>
								</body>
							</html>';
					
					$autoRego	= true;
				}
			}
			
			//if they get to this spot and they have a varification code for a guid, clear it and let the manual rego page show
			if (strlen($result['GUID']) == 13) {
				$stmt		= $db->query("UPDATE `User_Profiles` SET GUID=NULL WHERE EMAIL='". $tmn->getEmail() . "' AND FIN_ACC_NUM > 0");
			}
		}
		
		//if auto rego hasn't worked show the manual rego page
		if (!$autoRego) {
			echo	'<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
					<html>
						<head>
							<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
							<style type="text/css">
								.body-look{
									padding:10px;
									border-color: #8db2e3;
									background-color: #deecfd;
									font: normal 14px tahoma,arial,helvetica;
									color: #416aa3;
								}
								.title-look{
									padding:6px;
									background-image: url(lib/resources/images/default/panel/top-bottom.gif);
									color:#15428b;
									font:bold 14px tahoma,arial,verdana,sans-serif;
								}
							</style>
							<title>User Not Found!</title>
						</head>
						<body>
							<center>
								<div class="title-look" style="position:relative;left:20px;width:608px;text-align:left;">User Not Found!</div>
								<div class="body-look" style="position:relative;left:20px;width:600px;">You where not found in our system.<br />
									If you think you should be able to submit a TMN then register your details for processing.<br />
									Our Security checks are usually take One buisness day to complete.
								</div>
							</center>
							
							<br />
							
							<div class="title-look">Submit Details for Registration</div>
							<form class="body-look" name="security_scan" action="security_scan.php?ticket=' . $_REQUEST['ticket'] . '" method="post">
								
								<label for="email">The Key Email Address: </label>
								<input type="text" name="email" value="' . $tmn->getEmail() . '" style="position:relative;left:70px;" readonly /><br />
								<span>(this is how we will contact you, if it is wrong go to <a href="https://thekey.me/cas/service/selfservice?execution=e3s1">https://thekey.me/cas/service/selfservice?execution=e3s1</a> and update it. Then try to register again.)</span><br /><br />
								<label for="fan">Financial Account Number: </label><input type="text" name="fan" style="position:relative;left:26px;" /> <br />
								<input type="submit" value="Submit" />
							</form>
						</body>
					</html>';
		}
	}
} catch (Exception $e) {
	echo 'Authentication failed due to Database Error. Please contact <a href="tech.team@ccca.org.au">tech.team@ccca.org.au</a>.';
}


function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}
?>
