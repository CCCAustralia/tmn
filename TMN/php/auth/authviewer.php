<?php
include_once('../classes/Tmn.php');
include_once('../classes/TmnDatabase.php');
include_once('../classes/TmnCrudSession.php');

//set the log path
$LOGFILE	= "../logs/authviewer.log";
$tmn		= new Tmn($LOGFILE);
$db			= TmnDatabase::getInstance($LOGFILE);

//check that there is a mode
if (isset($_POST['mode'])) {
	
	//make sure user is authenticated and not sending data to this script directly
	if ($tmn->isAuthenticated()) {
		
		try {
			//grab mode
			$mode		= $_POST['mode'];
	
			if ($mode == "load") {
				//sql to grab sessions for the user that is logged in
				$sessionSql = "SELECT SESSION_ID, SESSION_NAME, GUID FROM Tmn_Sessions WHERE AUTH_SESSION_ID IN (SELECT AUTH_SESSION_ID FROM Auth_Table WHERE ";
				$sessionSql .= "(AUTH_USER = :guid ".																																" && FINANCE_RESPONSE = 'Pending') || ";
				$sessionSql .= "(AUTH_LEVEL_1 = :guid ".											"&& LEVEL_2_RESPONSE = 'Pending' ".		"&& LEVEL_3_RESPONSE = 'Pending'".		" && FINANCE_RESPONSE = 'Pending') || ";
				$sessionSql .= "(AUTH_LEVEL_2 = :guid ".	"&& LEVEL_1_RESPONSE = 'Yes' ".													"&& LEVEL_3_RESPONSE = 'Pending'".		" && FINANCE_RESPONSE = 'Pending') || ";
				$sessionSql .= "(AUTH_LEVEL_3 = :guid ".	"&& LEVEL_1_RESPONSE = 'Yes' ".			"&& LEVEL_2_RESPONSE = 'Yes'".													" && FINANCE_RESPONSE = 'Pending'))";
				$sessionGuid = array(':guid' => $tmn->getAuthenticatedGuid());
				
				//sql for grabbing the owner info associated with each session
				$ownerSql	= "SELECT FIRSTNAME, SURNAME, EMAIL FROM User_Profiles WHERE GUID=:ownerGuid";
				$ownerGuid	= array(':ownerGuid' => '');
				
				//prepare statements for execution
				$stmt 			= $db->prepare($sessionSql);
				$ownerStmt 		= $db->prepare($ownerSql);
				
				//execute sql statment for grabbing sessions
				$stmt->execute($sessionGuid);
				$data	= array();
				
				for ($sessionCount = 0; $sessionCount < $stmt->rowCount(); $sessionCount++) {
					//grab values
					$sessionRow					= $stmt->fetch(PDO::FETCH_ASSOC);
					$ownerGuid[':ownerGuid']	= $sessionRow['GUID'];
					
					//execute owner sql statement with those values
					$ownerStmt->execute($ownerGuid);
					
					//grab the owner data
					$ownerRow					= $ownerStmt->fetch(PDO::FETCH_ASSOC);
					
					//remove the guid
					unset($sessionRow['GUID']);
					
					//let the return object be the combination of the two sets of data
					$data[$sessionCount]	= array_merge($sessionRow, $ownerRow);
				}
				
				$return_data = array('data' => $data);
				
				echo json_encode($return_data);
			} elseif ($mode == 'get') {
				
				if (isset($_POST['session'])) {
					
					$session_id	= $_POST['session'];
					
					//retrieve an session from the database
					$session	= new TmnCrudSession($LOGFILE, $session_id);
					//grab these keys to see if there is an associated session to load
					$international_assignment_session_id	= $session->getField('international_assignment_session_id');
					$home_assignment_session_id				= $session->getField('home_assignment_session_id');
					$international_assignment_session;
					$home_assignment_session;
					
					//retrieve the overseas based sessions from the database
					if ($international_assignment_session_id != null || $home_assignment_session_id != null) {
						
						//grab the home assignment and associated international assignment
						if ($home_assignment_session_id != null) {
							$international_assignment_session	= $session;
							$home_assignment_session			= $international_assignment_session->getHomeAssignment();
						} else {
							$home_assignment_session			= new TmnCrudSession($LOGFILE, $international_assignment_session_id);
							$international_assignment_session	= $home_assignment_session->getInternationalAssignment();
						}
						
						//construct return array for overseas session
						$return_data						= array(
																'home-assignment'			=>	$home_assignment_session->produceAssocArrayForDisplay(),
																'international-assignment'	=>	$international_assignment_session->produceAssocArrayForDisplay()
															);
						
					//if its not an overseas session construct a return array for an aussie session
					} else {
						$return_data						= array('aussie-based'					=>	$session->produceAssocArrayForDisplay());
					}
					
					$response				= array("success"=>true);
					$response['data']		= $return_data;
					$response['progress']	= $session->getOverallProgress();
					$response['authoriser']	= $session->getAuthoriserDetailsForUser($tmn->getUser());
					
				} else {
					//if there is no data return success false
					fb("Invalid Params");
					$response = array("success"=>false);
				}
				
				echo json_encode($response);
				
			//for an update request take the data and update the sessions row in the database
			}
			
		} catch (FatalException $e) {
			$tmn->exceptionHandler($e);
		} catch (Exception $e) {
			$tmn->exceptionHandler(new FatalException($e->getMessage()));
		}
		
	} else {
		fb('Not Authenticated');
		die(json_encode(array("success"=>false)));
	}
	
} else {
	fb('Missing params');
	die(json_encode(array("success"=>false)));
}






