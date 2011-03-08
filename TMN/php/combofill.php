<?php
/**
 * Combofill.php - takes 'mode': a POST variable
 * @param mode: the database table you wish to retrieve
 * 
 * returns a json packet with the field names and values.
 */

include_once('TmnSessionComboLoader.php');
//include_once('TmnSession.php');


//set the log path
$LOGFILE = "logs/combofill.log";
/*
//$session = new TmnSession($LOGFILE);
$json_string	= '{"aussie-based":{"firstname":"Peter","s_firstname":"Jacqueline","surname":"Brook","s_surname":"Brook","date":"22 Nov 2010","s_date":"22 Nov 2010","fan":"1011131","s_fan":"1011131","ministry":"StudentLife","s_ministry":"StudentLife","ft_pt_os":"Full Time","s_ft_pt_os":"Full Time","days_per_wk":5,"s_days_per_wk":5,"stipend":"880","s_stipend":"880","housing_stipend":0,"s_housing_stipend":0,"net_stipend":880,"s_net_stipend":880,"tax":"17","s_tax":"17","additional_tax":"0","s_additional_tax":"0","post_tax_super":"84","s_post_tax_super":"84","taxable_income":"986","s_taxable_income":"986","pre_tax_super":"89","s_pre_tax_super":"89","housing":"0","monthly_housing":"0","housing_frequency":"Monthly","additional_housing":0,"additional_housing_allowance":0,"s_additional_housing_allowance":0,"additional_life_cover":0,"s_additional_life_cover":0,"income_protection_cover_source":"Support Account","s_income_protection_cover_source":"Support Account","mfb":986,"s_mfb":986,"claimable_mfb":986,"s_claimable_mfb":986,"housing_mfb":0,"s_housing_mfb":0,"mfb_rate":"Full","s_mfb_rate":"Full","mmr":"1200","s_mmr":"0","financial_package":2061,"s_financial_package":2061,"joint_financial_package":4122,"ministry_levy":112,"s_ministry_levy":0,"employer_super":89,"s_employer_super":89,"total_super":262,"s_total_super":262,"resc":0,"s_resc":0,"super_fund":"IOOF","s_super_fund":"IOOF","os_assignment_start_date":null,"os_assignment_end_date":null,"os_lafha":null,"s_os_lafha":null,"os_resident_for_tax_purposes":"Resident Of Australia","os_overseas_housing_allowance":0,"s_os_overseas_housing_allowance":0,"os_overseas_housing":0,"transfers":[{"name":"A & O Hdija","amount":"30"},{"name":"C Winter","amount":"20"},{"name":"S & R Adamson","amount":"30"},{"name":"Student Life Office","amount":"100"},{"name":"Peter & Jen Hibbs","amount":"30"},{"name":"Aaron Gibson","amount":"30"},{"name":"Andy Donaldson","amount":"20"},{"name":"StudentLife","amount":112}],"total_transfers":372,"workers_comp":62,"buffer":13186,"international_donations":"0","ccca_levy":659,"tmn":6593,"auth_lv1":1,"auth_lv2":0,"auth_lv2_reasons":[],"auth_lv3":0,"auth_lv3_reasons":[]}}';
$jsonObj		= json_decode($json_string, true);
$aussieObj		= $jsonObj['aussie-based'];
//echo $session->createSessionFromJson($aussieObj);
*/
if (isset($_POST['mode'])) {
	
	$tablename		= $_POST['mode'];
		
	if ($_POST['mode'] == 'Tmn_Sessions') {
		
		if (isset($_POST['aussie_form']) && isset($_POST['overseas_form']) && isset($_POST['home_assignment'])) {
			
			$aussie_form		= ($_POST['aussie_form'] == 'true' ? true : false);
			$overseas_form		= ($_POST['overseas_form'] == 'true' ? true : false);
			$home_assignment	= ($_POST['home_assignment'] == 'true' ? true : false);
			
			$comboLoader	= new TmnSessionComboLoader($LOGFILE, "Tmn_Sessions", $aussie_form, $overseas_form, $home_assignment);
			
		} else {
			fb('Invalid get_session params');
			die('{success: false}');
		}
		
	} else {
		$comboLoader	= new TmnComboLoader($LOGFILE, $tablename);
	}
	
	echo $comboLoader->produceJson();
	
} else {
	fb('Invalid params');
	die('{success: false}');
}

/*
include_once "dbconnect.php";
include_once "logger.php";

//connect to database
$connection = db_connect();
//set the log path
$LOGFILE = "logs/combofill.log";

//fetch the parameter from POST
$tablename = $_POST['mode'];

//check for sql injection by finding spaces in the parameter
$issql = true;
if (!strstr($tablename, ' '))
	$issql = false;

//if the request is invalid
if ($tablename == 'User_Profiles' || $tablename == 'Sessions' || $tablename == 'Authorising' || $issql)
	die();

//form the sql statement
$rows = "SELECT * FROM ".$tablename;
$rows = mysql_query($rows);

//form the returned json with the sql result:
//iterate through each returned row
for ($i = 0; $i < mysql_num_rows($rows); $i++) {
	$r = mysql_fetch_assoc($rows);
	$returndata .= "{";
	//iterate through each field in the row
	foreach ($r as $k=>$v) {
		$returndata .= "\"".$k."\": \"".$r[$k]."\",";
	}
	$returndata = trim($returndata, ",");
	$returndata .= "},";
}

//trim
$returndata = trim($returndata,",");

//return
echo '{	'.$tablename.':['.$returndata.'] }';

//$connection.close();

*/

?>