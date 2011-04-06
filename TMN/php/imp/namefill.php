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
		
		$tmn = new Tmn($LOGFILE);
		$user = $tmn->getUser();
		$userguid = $user->getGuid();
		//$tmn->authenticate();
		
		if ($tmn->isAuthenticated()) {
	////Get User
	////Get all users from User_Profiles table
			$sql = "SELECT ID, GUID, FIRSTNAME, SURNAME, MINISTRY, FIN_ACC_NUM FROM `User_Profiles` WHERE ID IS NOT NULL";
			$sql = mysql_query($sql);
			$userlist_all = array();
		////Sort the users into a single array
			for ($row = 0; $row < mysql_num_rows($sql); $row++) {
				$temp = mysql_fetch_assoc($sql);
				$userlist_all[$temp['GUID']] = $temp;
			}
			//fb("userlist_all:"); fb($userlist_all);

	////Get all authorisers from Authorisers table
			$sql = "SELECT GUID, MINISTRY FROM `Authorisers` WHERE GUID IS NOT NULL";
			$sql = mysql_query($sql);
			//get nmls
			$userlist_nml = array();
			//get ND
			$userlist_nd = array();
		////Sort the authorisers into arrays
			for ($row = 0; $row < mysql_num_rows($sql); $row++) {
				$temp = mysql_fetch_assoc($sql);
				if ($temp['MINISTRY'] == 'National Director') {
					$userlist_nd[$temp['GUID']] = $temp;
					$userlist_nd[$temp['GUID']]['FIRSTNAME'] = $userlist_all[$temp['GUID']]['FIRSTNAME'];
					$userlist_nd[$temp['GUID']]['SURNAME'] = $userlist_all[$temp['GUID']]['SURNAME'];
					$userlist_nd[$temp['GUID']]['ID'] = $userlist_all[$temp['GUID']]['ID'];
				} else {
					$userlist_nml[$temp['GUID']] = $temp;
					$userlist_nml[$temp['GUID']]['FIRSTNAME'] = $userlist_all[$temp['GUID']]['FIRSTNAME'];
					$userlist_nml[$temp['GUID']]['SURNAME'] = $userlist_all[$temp['GUID']]['SURNAME'];
					$userlist_nml[$temp['GUID']]['ID'] = $userlist_all[$temp['GUID']]['ID'];
				}
			}
			
			
			
		////Check what level the user is
			$userauthlevel = 1;		//default is level 1
			if (isset($userlist_nml[$userguid])) {
				//user is NML
				$userauthlevel = 2;
			}
			if (isset($userlist_nd[$userguid])) {
				//user is ND
				$userauthlevel = 3;
			}
			
			fb("userauthlevel:"); fb($userauthlevel);
			
			//store the requested authlevel
			$mode		= $_REQUEST['mode'];
			fb("$mode requested");
			$returnarray	= array();
			
		////Authlevel 1
			if ($mode == 'level_1') {
				//set is all users
				$returnarray = $userlist_all;
				
				//if user is NML or ND, level 1 is NML
				if (isset($userlist_nd[$userguid]) || isset($userlist_nml[$userguid])) {
					$returnarray = $userlist_nml;
				}
			} 
		////Authlevel 2
			if ($mode == 'level_2') {
				//return set is all NMLs
				$returnarray = $userlist_nml;
			}
		////Authlevel 3
			if ($mode == 'level_3') {
				//return set is ND
				$returnarray = $userlist_nd;
			}
		////mode=all
			if ($mode == "all") {
				$returnarray = $userlist_all;
			}
				
		////Remove the user from the returned list
			unset($returnarray[$userguid]);
		
			//json root = data
		////Set up array for json
			$temparray = array();
			$newindex = 0;
			foreach ($returnarray as $key => $value) {
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
			fb($temparray);
			$returnarray = $temparray;
			$returnarray = array('success' => true, 'data' => $returnarray);
			
		////echo json packet
			echo json_encode($returnarray);
		
		} else {
			die(json_encode(array('success' => false, 'alert' => "You are not authenticated.")));
		}
		
	} catch (Exception $e) {
		Reporter::newInstance($LOGFILE)->exceptionHandler($e);
	}
	
} else {
	fb('Invalid params');
	die(json_encode(array('success' => false, 'alert' => "Invalid Parameters.")));
}

?>