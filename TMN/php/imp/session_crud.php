<?php

include_once('../classes/Tmn.php');
include_once('../classes/TmnCrudSession.php');

//set the log path
$LOGFILE	= "../logs/session_crud.log";
$tmn		= new Tmn($LOGFILE);

//check that there is a mode
if (isset($_POST['mode'])) {
	
	//make sure user is authenticated and not sending data to this script directly
	if ($tmn->isAuthenticated()) {
		
		try {
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
					
					//create an aussie based session in the database
					if ($form_array['aussie_form'] == 'true' || $form_array['aussie_form'] == true) {
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
					$inflationApplied	= false;
					
					
					//retrieve an aussie based session from the database
					if ($form_array['aussie_form'] == 'true' || $form_array['aussie_form'] == true) {
						$session	= new TmnCrudSession($LOGFILE);
						$session->loadDataFromAssocArray($data_array);
						
						$session->retrieve();
						fb($session->produceAssocArray());
						for ($yearCount = 0; $yearCount < $session->financialYearsSinceSessionCreation(); $yearCount++) {
							$session->applyInflation();
							$inflationApplied	= true;
						}
						fb($session->produceAssocArray());
						$response = array("success"=>true,"data"=>$session->produceAssocArray(), "inflated"=>$inflationApplied);
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
							
							for ($yearCount = 0; $yearCount < $home_assignment_session->financialYearsSinceSessionCreation(); $yearCount++) {
								$home_assignment_session->applyInflation();
								$inflationApplied	= true;
							}
							
							$international_assignment_session	= $home_assignment_session->getInternationalAssignment();
						} else {
							$international_assignment_session	= new TmnCrudSession($LOGFILE);
							$international_assignment_session->loadDataFromAssocArray($data_array);
							$international_assignment_session->retrieve();
							
							for ($yearCount = 0; $yearCount < $international_assignment_session->financialYearsSinceSessionCreation(); $yearCount++) {
								$international_assignment_session->applyInflation();
								$inflationApplied	= true;
							}
							
							$home_assignment_session			= $international_assignment_session->getHomeAssignment();
						}
						
						//construct return array
						$return_data						= array(
																'home-assignment'			=>	$home_assignment_session->produceAssocArray(),
																'international-assignment'	=>	$international_assignment_session->produceAssocArray()
															);
						
						$response = array("success"=>true,"data"=>$return_data, "inflated"=>$inflationApplied);
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
						$session	= new TmnCrudSession($LOGFILE);
						$session->loadDataFromAssocArray($data_array);
						
						$session->update();
						
						$response = array("success"=>true);
					}
					
					//update the overseas based sessions from the database
					if ($form_array['overseas_form'] == 'true' || $form_array['overseas_form'] == true) {
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