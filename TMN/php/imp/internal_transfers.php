<?php
include_once '../classes/Tmn.php';
include_once '../classes/TmnDatabase.php';
include_once '../classes/TmnCrudSession.php';

$LOGFILE = "../logs/internal_transfers.log";

try {
		
	$tmn		= new Tmn($LOGFILE);
	
	if (isset($_POST['session']) && isset($_POST['mode'])) {
		
		if ($tmn->isAuthenticated()) {
			
			//create a database connection
			$db					= TmnDatabase::getInstance($LOGFILE);
			
			//grab data from _POST
			$session_id			= $_POST['session'];
			
			//strip transfers so they can be decoded
			if (isset($_POST['transfers'])) {
			if(get_magic_quotes_gpc()) {
				$transfers_string = stripslashes($_POST['transfers']);
			} else {
				$transfers_string = $_POST['transfers'];
			}
			}
			
			switch ($_POST['mode']){
				case 'get':
					
					try {
						//init variables
						$resultArray	= array();
						$sql	= "SELECT TRANSFER_ID, TRANSFER_NAME, TRANSFER_AMOUNT FROM `Internal_Transfers` WHERE SESSION_ID=:session_id";
						$values	= array(':session_id'=>(int)$session_id);
						
						//prepare the statement
						$stmt		= $db->prepare($sql);
						//bind and execute the statement
						$stmt->execute($values);
						
						for ($resultCount=0; $resultCount < $stmt->rowCount(); $resultCount++) {
							//grab result as an associative array
							$result						= $stmt->fetch(PDO::FETCH_ASSOC);
							$resultArray[$resultCount]	= $result;
						}
						
						//pull out transfer if it is the only one in the array
						if (count($resultArray) == 1) {
							$resultArray	= $resultArray[0];
						}
						
						$returnvalue = array('success' => true, 'transfers' => $resultArray);
						
					} catch (PDOException $e) {
						//if the SELECT didn't work, throw an exception
						throw new LightException(__CLASS__ . " Exception: " . $e->getMessage());
					}
			
					break;
				case 'add':
					
					try {
						$session			= new TmnCrudSession($LOGFILE, $session_id);
						$home_assignment_id	= $session->getField('home_assignment_session_id');
						
						//grab transfers
						$transferArray		= json_decode($transfers_string, true);
						
						//make sure $tranferArray was successfully parsed or throw an exception
						if (!isset($transferArray)) {
							throw new FatalException("Internal Transfer Exception: Transfer array couldn't be parsed when attempting an add.");
						}
						
						//check if it's a single transfer if it is make it into an array
						if (isset($transferArray['transfer_name'])) {
							$transferArray	= array($transferArray);
						}
						
						//init variables
						$resultArray	= array();
						$values	= array(':session_id' => (int)$session_id, ':transfer_name' => '', ':transfer_amount' => 0);
						$sql	= "INSERT INTO `Internal_Transfers` (SESSION_ID, TRANSFER_NAME, TRANSFER_AMOUNT) VALUES (:session_id, :transfer_name, :transfer_amount)";
							
						//start transaction so that if an error occurs we can roll it back
						$db->beginTransaction();
						
						//prepare the statement
						$stmt		= $db->prepare($sql);
						fb($transferArray);
						foreach ($transferArray as $transfer) {
							//set the values for this transfer
							$values[':transfer_name']	= $transfer['transfer_name'];
							$values[':transfer_amount']	= (int)$transfer['transfer_amount'];
							
							//bind and execute the statement
							$stmt->execute($values);
							
							//set the transfer's id now that it's been created
							$transfer['transfer_id']	= $db->lastInsertId();
						}
						
						//if this session has a home assignment do the same to the home assignment
						//this is because the user fills out the international assignment first and
						//expects the same results to appear in home assignment but not the other way
						if (isset($home_assignment_id)) {
							$values	= array(':session_id' => (int)$home_assignment_id, ':transfer_name' => '', ':transfer_amount' => 0);
							
							foreach ($transferArray as $transfer) {
								//set the values for this transfer
								$values[':transfer_name']	= $transfer['transfer_name'];
								$values[':transfer_amount']	= (int)$transfer['transfer_amount'];
								
								//bind and execute the statement
								$stmt->execute($values);
							}
						}
						
						$db->commit();
						
						//pull out transfer if it is the only one in the array
						if (count($transferArray) == 1) {
							$transferArray	= $transferArray[0];
						}
						
						$returnvalue = array('success' => true, 'transfers' => $transferArray);
						
					} catch (PDOException $e) {
						//if the SELECT didn't work, roll it back and throw an exception
						$db->rollBack();
						throw new LightException(__CLASS__ . " Exception: " . $e->getMessage());
					}
					
					break;
				case 'update':
					
					try {
						$session			= new TmnCrudSession($LOGFILE, $session_id);
						$home_assignment_id	= $session->getField('home_assignment_session_id');
						
						//grab transfers
						$transferArray		= json_decode($transfers_string, true);
						
						//make sure $tranferArray was successfully parsed or throw an exception
						if (!isset($transferArray)) {
							throw new FatalException("Internal Transfer Exception: Transfer array couldn't be parsed when attempting an update.");
						}
						
						//check if it's a single transfer if it is make it into an array
						if (isset($transferArray['transfer_name'])) {
							$transferArray	= array($transferArray);
						}
						
						//init variables
						$resultArray	= array();
						$values	= array(':transfer_id' => 0, ':transfer_name' => '', ':transfer_amount' => 0);
						$sql	= "UPDATE  `Internal_Transfers` SET TRANSFER_NAME = :transfer_name, TRANSFER_AMOUNT = :transfer_amount WHERE TRANSFER_ID = :transfer_id  ";
							
						//start transaction so that if an error occurs we can roll it back
						$db->beginTransaction();
						
						//prepare the statement
						$stmt		= $db->prepare($sql);
						
						foreach ($transferArray as $transfer) {
							//set the values for this transfer
							$values[':transfer_id']		= $transfer['transfer_id'];
							$values[':transfer_name']	= $transfer['transfer_name'];
							$values[':transfer_amount']	= $transfer['transfer_amount'];
							
							//bind and execute the statement
							$stmt->execute($values);
						}
						
						//if this session has a home assignment do the same to the home assignment
						//this is because the user fills out the international assignment first and
						//expects the same results to appear in home assignment but not the other way
						if (isset($home_assignment_id)) {
							//prepare a statement to grab the coresponding transfer in the home assignment session
							$grabSql	= "SELECT TRANSFER_ID FROM `Internal_Transfers` WHERE SESSION_ID = :session_id AND TRANSFER_NAME = :transfer_name";
							$grabValues	= array(':session_id' => (int)$home_assignment_id, ':transfer_name' => '');
							$grabStmt	= $db->prepare($grabSql);
							
							//change the session_id in the values array for the UPDATE statement
							$values	= array(':session_id' => (int)$home_assignment_id, ':transfer_name' => '', ':transfer_amount' => 0);
							
							
							foreach ($transferArray as $transfer) {
								
								//grab the transfer id of the coresponding transfer in the home assignment session
								$grabValues[':transfer_name']= $transfer['transfer_name'];
								$grabStmt->execute($grabValues);
								$transfer_id_result			= $grabStmt->fetch(PDO::FETCH_ASSOC);
								
								if ($grabStmt->rowCount() > 0) {
									//set the values for this transfer
									$values[':transfer_id']		= $transfer_id_result['TRANSFER_ID'];
									$values[':transfer_name']	= $transfer['transfer_name'];
									$values[':transfer_amount']	= $transfer['transfer_amount'];
									
									//bind and execute the statement
									$stmt->execute($values);
								}
							}
						}
						
						$db->commit();
						
						//pull out transfer if it is the only one in the array
						if (count($transferArray) == 1) {
							$transferArray	= $transferArray[0];
						}
						
						$returnvalue = array('success' => true, 'transfers' => $transferArray);
						
					} catch (PDOException $e) {
						//if the SELECT didn't work, roll it back and throw an exception
						$db->rollBack();
						throw new LightException(__CLASS__ . " Exception: " . $e->getMessage());
					}
					
					break;
				case 'remove':
					
					try {
						$session			= new TmnCrudSession($LOGFILE, $session_id);
						$home_assignment_id	= $session->getField('home_assignment_session_id');
						
						//grab transfers
						$transferArray		= json_decode($transfers_string, true);
						
						//make sure $tranferArray was successfully parsed or throw an exception
						if (!isset($transferArray)) {
							throw new FatalException("Internal Transfer Exception: Transfer array couldn't be parsed when attempting a remove.");
						}
						
						//check if it's a single transfer if it is make it into an array
						if (isset($transferArray['transfer_name'])) {
							$transferArray	= array($transferArray);
						}
						
						//init variables
						$resultArray	= array();
						$values	= array(':transfer_id' => 0);
						$sql	= "DELETE FROM `Internal_Transfers` WHERE TRANSFER_ID = :transfer_id";
							
						//start transaction so that if an error occurs we can roll it back
						$db->beginTransaction();
						
						//prepare the statement
						$stmt		= $db->prepare($sql);
						
						foreach ($transferArray as $transfer) {
							//set the values for this transfer
							$values[':transfer_id']		= $transfer['transfer_id'];
							
							//bind and execute the statement
							$stmt->execute($values);
						}
						
						//if this session has a home assignment do the same to the home assignment
						//this is because the user fills out the international assignment first and
						//expects the same results to appear in home assignment but not the other way
						if (isset($home_assignment_id)) {
							//prepare a statement to grab the coresponding transfer in the home assignment session
							$grabSql	= "SELECT TRANSFER_ID FROM `Internal_Transfers` WHERE SESSION_ID = :session_id AND TRANSFER_NAME = :transfer_name";
							$grabValues	= array(':session_id' => (int)$home_assignment_id, ':transfer_name' => '');
							$grabStmt	= $db->prepare($grabSql);
							
							//change the session_id in the values array for the UPDATE statement
							$values	= array(':session_id' => (int)$home_assignment_id, ':transfer_name' => '', ':transfer_amount' => 0);
							
							
							foreach ($transferArray as $transfer) {
								
								//grab the transfer id of the coresponding transfer in the home assignment session
								$grabValues[':transfer_name']= $transfer['transfer_name'];
								$grabStmt->execute($grabValues);
								$transfer_id_result			= $grabStmt->fetch(PDO::FETCH_ASSOC);
								
								if ($grabStmt->rowCount() > 0) {
									//set the values for this transfer
									$values[':transfer_id']		= $transfer_id_result['TRANSFER_ID'];
									
									//bind and execute the statement
									$stmt->execute($values);
								}
							}
						}
						
						$db->commit();
						
						//pull out transfer if it is the only one in the array
						if (count($transferArray) == 1) {
							$transferArray	= $transferArray[0];
						}
						
						$returnvalue = array('success' => true, 'transfers' => $transferArray);
						
					} catch (PDOException $e) {
						//if the SELECT didn't work, roll it back and throw an exception
						$db->rollBack();
						throw new LightException(__CLASS__ . " Exception: " . $e->getMessage());
					}
					
					break;
				case 'deleteall':
					
					try {
						$session						= new TmnCrudSession($LOGFILE, $session_id);
						$home_assignment_id				= $session->getField('home_assignment_session_id');
						$international_assignment_id	= $session->getField('international_assignment_session_id');
						
						//init variables
						$resultArray	= array();
						$values	= array(':session_id' => 0);
						$sql	= "DELETE FROM `Internal_Transfers` WHERE SESSION_ID = :session_id";
							
						//start transaction so that if an error occurs we can roll it back
						$db->beginTransaction();
						
						//prepare the statement
						$stmt		= $db->prepare($sql);
						
						//delete the session
						$stmt->execute($values);
						
						//delete coresponsding session (if there is one)
						if (isset($home_assignment_id)) {
							$values[':session_id']	= $home_assignment_id;
						}
						
						if (isset($international_assignment_id)) {
							$values[':session_id']	= $international_assignment_id;
						}
						
						$stmt->execute($values);
						
						$db->commit();
						
						$returnvalue = array('success' => true, 'transfers' => $transferArray);
						
					} catch (PDOException $e) {
						//if the SELECT didn't work, roll it back and throw an exception
						$db->rollBack();
						throw new LightException(__CLASS__ . " Exception: " . $e->getMessage());
					}
					
					break;
				default:
					throw new FatalException('Internal Transfer Exception: Invalid Mode');
			}
			
			//return the result
			echo json_encode($returnvalue);
			
		} else {
			throw new FatalException('Internal Transfer Exception: Not Authenticated');
		}
	} else {
		throw new FatalException('Internal Transfer Exception: Missing params');
	}

} catch (Exception $e) {
	Reporter::newInstance($LOGFILE)->exceptionHandler($e);
}

?>