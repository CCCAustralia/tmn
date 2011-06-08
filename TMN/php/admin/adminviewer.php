<?php
include_once('../classes/Tmn.php');
include_once('../classes/TmnDatabase.php');
include_once('../classes/TmnCrudSession.php');
include_once('../classes/TmnConstants.php');
$financeguid = getConstants(array('FINANCE_USER'));
$financeguid = $financeguid['FINANCE_USER'];

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
				/*
				//Grab the sessions
				$sessionSql = "SELECT SESSION_ID, SESSION_NAME, GUID FROM Tmn_Sessions WHERE AUTH_SESSION_ID IN (SELECT AUTH_SESSION_ID FROM Auth_Table WHERE ";
				//$sessionSql .= "(AUTH_USER IS NOT NULL ".																																" && FINANCE_RESPONSE = 'Pending') || ";
				$sessionSql .= "(AUTH_LEVEL_1 IS NOT NULL ".																"&& LEVEL_1_RESPONSE = 'Yes' && FINANCE_RESPONSE = 'Pending') || ";
				$sessionSql .= "(AUTH_LEVEL_1 IS NOT NULL ".	"&& AUTH_LEVEL_2 IS NOT NULL ".								"&& LEVEL_1_RESPONSE = 'Yes' && LEVEL_2_RESPONSE = 'Yes' && FINANCE_RESPONSE = 'Pending') || ";
				$sessionSql .= "(AUTH_LEVEL_1 IS NOT NULL ".	"&& AUTH_LEVEL_2 IS NOT NULL ".	"&& AUTH_LEVEL_3 IS NOT NULL && LEVEL_1_RESPONSE = 'Yes' && LEVEL_2_RESPONSE = 'Yes' && LEVEL_3_RESPONSE = 'Yes' && FINANCE_RESPONSE = 'Pending'))";
				
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
				*/
				echo "Not Implemented";
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
					$response['progress']	= $session->getAuthProgressForDisplay();
					
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

?>