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
	
			//for a create request take the data and put it in the database
			if ($crud == 'c') {
				if (isset($data_string)) {
					//parse json
					$data_array		= json_decode($data_string, true);
					//make sure that there is no id set
					if (isset($data_array['session_id'])) {unset($data_array['session_id']);}
					//create an empty session
					$new_session	= new TmnCrudSession($LOGFILE);
					//load the session with data
					$new_session->loadDataFromAssocArray($data_array);
					//set the owner to be the user that is currently logged in
					$new_session->setOwner($tmn->getUser());
					
					fb($data_array);
					fb($new_session);
					
					//create the session in the database
					$id = $new_session->create();
					
					//return the id of the new session
					$response = array("success"=>true,"data"=>array("session_id"=>$id));
				} else {
					//if there is no data return success false
					$response = array("success"=>false);
				}
				
				//echo response
				echo json_encode($response);
			
			//for a retrieve request take the data array (which will contain only the session_id) and use it to pull the session from the database
			} elseif ($crud == 'r') {
				
				if (isset($data_string)) {
					$data_array		= json_decode($data_string, true);
					$session	= new TmnCrudSession($LOGFILE);
					$session->loadDataFromAssocArray($data_array);
					
					$session->retrieve();
					
					$response = array("success"=>true,"data"=>$session->produceAssocArray());
				} else {
					$response = array("success"=>false);
				}
				
				echo json_encode($response);
				
			//for an update request take the data and update the sessions row in the database
			} elseif ($crud == 'u') {
				
				if (isset($data_string)) {
					$data_array		= json_decode($data_string, true);
					$session	= new TmnCrudSession($LOGFILE);
					$session->loadDataFromAssocArray($data_array);
					
					$session->update();
					
					$response = array("success"=>true);
				} else {
					$response = array("success"=>false);
				}
				
				echo json_encode($response);
				
			//for a delete request take the data array (which will contain only the session_id) and use it to remove the session from the database
			} elseif ($crud == 'd') {
				
				if (isset($data_string)) {
					$data_array		= json_decode($data_string, true);
					$session	= new TmnCrudSession($LOGFILE);
					$session->loadDataFromAssocArray($data_array);
					
					$session->delete();
					
					$response = array("success"=>true);
				} else {
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
	fb('Invalid params');
	die(json_encode(array("success"=>false)));
}

?>