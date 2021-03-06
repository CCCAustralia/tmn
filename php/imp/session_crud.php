<?php

include_once('../classes/Tmn.php');
include_once('../classes/TmnCrudSession.php');
include_once('../classes/TmnConstants.php');


//add constants to extra data (will be appended to data before its saved)
$e_data = getVersionNumberAsArray();
//set the log path
$LOGFILE	= "../logs/session_crud.log";

//check that there is a mode
if (isset($_POST['mode'])) {
	
	try {
		
		$tmn		= new Tmn($LOGFILE);
		$user		= $tmn->getUser();
		$spouseUser	= $user->getSpouse();
		
		//add user data to extra data
		if ($spouseUser != null) {
			$spouseArray = array();
			foreach ($spouseUser->produceAssocArray() as $key=>$value) {
				$spouseArray["s_" . $key]	= $value;
			}
			$e_data		= array_merge($spouseArray, $e_data);
		}
		
		$e_data		= array_merge($user->produceAssocArray(), $e_data);
		
		$extra_data	= array();
		//make sure all keys in extra data are lowercase
		foreach ($e_data as $key=>$value) {
			if ($key == 'fin_acc_num') {
				$extra_data['fan']				= $value;
			} elseif ($key == 'days_per_week') {
				//change days per week from index to value
				$extra_data['days_per_wk']		= $value + 1;
			} elseif ($key == 's_days_per_week') {
				//change days per week from index to value
				$extra_data['s_days_per_wk']	= $value + 1;
			} else {
				$extra_data[strtolower($key)]	= $value;
			}
		}
		
		//make sure user is authenticated and not sending data to this script directly
		if ($tmn->isAuthenticated()) {
			
			//grab mode
			$crud		= $_POST['mode'];
			
			//grab and strip data if it exists
			if (isset($_POST['data'])) {
				if(get_magic_quotes_gpc()) {
					$data_string = stripslashes($_POST['data']);
				} else {
					$data_string = $_POST['data'];
				}
			}
			
			//grab and strip form if it exists
			if (isset($_POST['form'])) {
				if(get_magic_quotes_gpc()) {
					$form_string = stripslashes($_POST['form']);
				} else {
					$form_string = $_POST['form'];
				}
			}
	
			//for a create request take the data and put it in the database
			if ($crud == 'c') {
				if (isset($data_string) && isset($form_string)) {
					//parse json
					$form_array		= json_decode($form_string, true);
					$data_array		= json_decode($data_string, true);
					
					//when saving as date modified should be now not the creation date of the parent session
					unset($data_array['date_modified']);
					
					//create an aussie based session in the database
					if ($form_array['aussie_form'] == 'true' || $form_array['aussie_form'] == true) {
						//add personal details and version number to data
						$data_array		= array_merge($data_array, $extra_data);
						
						//create an empty session
						$new_session	= new TmnCrudSession($LOGFILE);
						//load the session with data
						$new_session->loadDataFromAssocArray($data_array);
						//set the owner to be the user that is currently logged in
						$new_session->setOwner($tmn->getUser());
						
						//create the session in the database
						$id = $new_session->create();
						
						//return the id of the new session
						$response = array("success"=>true,"data"=>array("session_id"=>$id));
					}
					
					//create an international based session in the database
					if ($form_array['overseas_form'] == 'true' || $form_array['overseas_form'] == true) {
						
						//add personal details and version number to data
						$data_array['home-assignment']			= array_merge($data_array['home-assignment'], $extra_data);
						$data_array['international-assignment']	= array_merge($data_array['international-assignment'], $extra_data);
						
						//create empty sessions
						$new_home_session			= new TmnCrudSession($LOGFILE);
						$new_international_session	= new TmnCrudSession($LOGFILE);
						
						//link the home assignment & international assignment sessions
							
						//make the home assignment using the data
							//load the session with data
						$new_home_session->loadDataFromAssocArray($data_array['home-assignment']);
							//set the owner to be the user that is currently logged in
						$new_home_session->setOwner($tmn->getUser());
							//create the session in the database
						$home_session_id = $new_home_session->create();
						
						//make the international assignment using the data
							//load the session with data
						$new_international_session->loadDataFromAssocArray($data_array['international-assignment']);
							//set the owner to be the user that is currently logged in
						$new_international_session->setOwner($tmn->getUser());
							//set the home assignement id to the id of the home assignment session that was just created
						$new_international_session->setField('home_assignment_session_id', $home_session_id);
							//create the session in the database
						$new_international_id = $new_international_session->create();
						//update the home assignment's international assignement session id to the id of the international assignment just created
						$new_home_session->setField('international_assignment_session_id', $new_international_id);
						//push that change to the database
						$new_home_session->update();
						
						$return_data						= array(
																'home-assignment'			=>	array('session_id' => $home_session_id),
																'international-assignment'	=>	array('session_id' => $new_international_id)
															);
						
						//return the id of the new session
						$response = array("success"=>true,"data"=>$return_data);
					}
					
				} else {
					//if there is no data return success false
					fb("Invalid Params");
					$response = array("success"=>false);
				}
				
				//echo response
				echo json_encode($response);
			
			//for a retrieve request take the data array (which will contain only the session_id) and use it to pull the session from the database
			} elseif ($crud == 'r') {
				
				if (isset($data_string) && isset($form_string)) {
					//parse json
					$form_array			= json_decode($form_string, true);
					$data_array			= json_decode($data_string, true);
					$inflate			= false;
					$inflationStatus	= 'not-needed';
					
					//set whether numbers should be inflated based on POST values
					if (isset($_POST['inflate'])) {
						if ($_POST['inflate'] == 'true') {
							$inflate	= true;
						}
					}
					
					//retrieve an aussie based session from the database
					if ($form_array['aussie_form'] == 'true' || $form_array['aussie_form'] == true) {
						$session	= new TmnCrudSession($LOGFILE);
						$session->loadDataFromAssocArray($data_array);
						
						$session->retrieve();
						
						if ($inflate) {
							for ($yearCount = 0; $yearCount < $session->financialYearsSinceSessionCreation(); $yearCount++) {
								$session->applyInflation();
								$inflationStatus	= 'applied';
							}
						} else {
							if ($session->financialYearsSinceSessionCreation() > 0) {
								$inflationStatus	= 'needed';
							}
						}
						
						$response = array("success"=>true,"data"=>$session->produceAssocArray(), "inflation_status"=>$inflationStatus);
					}
					
					//retrieve the overseas based sessions from the database
					if ($form_array['overseas_form'] == 'true' || $form_array['overseas_form'] == true) {
						$home_assignment_session;
						$international_assignment_session;
						
						//grab the home assignment and associated international assignment
						if ($form_array['home_assignment'] == 'true' || $form_array['home_assignment'] == true) {
							$home_assignment_session			= new TmnCrudSession($LOGFILE);
							$home_assignment_session->loadDataFromAssocArray($data_array);
							$home_assignment_session->retrieve();
							
							$international_assignment_session	= $home_assignment_session->getInternationalAssignment();
						} else {
							$international_assignment_session	= new TmnCrudSession($LOGFILE);
							$international_assignment_session->loadDataFromAssocArray($data_array);
							$international_assignment_session->retrieve();
							
							$home_assignment_session			= $international_assignment_session->getHomeAssignment();
						}
						
						if ($inflate) {
							for ($yearCount = 0; $yearCount < $international_assignment_session->financialYearsSinceSessionCreation(); $yearCount++) {
								$international_assignment_session->applyInflation();
								$inflationStatus	= 'applied';
							}
							
							for ($yearCount = 0; $yearCount < $home_assignment_session->financialYearsSinceSessionCreation(); $yearCount++) {
								$home_assignment_session->applyInflation();
								$inflationStatus	= 'applied';
							}
						} else {
							if ($international_assignment_session->financialYearsSinceSessionCreation() > 0 || $home_assignment_session->financialYearsSinceSessionCreation() > 0) {
								$inflationStatus	= 'needed';
							}
						}
							
						//construct return array
						$return_data						= array(
																'home-assignment'			=>	$home_assignment_session->produceAssocArray(),
																'international-assignment'	=>	$international_assignment_session->produceAssocArray()
															);
						
						$response = array("success"=>true,"data"=>$return_data, "inflation_status"=>$inflationStatus);
					}
					
				} else {
					//if there is no data return success false
					fb("Invalid Params");
					$response = array("success"=>false);
				}
				
				echo json_encode($response);
				
			//for an update request take the data and update the sessions row in the database
			} elseif ($crud == 'u') {
				
				if (isset($data_string) && isset($form_string)) {
					//parse json
					$form_array		= json_decode($form_string, true);
					$data_array		= json_decode($data_string, true);
					
					if ($form_array['aussie_form'] == 'true' || $form_array['aussie_form'] == true) {
						//add personal details and version number to data
						$data_array	= array_merge($data_array, $extra_data);
						
						$session	= new TmnCrudSession($LOGFILE);
						$session->loadDataFromAssocArray($data_array);
						
						$session->update();
						
						$response = array("success"=>true);
					}
					
					//update the overseas based sessions from the database
					if ($form_array['overseas_form'] == 'true' || $form_array['overseas_form'] == true) {
						
						//add personal details and version number to data
						$data_array['home-assignment']			= array_merge($data_array['home-assignment'], $extra_data);
						$data_array['international-assignment']	= array_merge($data_array['international-assignment'], $extra_data);
						
						$home_assignment_session			= new TmnCrudSession($LOGFILE);
						$international_assignment_session	= new TmnCrudSession($LOGFILE);
						
						//load their data
						$home_assignment_session->loadDataFromAssocArray($data_array['home-assignment']);
						$international_assignment_session->loadDataFromAssocArray($data_array['international-assignment']);
						
						//update these sessions
						$home_assignment_session->update();
						$international_assignment_session->update();
						
						$response = array("success"=>true);
					}
					
				} else {
					//if there is no data return success false
					fb("Invalid Params");
					$response = array("success"=>false);
				}
				
				echo json_encode($response);
				
			//for a delete request take the data array (which will contain only the session_id) and use it to remove the session from the database
			} elseif ($crud == 'd') {
				
				if (isset($data_string) && isset($form_string)) {
					//parse json
					$form_array		= json_decode($form_string, true);
					$data_array		= json_decode($data_string, true);
					
					if ($form_array['aussie_form'] == 'true' || $form_array['aussie_form'] == true) {
						$session	= new TmnCrudSession($LOGFILE);
						$session->loadDataFromAssocArray($data_array);
						
						$session->delete();
						
						$response = array("success"=>true);
					}
					
					//delete the overseas based sessions from the database
					if ($form_array['overseas_form'] == 'true' || $form_array['overseas_form'] == true) {
						$home_assignment_session			= new TmnCrudSession($LOGFILE);
						$international_assignment_session	= new TmnCrudSession($LOGFILE);
						
						//set their id's
						$home_assignment_session->loadDataFromAssocArray($data_array['home-assignment']);
						$international_assignment_session->loadDataFromAssocArray($data_array['international-assignment']);
						
						//delete these sessions
						$home_assignment_session->delete();
						$international_assignment_session->delete();
						
						$response = array("success"=>true);
					}
					
				} else {
					//if there is no data return success false
					fb("Invalid Params");
					$response = array("success"=>false);
				}
				
				echo json_encode($response);
				
			}
			
		} else {
			fb('Not Authenticated');
			die(json_encode(array("success"=>false)));
		}
	} catch (FatalException $e) {
		$tmn->exceptionHandler($e);
	} catch (Exception $e) {
		$tmn->exceptionHandler(new FatalException($e->getMessage()));
	}
	
} else {
	fb('Missing params');
	die(json_encode(array("success"=>false)));
}

?>