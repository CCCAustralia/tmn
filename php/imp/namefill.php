<?php
/**
 * namefill.php - takes 'mode': a POST variable
 * @param mode: the authlevel of the list of users you wish to retrieve
 * 
 * returns a json packet with the firstname, surname and ministry.
 */

include_once('../classes/Tmn.php');
include_once('../classes/TmnSessionComboLoader.php');
include_once('../dbconnect.php');
db_connect();



//set the log path
$LOGFILE = "../logs/namefill.log";

if (isset($_REQUEST['mode'])) {
	
	try {
		
		////Get User
		$tmn = new Tmn($LOGFILE);
		$user = $tmn->getUser();
		
		if ($tmn->isAuthenticated()) {

			//initialise variables for the script
			$mode				= $_REQUEST['mode'];
			$returnArray		= array();
			$listOfLevel1Users	= array();
			$listOfLevel2Users	= array();
			$listOfLevel3Users	= array();
			$listOfAllUsers		= array();
			
			////Get all users from User_Profiles table
			$sql				= "SELECT ID, GUID, FIRSTNAME, SURNAME, MINISTRY, FIN_ACC_NUM, AUTH_LEVEL FROM `User_Profiles` WHERE ID IS NOT NULL AND IS_TEST_USER = 0 AND INACTIVE = 0";
			$result				= mysql_query($sql);
			
			////Sort the users into appropriate lists
			for ($userCount = 0; $userCount < mysql_num_rows($result); $userCount++) {
				$currentUser = mysql_fetch_assoc($result);
				
				//add current user to list of all users
					$listOfAllUsers[$currentUser['GUID']]		= $currentUser;
				
				//if current user has level 1 authority add them to level 1 list
				if ($currentUser['AUTH_LEVEL'] == 1) {
					
					$listOfLevel1Users[$currentUser['GUID']]	= $currentUser;
					
				//if current user has level 2 authority add them to level 2 list
				} else if ($currentUser['AUTH_LEVEL'] == 2) {
					
					$listOfLevel2Users[$currentUser['GUID']]	= $currentUser;
					
				//if current user has level 3 authority add them to level 3 list
				} else if ($currentUser['AUTH_LEVEL'] == 3) {
					
					$listOfLevel3Users[$currentUser['GUID']]	= $currentUser;
					
				}
			}
			
			
			if ($mode == 'all') {
				
				$returnArray = $listOfAllUsers;
				
			} else {
			
				//generate different sets of lists depending on what level of authorisation the user has
				switch ($user->getField("auth_level")) {
					
					//if the user is just a level 1 then give them the standard lists
					case 1:
						
						//check which list is being requested
						switch ($mode) {
							
							//for level 1 generate a list of all level 1 users in the user's ministry + all level 2 users in the user's minsitry + all level 3 users
							case 'level_1':
								
								foreach ($listOfAllUsers as $guid => $currentUser) {
									
									if (	($currentUser['AUTH_LEVEL'] == 1 && $currentUser['MINISTRY'] == $user->getField('ministry'))
										||	($currentUser['AUTH_LEVEL'] == 2 && $currentUser['MINISTRY'] == $user->getField('ministry'))
										||	($currentUser['AUTH_LEVEL'] == 3)	) {
											
										$returnArray[$guid] = $currentUser;
										
									}
									
								}
								
								break;
								
							//for level 2 generate a list of all level 2 users in the user's minsitry + all level 3 users
							case 'level_2':
								
								foreach ($listOfAllUsers as $guid => $currentUser) {
									
									if (	($currentUser['AUTH_LEVEL'] == 2 && $currentUser['MINISTRY'] == $user->getField('ministry'))
										||	($currentUser['AUTH_LEVEL'] == 3)	) {
											
										$returnArray[$guid] = $currentUser;
										
									}
									
								}
								
								break;
								
							//for level 3 generate a list of all level 3 users
							case 'level_3':
								
								$returnArray	= $listOfLevel3Users;
								
								break;
								
							//by default do nothing
							default:
								break;
							
						}
						
						break;
						
					//if the user is level 2 then make level 1 & 2 their peers and level 3 their superiors
					case 2:
						
						//check which list is being requested
						switch ($mode) {
							
							//for level 1 generate a list of level 2 users in user's ministry + level 3 authorisers 
							case 'level_1':
								
								foreach ($listOfLevel2Users as $guid => $currentUser) {
									
									if (	$currentUser['MINISTRY'] == $user->getField('ministry')	) {
											
										$returnArray[$guid] = $currentUser;
										
									}
									
								}
								
								$returnArray	= array_merge($returnArray, $listOfLevel3Users);
								//$returnArray	= array_merge($listOfLevel2Users, $listOfLevel3Users);
								
								break;
								
							//for level 2 generate a list of level 2 users in user's ministry + level 3 authorisers 
							case 'level_2':
								
								foreach ($listOfLevel2Users as $guid => $currentUser) {
									
									if (	$currentUser['MINISTRY'] == $user->getField('ministry')	) {
											
										$returnArray[$guid] = $currentUser;
										
									}
									
								}
								
								$returnArray	= array_merge($returnArray, $listOfLevel3Users);
								//$returnArray	= array_merge($listOfLevel2Users, $listOfLevel3Users);
								
								break;
								
							//for level 3 generate a list of
							case 'level_3':
								
								$returnArray	= $listOfLevel3Users;
								
								break;
								
							//by default do nothing
							default:
								break;
							
						}
						
						break;
						
					//if the user is level 3 then make level 1, 2 & 3 all lists of their peers
					case 3:
						
						//check which list is being requested
						switch ($mode) {
							
							//for level 1 generate a list of
							case 'level_1':
								
								$returnArray	= $listOfLevel3Users;
								
								break;
								
							//for level 2 generate a list of
							case 'level_2':
								
								$returnArray	= $listOfLevel3Users;
								
								break;
								
							//for level 3 generate a list of
							case 'level_3':
								
								$returnArray	= $listOfLevel3Users;
								
								break;
								
							//by default do nothing
							default:
								break;
							
						}
						
						break;
						
					//default should be to return an error message
					default:
						
						die(json_encode(array('success' => false, 'alert' => "Invalid Parameters.")));
						
						break;
					
				}
				
			}
				
		////Remove the user from the returned list
			unset($returnArray[$user->getGuid()]);
		
			//json root = data
		////Set up array for json
			$temparray = array();
			$newindex = 0;
			foreach ($returnArray as $key => $value) {
				$temparray[$newindex] = $value;
			////remove test accounts
				if ((int)$temparray[$newindex]['FIN_ACC_NUM'] > 9990000 
				|| strpos($temparray[$newindex]['FIRSTNAME'],'test') !== false
				|| strpos($temparray[$newindex]['SURNAME']	,'test') !== false
				|| strpos($temparray[$newindex]['FIRSTNAME'],'debug')!== false
				|| strpos($temparray[$newindex]['SURNAME']	,'user') !== false
				|| strpos($temparray[$newindex]['SURNAME']	,'Tmn')	 !== false) {
					unset($temparray[$newindex]);
				} else {
					unset($temparray[$newindex]['GUID']);
					unset($temparray[$newindex]['FIN_ACC_NUM']);
					$newindex++;
				}
			}
			//fb($temparray);
			$returnArray = $temparray;
			$returnArray = array('success' => true, 'data' => $returnArray);
			
		////echo json packet
			echo json_encode($returnArray);
		
		} else {
			die(json_encode(array('success' => false, 'alert' => "You are not authenticated.")));
		}
		
	} catch (Exception $e) {
		Reporter::newInstance($LOGFILE)->exceptionHandler($e);
	}
	
} else {
	//fb('Invalid params');
	die(json_encode(array('success' => false, 'alert' => "Invalid Parameters.")));
}

?>