/*

if($DEBUG) require_once("../../lib/FirePHPCore/fb.php");
include_once "../dbconnect.php";
include_once "../logger.php";
include_once("../FinancialSubmitter.php");
include_once("../classes/TmnCrudSession.php");


//Authenticate the user in GCX with phpCAS
include_once('../../lib/cas/cas.php');		//include the CAS module
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('{success: false}');

if($DEBUG) ob_start();		//enable firephp logging

if (isset($_SESSION['phpCAS'])) {
	$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
	$xmlobject = new SimpleXmlElement($xmlstr);
	$guid = $xmlobject->authenticationSuccess->attributes->ssoGuid;
}

$connection = db_connect();
$LOGFILE = "logs/tmn-viewer-backend.log";

if (!isset($_POST["mode"]))
	die('{"success": false}');

if ($_POST["mode"] == "load") {
	//if ($DEBUG) {$guid="test";}
	$rows = "SELECT SESSION_ID, SESSION_NAME FROM Tmn_Sessions WHERE AUTH_SESSION_ID IN (SELECT AUTH_SESSION_ID FROM Auth_Table WHERE ";
	$rows .= "(AUTH_USER = '"	.$guid."' ".																															" && FINANCE_RESPONSE = 'Pending') || ";
	$rows .= "(AUTH_LEVEL_1 = '".$guid."' ".											"&& LEVEL_2_RESPONSE = 'Pending' ".		"&& LEVEL_3_RESPONSE = 'Pending'".		" && FINANCE_RESPONSE = 'Pending') || ";
	$rows .= "(AUTH_LEVEL_2 = '".$guid."' ".	"&& LEVEL_1_RESPONSE = 'Yes' ".													"&& LEVEL_3_RESPONSE = 'Pending'".		" && FINANCE_RESPONSE = 'Pending') || ";
	$rows .= "(AUTH_LEVEL_3 = '".$guid."' ".	"&& LEVEL_1_RESPONSE = 'Yes' ".			"&& LEVEL_2_RESPONSE = 'Yes'".													" && FINANCE_RESPONSE = 'Pending'))";
	//TODO: USE PREPAREDSTATEMENTS
	if($DEBUG) {fb($rows);}
	$rows = mysql_query($rows);
	$returndata = "";
	
	for ($i = 0; $i < mysql_num_rows($rows); $i++) {
		$r = mysql_fetch_assoc($rows);
		$returndata .= "{";
		foreach ($r as $k=>$v) {
			$returndata .= "\"".$k."\": \"".$r[$k]."\",";
		}
		$returndata = trim($returndata, ",");
		$returndata .= "},";
	}
	
	$returndata = trim($returndata,",");
	
	echo '{"data":['.$returndata.'] }';
} else if ($_POST["mode"] == "get") {
	
	$crudsession = new TmnCrudSession("../logs/authviewer.log", (int)$_REQUEST["session"]);
	
	$crudsession->retrieve();
	$cruddata = $crudsession->produceJsonForDisplay();
	fb("cruddata:");
	fb($cruddata);
	$cruddata = (array)json_decode($cruddata);
	
	//TODO: ADD AUTH DATA FROM AUTH_TABLE(TO BE MODIFIED, WITH SUBMITTER)
	//$cruddata['session'] = $cruddata['session_id'];
	$returndata = array("success" => true, "tmn_data" => array('aussie-based'=>$cruddata));
	
	echo json_encode($returndata);
	
} else {			//if its an invalid mode tell the front end it failed
	echo '{"success": false}';
}
*/

?>