<?php

include_once("../classes/TmnCrudLowAccountProcessor.php");
include_once("../classes/TmnMembercareAdminsUsersGroup.php");
include_once("../classes/TmnCrudSession.php");
include_once("../classes/TmnCrudUser.php");
include_once("../classes/TmnConstants.php");
include_once("../classes/TmnDatabase.php");
include_once("../classes/Tmn.php");

//Create the objects required for authorisation
try {
	$logfile			= "../logs/authprocessor.php.log";								//required for logging
	$tmn				= new Tmn($logfile);
	$db					= TmnDatabase::getInstance($logfile);
	if ($tmn->isAuthenticated()) {
		
		//get rid of slashes if they have been added
		if(get_magic_quotes_gpc()) {
			$action	= stripslashes($_POST['action']);
		} else {
			$action = $_POST['action'];
		}
		
		$returnArray				= array();
		$membercareAdminsUserGroup	= new TmnMembercareAdminsUsersGroup();
		
		if ( !$membercareAdminsUserGroup->containsUser($tmn->getAuthenticatedGuid()) ) {
			
			$returnArray['success']	= false;
			$returnArray['message']	= 'You are not a Member Care Administrator. You do not have permission to run this process. If you think you should please contact tech.team@ccca.org.au';
			die(json_encode($returnArray));
			
		}
		
		switch ($action) {
			
			case 'lazy_missionaries':
				
				//find url of TMN
				$curpageurl = TmnAuthenticator::curPageURL();
				$curpageurl = split("/", $curpageurl);
				unset($curpageurl[count($curpageurl) -1]);	//take off page name
				unset($curpageurl[count($curpageurl) -1]);	//take off php/
				if ($curpageurl[count($curpageurl) - 1] == "php") {
					unset($curpageurl[count($curpageurl) -1]);	//take off php/
				}
				$tmn_url = join("/",	$curpageurl);
				
				//set up dates for comparison
				$constants				= getConstants();
				$dateOfNow				= getdate();
				$dateOfTmnUpdate		= getdate(strtotime($constants['DATE_MODIFIED']));
				$dateOfStudentLifeCutOff= getdate(mktime(0,0,0,5,1,date("Y")));
				
				//set up constant email variables
				$membercareAdminsEmails	= $membercareAdminsUserGroup->getEmailsAsString();
				$lazyMissionaryEmail	= new Email(null, "A friendly reminder that your TMN is due", null, "CCCA TMN <noreply@ccca.org.au>\r\nReply-To: noreply@ccca.org.au");
				$leaderEmail			= new Email(null, "Missionaries who still need to do TMNs", null, "CCCA TMN <noreply@ccca.org.au>\r\nReply-To: noreply@ccca.org.au");
				
				//check whether it is the Student Life only period to do TMNs
				if ( $dateOfTmnUpdate[0] < $dateOfStudentLifeCutOff[0] && $dateOfNow[0] > $dateOfTmnUpdate[0] && $dateOfNow[0] < $dateOfStudentLifeCutOff[0] ) {
					$where_clause = " WHERE MINISTRY='StudentLife'";
				} else {
					$where_clause = "";
				}
				
				//grab an array of all the leaders
				$leadersStmt			= $db->query("SELECT MINISTRY, GUID FROM Authorisers" . $where_clause);
				$allLeaders 			= $leadersStmt->fetchAll(PDO::FETCH_ASSOC);
				
				//set up the query that will be run for each ministry
				$lazyMissionariesStmt	= $db->prepare("SELECT * FROM User_Profiles AS u LEFT JOIN Low_Account AS l ON u.FIN_ACC_NUM=l.FIN_ACC_NUM WHERE ( l.CURRENT_SESSION_ID IS NULL OR l.TMN_EFFECTIVE_DATE < (SELECT MAX(DATE_MODIFIED) FROM Constants) ) AND u.MINISTRY = :ministry AND u.IS_TEST_USER = 0 AND u.INACTIVE = 0 AND u.EXEMPT_FROM_TMN = 0");
				$leadersMinistry		= array(":ministry" => "");
				
				//for each ministry generate an email of all missionaries that haven't put in a TMN since its been updated
				foreach ($allLeaders as $leaderRow) {
					
					$leader				= new TmnCrudUser($logfile, $leaderRow['GUID']);
					$leaderEmailAddress	= $leader->getField("email") . "," . $membercareAdminsEmails;
					$leaderEmailBody	= "Hi " . $leader->getField("firstname") . ",\n\n";
					$leaderEmailBody	.= "The Following " . $leaderRow['MINISTRY'] . " Missionaries still need to do their TMNs:\n";
					
					$leadersMinistry[":ministry"]	= $leaderRow['MINISTRY'];
					$lazyMissionariesStmt->execute($leadersMinistry);
					$allLazyMissionaries 	= $lazyMissionariesStmt->fetchAll(PDO::FETCH_ASSOC);
					
					if (count($allLazyMissionaries)) {
						
						foreach ($allLazyMissionaries as $lazyMissionaryRow) {
							
							$leaderEmailBody	.= "    - " . $lazyMissionaryRow['FIRSTNAME'] . " " . $lazyMissionaryRow['SURNAME'] . ", " . $lazyMissionaryRow['EMAIL'] . "\n";
							
							$lazyMissionaryEmailAddress	= $lazyMissionaryRow['EMAIL'];
							$lazyMissionaryEmailBody	= "Hi " . $lazyMissionaryRow['FIRSTNAME'] . ",\n\n";
							$lazyMissionaryEmailBody	.= "You still need to do your TMN. Please go to $tmn_url to complete it.\n " . $leader->getField("firstname") . " " . $leader->getField("surname") . " will be following you up on this. If you need to talk to someone about not having done your TMN your should email " . $leader->getField("firstname") . " at " . $leader->getField("email") . ".\n";
							$lazyMissionaryEmailBody	.= "\n\n-The TMN Development Team";
							
							$lazyMissionaryEmail->update($lazyMissionaryEmailAddress, null, $lazyMissionaryEmailBody);
							fb($lazyMissionaryEmail);
							$lazyMissionaryEmail->send();
							
						}
						
						$leaderEmailBody .= "\n\n-The TMN Development Team";
						$leaderEmail->update($leaderEmailAddress, null, $leaderEmailBody);
						fb($leaderEmail);
						$leaderEmail->send();
						
					}
					
				}
				
				$returnArray['success']	= true;
				$returnArray['message']	= 'Lazy Missionaries and their leaders have been notified. Check your email if you would like to see a list of who was notified.';
				
				break;
			case 'low_account':
				
				//TODO: Remove once TmnCrudLowAccountProcessor::compareAllAccounts() is complete
				$returnArray['success']	= false;
				$returnArray['message']	= 'The Low Account Process has not been fully implemented. Contact tech.team@ccca.org.au to find out when it will be finished.';
				
				/*TODO: Uncomment once TmnCrudLowAccountProcessor::compareAllAccounts() is complete
				$dateOfNow							= getdate();
				$dateOfLastTimeLowAccountRanStmt	= $db->query("SELECT MAX(LAST_LOW_MONTH_DATE) AS DATE FROM Low_Account");
				$dateOfLastTimeLowAccountRan 		= $dateOfLastTimeLowAccountRanStmt->fetch(PDO::FETCH_ASSOC);
				$dateOfLastTimeLowAccountRan		= getdate(strtotime($dateOfLastTimeLowAccountRan['DATE']));
				
				if ($dateOfNow["mon"] == $dateOfLastTimeLowAccountRan["mon"] && $dateOfNow["year"] == $dateOfLastTimeLowAccountRan["year"]) {
					
					$returnArray['success']	= false;
					$returnArray['message']	= 'You have already run the Low Account Process this month. Try again on the first of next month.';
				
				} else {
					
					TmnCrudLowAccountProcessor::compareAllAccounts();
					$returnArray['success']	= true;
					$returnArray['message']	= 'The Low Account Process has been run. Missionaries and their leaders have been notified. Check your email if you would like to see a list of who was notified.';
					
				}
				*/
				
				break;
			case 'lazy_authorisers':
				
				//find url of TMN
				$curpageurl = TmnAuthenticator::curPageURL();
				$curpageurl = split("/", $curpageurl);
				unset($curpageurl[count($curpageurl) -1]);	//take off page name
				unset($curpageurl[count($curpageurl) -1]);	//take off php/
				if ($curpageurl[count($curpageurl) - 1] == "php") {
					unset($curpageurl[count($curpageurl) -1]);	//take off php/
				}
				$tmn_url = join("/",	$curpageurl);
				
				//set how many days an authoriser can go before being pulled up
				$authoriserLimit		= 7;
				
				//set up constant email variables
				$membercareAdminsEmails	= $membercareAdminsUserGroup->getEmailsAsString();
				$lazyAuthorisersEmail	= new Email(null, "A friendly reminder that you need to approve TMNs", null, "CCCA TMN <noreply@ccca.org.au>\r\nReply-To: noreply@ccca.org.au");
				$leaderEmail			= new Email(null, "Missionaries who still need to approve TMNs", null, "CCCA TMN <noreply@ccca.org.au>\r\nReply-To: noreply@ccca.org.au");
				
				//grab an array of all the leaders
				$leadersStmt			= $db->query("SELECT MINISTRY, GUID FROM Authorisers" . $where_clause);
				$allLeaders 			= $leadersStmt->fetchAll(PDO::FETCH_ASSOC);
				
				//set up the query that will be run for each ministry
				$lazyAuthorisersStmt	= $db->query("SELECT * FROM User_Profiles WHERE GUID IN (SELECT AUTH_LEVEL_1 FROM Auth_Table WHERE (AUTH_LEVEL_1 != '') && (LEVEL_1_RESPONSE = 'Pending') && (DATEDIFF(CURRENT_DATE(), USER_TIMESTAMP) > " . $authoriserLimit . ")) OR GUID IN (SELECT AUTH_LEVEL_2 FROM Auth_Table WHERE (AUTH_LEVEL_2 != '') && (LEVEL_2_RESPONSE = 'Pending') && (DATEDIFF(CURRENT_DATE(), USER_TIMESTAMP) > " . $authoriserLimit . ")) OR GUID IN (SELECT AUTH_LEVEL_3 FROM Auth_Table WHERE (AUTH_LEVEL_3 != '') && (LEVEL_3_RESPONSE = 'Pending') && (DATEDIFF(CURRENT_DATE(), USER_TIMESTAMP) > " . $authoriserLimit . "))");
				$lazyAuthorisersResult 	= $lazyAuthorisersStmt->fetchAll(PDO::FETCH_ASSOC);
				$lazyAuthorisersArray	= array();
				
				//group authorisers by ministry
				foreach ($lazyAuthorisersResult as $authRow) {
					
					if (!isset($lazyAuthorisersArray[$authRow['MINISTRY']])) {
						$lazyAuthorisersArray[$authRow['MINISTRY']] = array();
					}
					
					$lazyAuthorisersArray[$authRow['MINISTRY']][count($lazyAuthorisersArray[$authRow['MINISTRY']])] = $authRow;
				}
				
				//for each ministry generate an email of all missionaries that haven't put in a TMN since its been updated
				foreach ($allLeaders as $leaderRow) {
					
					$leader				= new TmnCrudUser($logfile, $leaderRow['GUID']);
					$leaderEmailAddress	= $leader->getField("email") . "," . $membercareAdminsEmails;
					$leaderEmailBody	= "Hi " . $leader->getField("firstname") . ",\n\n";
					$leaderEmailBody	.= "The Following " . $leaderRow['MINISTRY'] . " Missionaries still need to approve their Missionary's TMNs:\n";
					
					if (count($lazyAuthorisersArray[$leaderRow['MINISTRY']])) {
						
						foreach ($lazyAuthorisersArray[$leaderRow['MINISTRY']] as $lazyAuthorisersRow) {
							
							$leaderEmailBody	.= "    - " . $lazyAuthorisersRow['FIRSTNAME'] . " " . $lazyAuthorisersRow['SURNAME'] . ", " . $lazyAuthorisersRow['EMAIL'] . "\n";
							
							$lazyAuthorisersEmailAddress	= $lazyAuthorisersRow['EMAIL'];
							$lazyAuthorisersEmailBody	= "Hi " . $lazyAuthorisersRow['FIRSTNAME'] . ",\n\n";
							$lazyAuthorisersEmailBody	.= "You still need to approve a TMN. Please go to $tmn_url/tmn-authviewer.php to complete it.\n " . $leader->getField("firstname") . " " . $leader->getField("surname") . " will be following you up on this. If you need to talk to someone about the TMNs you have not responded to, you should email " . $leader->getField("firstname") . " at " . $leader->getField("email") . ".\n";
							$lazyAuthorisersEmailBody	.= "\n\n-The TMN Development Team";
							
							$lazyAuthorisersEmail->update($lazyAuthorisersEmailAddress, null, $lazyAuthorisersEmailBody);
							//fb($lazyAuthorisersEmail);
							$lazyAuthorisersEmail->send();
							
						}
						
						$leaderEmailBody .= "\n\n-The TMN Development Team";
						$leaderEmail->update($leaderEmailAddress, null, $leaderEmailBody);
						//fb($leaderEmail);
						$leaderEmail->send();
						
					}
					
				}
				
				$returnArray['success']	= true;
				$returnArray['message']	= 'Lazy Authorisers and thier leaders have been notified. Check your email if you would like to see a list of who was notified.';
				
				break;
			default:
				
				$returnArray['success']	= false;
				$returnArray['message']	= 'Unknown Action!';
			
				break;
			
		}
		
		echo json_encode($returnArray);
		
	} else {
		throw new FatalException("Authentication Failed: Try Logging in.");
	}
} catch (Exception $e) {
	Reporter::newInstance($logfile)->exceptionHandler($e);
}
?>