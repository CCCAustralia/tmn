<?php


/*******************************************                                                        
# Test Code                 
*******************************************/

include_once('../classes/Tmn.php');
include_once('../classes/TmnCrudSession.php');
$LOGFILE	= "../logs/TmnSessionClass-test.log";
$DEBUG		= 1;

	//Constructor test
	
fb("Constructor Test");
try {
	$tmn		= new Tmn($LOGFILE);
	$session	= new TmnCrudSession($LOGFILE, 1234);
} catch (Exception $e) {
	Reporter::newInstance($LOGFILE)->exceptionHandler($e);
}


/*
 * Expected output
 * 
 * Console Output:
 * Constructor Test
 * [<now>] User Authenticated: guid = 691EC152-0565-CEF4-B5D8-************
 * 
 * Screen Output:
 * 
 */

	//Access test

fb("Access Test");
fb("getOwner()");
fb($session->getOwner());
fb("setOwner(tmn->getUser())");
$session->setOwner($tmn->getUser());
fb("getOwner()");
fb($session->getOwner());
fb("setOwner()");
$session->setOwner();
fb("getOwner()");
fb($session->getOwner());
fb("setOwnerGuid('testuserguid')");
$session->setOwnerGuid('testuserguid');
fb("getOwner()");
fb($session->getOwner());
fb("setOwnerGuid(tmn->getAuthenticatedGuid())");
$session->setOwnerGuid($tmn->getAuthenticatedGuid());
fb("getOwnerGuid()");
fb($session->getOwnerGuid());

/*
 * Expected output
 * 
 * Console Output:
 * Access Test
 * getOwner()
 * FALSE
 * setOwner(tmn->getUser())
 * getOwner()
 * TmnCrudUser('db'=>TmnDatabase('instance'=>'** Recursion (TmnDatabase) **', 'db'=>PDO(), 'db_name'=> ... ), 'table_name'=>'User_Profiles', 'primarykey_name'=> ... )
 * setOwner()
 * getOwner()
 * FALSE
 * setOwnerGuid('testuserguid')
 * getOwner()
 * TmnCrudUser('db'=>TmnDatabase('instance'=>'** Recursion (TmnDatabase) **', 'db'=>PDO(), 'db_name'=> ... ), 'table_name'=>'User_Profiles', 'primarykey_name'=> ... )
 * setOwnerGuid(tmn->getAuthenticatedGuid())
 * getOwnerGuid()
 * 691EC152-0565-CEF4-B5D8-99286252652B
 * 
 * Screen Output:
 * 
 */

/*
	//Authorisation Test

fb("Authorisation Test");

$dud	= new TmnCrudUser($LOGFILE);
$dud->loadUserWithName("Kent", "Keller");
$user	= new TmnCrudUser($LOGFILE);
$user->loadUserWithName("Michael", "Harrison");
$level1	= new TmnCrudUser($LOGFILE);
$level1->loadUserWithName("Thomas", "Flynn");
$level2	= new TmnCrudUser($LOGFILE);
$level2->loadUserWithName("test", "user");
$level3	= new TmnCrudUser($LOGFILE);
$level3->loadUserWithName("debug", "user");
$data = json_decode('{"session_name":"test","date_modified":"2011-03-16 20:05:21","os_resident_for_tax_purposes":"","net_stipend":950,"tax":30,"post_tax_super":84,"taxable_income":1069,"pre_tax_super":96,"mfb":1069,"financial_package":2234,"employer_super":96,"mmr":200,"stipend":950,"housing_mfb":236,"mfb_rate":"Full","claimable_mfb":833,"total_super":276,"super_fund":"IOOF","income_protection_cover_source":"Support Account","s_mfb_rate":"Full","s_super_fund":"IOOF","s_income_protection_cover_source":"Support Account","joint_financial_package":2234,"total_transfers":92,"workers_comp":34,"ccca_levy":295,"tmn":2951,"buffer":4426,"monthly_housing":236,"housing":236,"housing_frequency":"Monthly"}', true);

fb("submit(user, level1, level2, level3, data): ");
fb($session->submit($user, $level1, $level2, $level3, $data));
fb("userIsAuthoriser(level1): ");
fb($session->userIsAuthoriser($level1));
fb("userIsAuthoriser(dud): ");
fb($session->userIsAuthoriser($dud));
fb("authorise(2,1): ");
fb($session->authorise(2,1));
*/

/*
 * Expected output
 * 
 * Console Output:
 * Authorisation Test
 * submit(user, level1, level2, level3, data): 
 * TRUE
 * userIsAuthoriser(level1): 
 * TRUE
 * userIsAuthoriser(dud): 
 * FALSE
 * authorise(2,1): 
 * TRUE
 * 
 * Screen Output:
 * 
 */

?>
