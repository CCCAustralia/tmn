<?php

$DEBUG = 1;
$NEWVERSION = 0;
$BUILDNUMBER = "current_build_number_will_be_inserted_by_upload_script";

if ($DEBUG) {
	$force_debug = "-debug";
} else {
	$force_debug = "";
}

if ($NEWVERSION){
	$force_reload = "?" . $BUILDNUMBER;
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
		
		//these are included here because they require the user to have already authenticated
		include_once('php/FinancialProcessor.php');
		include_once('php/FinancialSubmitter.php');
		
		//if a session has been set to load
		if (isset($_REQUEST['session']) || isset($_REQUEST['isession'])) {
			
			//grab the data from the Resquest valiables
			if (isset($_REQUEST['session'])) {
				$session_id	= $_REQUEST['session'];
				$infalte	= false;
			}
			
			if (isset($_REQUEST['isession'])) {
				$session_id	= $_REQUEST['isession'];
				$infalte	= true;
			}
			
			//load the session
			$session = new TmnCrudSession($LOGFILE,$session_id);
			
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
					
					if ($infalte) {
						//inflate values
						for ($yearCount = 0; $yearCount < $session->financialYearsSinceSessionCreation(); $yearCount++) {
							$session->applyInflation();
						}						
					}
					
					//reprocess data
					$data	= reprocessData($session->produceAssocArray(), true, false, false);
					
					//put together the data packet for the front end to render
					$response	= array('aussie-based' => $data);
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
					
					if ($infalte) {
						//inflate values for international assignment
						for ($yearCount = 0; $yearCount < $ia_session->financialYearsSinceSessionCreation(); $yearCount++) {
							$ia_session->applyInflation();
						}
						
						//inflate values for home assignment
						for ($yearCount = 0; $yearCount < $ha_session->financialYearsSinceSessionCreation(); $yearCount++) {
							$ha_session->applyInflation();
						}
						
					}
					
					//reprocess data
					$ia_data		= reprocessData($ia_session->produceAssocArray(), false, true, false);
					$ha_data		= reprocessData($ha_session->produceAssocArray(), false, true, true);

						//put together the data packet for the front end to render
					$response	= array(
									'international-assignment'	=> $ia_data,
									'home-assignment'			=> $ha_data
								);
					$isOverseas	= "true";
					
				}
				
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
							document.getElementById("loading-message").innerHTML = "Loading TMN Viewer...";
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
	
	if (isset($_REQUEST['session'])) {
		$session_id	= $_REQUEST['session'];
	}
	
	if (isset($_REQUEST['isession'])) {
		$session_id	= $_REQUEST['isession'];
	}
	
	Tmn::showErrorPage("A Database error occured for session " . $session_id . " while trying to load the TMN viewer.");
}

function reprocessData($data, $aussie_form, $overseas_form, $home_assignment) {
	
	//format array so that it is in the form expected for processing
	$d_array							= $data;
	$process_array						= array();
	foreach ($d_array as $key => $value) {
		$process_array[strtoupper($key)]= $value;
	}
	
	//add extra fields
	$process_array['session']			= $d_array['session_id'];
	$process_array['aussie_form']		= $aussie_form;
	$process_array['overseas_form']		= $overseas_form;
	$process_array['home_assignment']	= $home_assignment;
	$process_array['spouse']			= (isset($process_array['S_FIRSTNAME']) && $process_array['S_FIRSTNAME'] != '') ? true : false;
	$process_array['overseas']			= $process_array['overseas_form'];
	
	//process data
	$finance_processor					= new FinancialProcessor($process_array, $DEBUG);
	$d_array							= json_decode($finance_processor->process(), true);
	
	if ($d_array['success'] == 'false') {
		Tmn::showErrorPage("Session " . $process_array['session'] . " does not have enough data to be shown by the TMN viewer.");
		die();
	}
	
	//format array so that it is in the form expected for submitting
	$submit_array						= $d_array['financial_data'];
	//add extra fields
	$submit_array['session']			= $process_array['session'];
	$submit_array['aussie_form']		= $process_array['aussie_form'];
	$submit_array['overseas_form']		= $process_array['overseas_form'];
	$submit_array['home_assignment']	= $process_array['home_assignment'];
	$submit_array['spouse']				= $process_array['spouse'];
	$submit_array['overseas']			= $process_array['overseas_form'];
	
	
	//submit data
	$finance_submitter					= new FinancialSubmitter($submit_array, $DEBUG);
	$submit_array						= json_decode($finance_submitter->submit(), true);
	
	if ($submit_array['success'] == 'false') {
		Tmn::showErrorPage("Session " . $process_array['session'] . " does not have enough data to be shown by the TMN viewer.");
		die();
	}
	
	return $submit_array['tmn_data'];
}

?>