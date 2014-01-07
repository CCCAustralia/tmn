<?php

include_once("../classes/TmnCrudLowAccountProcessor.php");
include_once("../classes/TmnMembercareAdminsUsersGroup.php");
include_once("../classes/TmnCrudSession.php");
include_once("../classes/TmnCrudUser.php");
//include_once("../classes/TmnFinancialUnit.php");
include_once("../classes/TmnNotifier.php");
include_once("../classes/TmnConstants.php");
include_once("../classes/TmnDatabase.php");
include_once("../classes/Tmn.php");

//Create the objects required for authorisation
try {


	$logfile			= "../logs/authprocessor.php.log";								//required for logging
	$tmn				= new Tmn($logfile);
	$db					= TmnDatabase::getInstance($logfile);
	if ($tmn->isAuthenticated()) {

        $action = "";

		//get rid of slashes if they have been added
		if(get_magic_quotes_gpc()) {
			$action	= ( isset($_POST['action']) ? stripslashes($_POST['action']) : stripslashes($_GET['action']) );
		} else {
			$action = ( isset($_POST['action']) ? $_POST['action'] : $_GET['action'] );
		}

		$returnArray				= array();
		$membercareAdminsUserGroup	= new TmnMembercareAdminsUsersGroup();

		if ( !$membercareAdminsUserGroup->containsUser($tmn->getAuthenticatedGuid()) ) {

			$returnArray['success']	= false;
			$returnArray['message']	= 'You are not a Member Care Administrator. You do not have permission to run this process. If you think you should please contact tech.team@ccca.org.au';
			die(json_encode($returnArray));

		}

		$constants	= getConstants();
		$dateOfNow	= new DateTime();
		$reminder 	= TmnNotifier::create($action);

		if (isset($reminder)) {

			$activeFinancialUnits	= TmnFinancialUnit::getActiveFinancialUnits($logfile);

			foreach($activeFinancialUnits as $financialUnit) {

				if ($financialUnit->getMinistry() == 'StudentLife') {
					$availableDate = new DateTime($constants['STUDENT_LIFE_ACTIVE_DATE']);
				} else {
					$availableDate = new DateTime($constants['EVERYONE_ACTIVE_DATE']);
				}

				if ($financialUnit->last_tmn_effective_date < $availableDate) {
					$reminder->sendEmailsFor($financialUnit);
				}

			}

            $reminder->sendReportToMemberCare();

			$returnArray['success']	= true;
			$returnArray['message']	= $reminder->sendCount() . " reminders were sent. A report of what was sent has been delivered to: " . $membercareAdminsUserGroup->getEmailsAsString();

		} else if ($action == 'low_account') {

			//TODO: Remove once TmnCrudLowAccountProcessor::compareAllAccounts() is complete
 			$returnArray['success']	= false;
 			$returnArray['message']	= 'The Low Account Process has not been fully implemented. Contact tech.team@ccca.org.au to find out when it will be finished.';

// 			//TODO: Uncomment once TmnCrudLowAccountProcessor::compareAllAccounts() is complete
// 			$dateOfNow							= getdate();
// 			$dateOfLastTimeLowAccountRanStmt	= $db->query("SELECT MAX(LAST_LOW_MONTH_DATE) AS DATE FROM Low_Account");
// 			$dateOfLastTimeLowAccountRan 		= $dateOfLastTimeLowAccountRanStmt->fetch(PDO::FETCH_ASSOC);
// 			$dateOfLastTimeLowAccountRan		= getdate(strtotime($dateOfLastTimeLowAccountRan['DATE']));
//
// 			if ($dateOfNow["mon"] == $dateOfLastTimeLowAccountRan["mon"] && $dateOfNow["year"] == $dateOfLastTimeLowAccountRan["year"]) {
//
// 				$returnArray['success']	= false;
// 				$returnArray['message']	= 'You have already run the Low Account Process this month. Try again on the first of next month.';
//
// 			} else {
//
// 				TmnCrudLowAccountProcessor::compareAllAccounts($logfile);
// 				$returnArray['success']	= true;
// 				$returnArray['message']	= 'The Low Account Process has been run. Missionaries and their leaders have been notified. Check your email if you would like to see a list of who was notified.';
//
// 			}

		} else {

			$returnArray['success']	= false;
			$returnArray['message']	= 'Unknown Action!';

		}

		echo json_encode($returnArray);

	} else {
		throw new FatalException("Authentication Failed: Try Logging in.");
	}
} catch (Exception $e) {
	Reporter::newInstance($logfile)->exceptionHandler($e);
}