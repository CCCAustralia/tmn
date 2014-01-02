<?php
include_once('../classes/Tmn.php');
include_once('../classes/TmnDatabase.php');
include_once('../classes/TmnCrudSession.php');
include_once('../classes/TmnFinanceAdminsUsersGroup.php');

//set the log path
$LOGFILE				= "../logs/authviewer.log";
$tmn					= new Tmn($LOGFILE);
$db						= TmnDatabase::getInstance($LOGFILE);
$financeAdminsUserGroup = new TmnFinanceAdminsUsersGroup();

//check that there is a mode
if (isset($_POST['mode'])) {
	
	//make sure user is authenticated and not sending data to this script directly
	if ($tmn->isAuthenticated()) {
		
		try {
			//grab mode
			$mode		= $_POST['mode'];
	
			if ($mode == "load") {

				//make a variable for the return data
				$data	= array();
				
				//sql to grab sessions for the user that is logged in
				$sessionSql = "SELECT SESSION_ID, SESSION_NAME, GUID FROM Tmn_Sessions WHERE HOME_ASSIGNMENT_SESSION_ID IS NULL AND AUTH_SESSION_ID IN (SELECT AUTH_SESSION_ID FROM Auth_Table WHERE ";
				$sessionSql .= "(AUTH_USER = :guid ".																																" && FINANCE_RESPONSE = 'Pending') || ";
				$sessionSql .= "(AUTH_LEVEL_1 = :guid ".	"&& LEVEL_1_RESPONSE = 'Pending' ".		"&& LEVEL_2_RESPONSE = 'Pending' ".		"&& LEVEL_3_RESPONSE = 'Pending'".		" && FINANCE_RESPONSE = 'Pending') || ";
				$sessionSql .= "(AUTH_LEVEL_2 = :guid ".	"&& LEVEL_1_RESPONSE = 'Yes' ".			"&& LEVEL_2_RESPONSE = 'Pending' ".		"&& LEVEL_3_RESPONSE = 'Pending'".		" && FINANCE_RESPONSE = 'Pending') || ";
				$sessionSql .= "(AUTH_LEVEL_3 = :guid ".	"&& LEVEL_1_RESPONSE = 'Yes' ".			"&& LEVEL_2_RESPONSE = 'Yes'".			"&& LEVEL_3_RESPONSE = 'Pending'".		" && FINANCE_RESPONSE = 'Pending'))";
				$sessionGuid = array(':guid' => $tmn->getAuthenticatedGuid());
				
				if ( $financeAdminsUserGroup->containsUser($tmn->getAuthenticatedGuid()) ) {
					$sessionSql = "SELECT SESSION_ID, SESSION_NAME, GUID FROM Tmn_Sessions WHERE HOME_ASSIGNMENT_SESSION_ID IS NULL AND AUTH_SESSION_ID IN (SELECT AUTH_SESSION_ID FROM Auth_Table WHERE ";
					//$sessionSql .= "(AUTH_USER IS NOT NULL ".																																" && FINANCE_RESPONSE = 'Pending') || ";
					$sessionSql .= "(AUTH_LEVEL_1 != '' ".															"&& LEVEL_1_RESPONSE = 'Yes' && FINANCE_RESPONSE = 'Pending') || ";
					$sessionSql .= "(AUTH_LEVEL_1 != '' ".	"&& AUTH_LEVEL_2 != '' ".								"&& LEVEL_1_RESPONSE = 'Yes' && LEVEL_2_RESPONSE = 'Yes' && FINANCE_RESPONSE = 'Pending') || ";
					$sessionSql .= "(AUTH_LEVEL_1 != '' ".	"&& AUTH_LEVEL_2 != '' ".	"&& AUTH_LEVEL_3 != '' ".	"&& LEVEL_1_RESPONSE = 'Yes' && LEVEL_2_RESPONSE = 'Yes' && LEVEL_3_RESPONSE = 'Yes' && FINANCE_RESPONSE = 'Pending'))";
				}
				
				//add users current session to the list
				$currentUser		= $tmn->getUser();
				$currentSessionSql	= "SELECT CURRENT_SESSION_ID FROM Low_Account WHERE FIN_ACC_NUM = :fan AND CURRENT_SESSION_ID IS NOT NULL";
				$currentUserFan		= array(':fan' => $currentUser->getFan());
				$currentStmt 		= $db->prepare($currentSessionSql);
				$currentStmt->execute($currentUserFan);
				
				//if the user has a current session add it to the list
				if ($currentStmt->rowCount() == 1) {
					
					$currentSessionRow	= $currentStmt->fetch(PDO::FETCH_ASSOC);
					
					$currentSessionRow	= array("SESSION_ID"	=> $currentSessionRow["CURRENT_SESSION_ID"],
												"SESSION_NAME"	=> "My Current TMN",
												"FIRSTNAME"		=> $currentUser->getField("firstname"),
												"SURNAME"		=> $currentUser->getField("surname"),
												"EMAIL"			=> $tmn->getEmail()
												);
												
					$data[count($data)]	= $currentSessionRow;
				}
				
				//sql for grabbing the owner info associated with each session
				$ownerSql	= "SELECT FIRSTNAME, SURNAME, EMAIL FROM User_Profiles WHERE GUID=:ownerGuid";
				$ownerGuid	= array(':ownerGuid' => '');
				
				//prepare statements for execution
				$stmt 			= $db->prepare($sessionSql);
				$ownerStmt 		= $db->prepare($ownerSql);
				
				//execute sql statment for grabbing sessions
				$stmt->execute($sessionGuid);
				
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
					$data[count($data)]	= array_merge($sessionRow, $ownerRow);
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
							$home_assignment_session			= $session;
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

?>