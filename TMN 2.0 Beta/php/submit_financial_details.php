<?php
$DEBUG = 0;

include_once("logger.php");
include_once("dbconnect.php");
include_once("./calc/calc_tax.php");
if($DEBUG) require_once("../lib/FirePHPCore/fb.php");

if($DEBUG) ob_start();		//enable firephp logging


$LOGFILE = "./logs/submit_fd.log";

$WORKERS_COMP_RATE = 0.015;
$CCCA_LEVY_RATE = 0.1;
$MIN_ADD_SUPER_RATE = 0.09;	//Minimum Additional CCCA Pre-tax Super - rate for Full MFB
$NET_STIPEND_MIN = 100;

//Band values
$MULTIPLIER				=	1;
$BAND_FP_COUPLE			=	6000;
$BAND_FP_SINGLE			=	3600;
$BAND_TMN_COUPLE_MIN	=	3600;
$BAND_TMN_COUPLE_MAX	=	7200;
$BAND_TMN_SINGLE_MIN	=	2400;
$BAND_TMN_SINGLE_MAX	=	4100;


$connection = db_connect();



//get if is couple
$couple_row = mysql_fetch_assoc(mysql_query("SELECT GUID FROM User_Profiles WHERE FIN_ACC_NUM=(SELECT FIN_ACC_NUM FROM User_Profiles WHERE guid='".$_POST['guid']."') && guid !='".$_POST['guid']."'"));
$iscouple = !is_null($couple_row['GUID']);

//Make the page more readable for debugging
//header("content-type:text/plain");

//decode the data from the form
$formdata = (isset($_POST[guid]) ? $_POST : $_GET);		//json_decode(stripslashes($_REQUEST['financial_data']),true);

//DATA ARRAY SETUP//
$data = array(
firstname						=>	"__",
s_firstname						=>	"__",
surname							=>	"__",
s_surname						=>	"__",
date							=>	"__",
s_date							=>	"__",
fan								=>	"__",
s_fan							=>	"__",

ministry						=>	"__",
s_ministry						=>	"__",

ft_pt_os						=>	"__",
s_ft_pt_os						=>	"__",
days_per_wk						=>	"__",
s_days_per_wk					=>	"__",

net_stipend						=>	"__",
s_net_stipend					=>	"__",
tax								=>	"__",
s_tax							=>	"__",
additional_tax					=>	"__",
s_additional_tax				=>	"__",
post_tax_super					=>	"__",
s_post_tax_super				=>	"__",

taxable_income					=>	"__",
s_taxable_income				=>	"__",
pre_tax_super					=>	"__",
s_pre_tax_super					=>	"__",
housing							=>	"__",
housing_frequency				=>	"__",
additional_housing				=>	"__",
additional_life_cover			=>	"__",
s_additional_life_cover			=>	"__",
income_protection_cover_source	=>	"__",
s_income_protection_cover_source=>	"__",
mfb								=>	"__",
s_mfb							=>	"__",
mfb_rate						=>	"__",
s_mfb_rate						=>	"__",
mmr								=>	"__",
s_mmr							=>	"__",
financial_package				=>	"__",
s_financial_package				=>	"__",
joint_financial_package			=>	"__",
ministry_levy					=>	"__",
s_ministry_levy					=>	"__",

pre_tax_super					=>	"__",
s_pre_tax_super					=>	"__",
employer_super					=>	"__",
s_employer_super				=>	"__",
total_super						=>	"__",
s_total_super					=>	"__",
resc							=>	"__",
s_resc							=>	"__",
super_fund						=>	"__",
s_super_fund					=>	"__",


//joint data
//-housing
//-additional_housing
//-joint_financial_package


transfers						=>	"",
total_transfers					=>	"",

workers_comp					=>	"__",

buffer							=>	"__",

international_donations			=> 	0,
ccca_levy						=>	"__",

tmn								=>	"__",

auth_lv1						=>	0,
auth_lv2						=>	0,
auth_lv2_reasons				=>  array(),
auth_lv3						=>	0,
auth_lv3_reasons				=>	array()
);
//END DATA ARRAY SETUP//



//Fetch names
$row = mysql_fetch_assoc(mysql_query("SELECT * FROM User_Profiles WHERE guid='".$formdata['guid']."'"));
$s_row = mysql_fetch_assoc(mysql_query("SELECT * FROM User_Profiles WHERE guid=(SELECT SPOUSE_GUID FROM User_Profiles WHERE guid='".$formdata['guid']."')"));


//Main user
//-from db(User_Profiles)
$data['firstname']					=	$row['FIRSTNAME'];
$data['surname']					=	$row['SURNAME'];

//Spouse
//-from db(User_Profiles)
$data['s_firstname']				=	$s_row['FIRSTNAME'];
$data['s_surname']					=	$s_row['SURNAME'];

//Date
//(date of last change i.e. now)
$data['date']						=	date("d M Y");
$data['s_date']						=	date("d M Y");

//Financial Account Number
//-from db(User_Profiles)
$data['fan']						=	$row['FIN_ACC_NUM'];
$data['s_fan']						=	$s_row['FIN_ACC_NUM'];

//Ministry
//-from db(User_Profiles)
$data['ministry']					=	$row['MINISTRY'];
$data['s_ministry']					=	$s_row['MINISTRY'];

//Full-time/Part-time/Overseas
//-from db(User_Profiles)
$ftptos_sql = mysql_query("SELECT * FROM FT_PT_OS");
for ($i = 0; $i < mysql_num_rows($ftptos_sql); $i++) {
	$ftptos_row = mysql_fetch_assoc($ftptos_sql);
	$ftptos_map[$ftptos_row['key']] = $ftptos_row['value'];
}
if($DEBUG) fb($ftptos_map);
$data['ft_pt_os']					=	$ftptos_map[$row['FT_PT_OS']];
$data['s_ft_pt_os']					=	$ftptos_map[$s_row['FT_PT_OS']];

//Days per Week
//-from db(User_Profiles)
if (is_null($row['DAYS_PER_WEEK']) || $ftptos_map[$row['FT_PT_OS']] == "Full Time")
	$row['DAYS_PER_WEEK']			=	4;
$data['days_per_wk']				=	$row['DAYS_PER_WEEK'] + 1;
if (is_null($s_row['DAYS_PER_WEEK']) || $ftptos_map[$s_row['FT_PT_OS']] == "Full Time")
	$s_row['DAYS_PER_WEEK']			=	4;
$data['s_days_per_wk']				=	$s_row['DAYS_PER_WEEK'] + 1;

//Net Stipend
//-from Form on Page
//NOTE: The old TMN refers to and displays this as Gross Stipend.
//Either way, this value is as such: <Net/Gross Stipend> + <Additional Tax> + <Post-Tax Super> = Taxable Income
$data['net_stipend']				=	$formdata['NET_STIPEND'];
$data['s_net_stipend']				=	$formdata['S_NET_STIPEND'];

//Tax
//-from Form on Page
$data['tax']						=	$formdata['TAX'];
$data['s_tax']						=	$formdata['S_TAX'];

//Additional Tax
//-from Form on Page
$data['additional_tax']				=	$formdata['ADDITIONAL_TAX'];
$data['s_additional_tax']			=	$formdata['S_ADDITIONAL_TAX'];

//Post-Tax Super (voluntary super contributions)
//-from Form on Page
$data['post_tax_super']				=	$formdata['POST_TAX_SUPER'];
$data['s_post_tax_super']			=	$formdata['S_POST_TAX_SUPER'];

//Taxable Income
//-calculated using sum of (Net Stipend, Add. Tax, and Post-Tax Super)
$data['taxable_income']				=	$formdata['TAXABLE_INCOME'];//calculateTaxableIncome($data['net_stipend'] + $data['additional_tax'] + $data['post_tax_super']);
$data['s_taxable_income']			=	$formdata['S_TAXABLE_INCOME'];//calculateTaxableIncome($data['s_net_stipend'] + $data['s_additional_tax'] + $data['s_post_tax_super']);

//Housing
$data['housing']					=	$formdata['HOUSING'];

//Housing Frequency
$data['housing_frequency']			=	($formdata['HOUSING_FREQUENCY'] ? "Fortnightly" : "Monthly");

//Additional Housing Allowance
$data['additional_housing']			=	$formdata['ADDITIONAL_HOUSING'];

//Additional Life Cover
$data['additional_life_cover']		=	$formdata['LIFE_COVER'];
$data['s_additional_life_cover']	=	$formdata['S_LIFE_COVER'];

//INCOME_PROTECTION Cover Source
//(index: 0=Support Account, 1=Super Fund)
$data['income_protection_cover_source']	=	($formdata['INCOME_PROTECTION_COVER_SOURCE'] ? "Super Fund" : "Support Account");
$data['s_income_protection_cover_source']	=	($formdata['S_INCOME_PROTECTION_COVER_SOURCE'] ? "Super Fund" : "Support Account");

//Ministry Fringe Benefits
$data['mfb']						=	$formdata['MFB'];
$data['s_mfb']						=	$formdata['S_MFB'];

//MFB Rate
switch ($formdata['MFB_RATE']) {
case 0:
	$data['mfb_rate'] = "Zero";
	break;
case 1:
	$data['mfb_rate'] = "Half";
	break;
case 2:
	$data['mfb_rate'] = "Full";
	break;
}
$mfb_rate = $formdata['MFB_RATE'] * 0.5;
switch ($formdata['S_MFB_RATE']) {
case 0:
	$data['s_mfb_rate'] = "Zero";
	break;
case 1:
	$data['s_mfb_rate'] = "Half";
	break;
case 2:
	$data['s_mfb_rate'] = "Full";
	break;
}
$s_mfb_rate = $formdata['S_MFB_RATE'] * 0.5;


//Pre-Tax Super
//TODO: Find how this is calculated and where to get it from.
$min_pretax_super = round($mfb_rate * $MIN_ADD_SUPER_RATE * $data['taxable_income']);
if ($formdata['PRE_TAX_SUPER'] >= $min_pretax_super){
	$data['pre_tax_super']				=	$formdata['PRE_TAX_SUPER'];
	//Reportable Employer Super Contribution
	$data['resc']						=	round($data['pre_tax_super'] - $min_pretax_super);
} else {
	$err .= "PRE_TAX_SUPER:\"Your Pre-Tax Super must be at least $".$min_pretax_super.".\", ";
}
$s_min_pretax_super = round($s_mfb_rate * $MIN_ADD_SUPER_RATE * $data['s_taxable_income']);
if ($formdata['S_PRE_TAX_SUPER'] >= $s_min_pretax_super){
	$data['s_pre_tax_super']			=	$formdata['S_PRE_TAX_SUPER'];
	//Reportable Employer Super Contribution
	$data['s_resc']						=	round($data['s_pre_tax_super'] - $s_min_pretax_super);
} else {
	$err .= "S_PRE_TAX_SUPER:\"Your Pre-Tax Super must be at least $".$s_min_pretax_super.".\", ";
}


//Financial Packages
if (!$iscouple){
	$data['financial_package']			=	round($data['taxable_income'] + $data['mfb'] + $data['pre_tax_super'] + $data['additional_life_cover'] + $data['additional_housing'], ROUND_HALF_UP);
	$data['s_financial_package']		=	0;
	$data['joint_financial_package']	=	$data['financial_package'];
} else {
	$data['financial_package']			=	round($data['taxable_income'] + $data['mfb'] + $data['pre_tax_super'] + $data['additional_life_cover'] + ($data['additional_housing'] * ($data['net_stipend'] / ($data['net_stipend'] + $data['s_net_stipend']))), ROUND_HALF_UP);
	$data['s_financial_package']		=	round($data['s_taxable_income'] + $data['s_mfb'] + $data['s_pre_tax_super'] + $data['additional_life_cover'] + ($data['additional_housing'] * ($data['s_netstipend'] / ($data['net_stipend'] + $data['s_net_stipend']))), ROUND_HALF_UP);
	$data['joint_financial_package']	=	$data['financial_package'] + $data['s_financial_package'];
}

//Employer Super
$data['employer_super']				=	$formdata['EMPLOYER_SUPER'];
$data['s_employer_super']			=	$formdata['S_EMPLOYER_SUPER'];

//Total Super
$data['total_super']				=	$data['post_tax_super'] + $data['pre_tax_super'] + $data['employer_super'];
$data['s_total_super']				=	$data['s_post_tax_super'] + $data['s_pre_tax_super'] + $data['s_employer_super'];


//Super fund choice
//(index: 0=Other, 1=IOOF)
$data['super_fund']					=	($formdata['IOOF'] ? 'IOOF' : 'Other');
$data['s_super_fund']				=	($formdata['S_IOOF'] ? 'IOOF' : 'Other');

//Monthly Ministry Reimbursements
$data['mmr']						=	$formdata['MMR'];
$data['s_mmr']						=	$formdata['S_MMR'];

//Worker's Compensation
$data['workers_comp']				=	round($data['joint_financial_package'] * $WORKERS_COMP_RATE);


//Ministry Levy
if ($iscouple){
	//calc the amount that the levy should be applied to
	$subtotal = $data['employer_super'] + $data['s_employer_super'] + $data['joint_financial_package'] + $data['workers_comp'] + $data['mmr'] + $data['s_mmr'];
	//grab the levy percentage
	$ministry_row = mysql_fetch_assoc(mysql_query("SELECT * FROM Ministry WHERE MINISTRY_ID='".$data['ministry']."'"));
	$ministry_levy_rate = $ministry_row['MINISTRY_LEVY'];
	$s_ministry_row = mysql_fetch_assoc(mysql_query("SELECT * FROM Ministry WHERE MINISTRY_ID='".$data['s_ministry']."'"));
	$s_ministry_levy_rate = $s_ministry_row['MINISTRY_LEVY'];
	//calc levy (levy is in proportion to the days per week each works)
	$data['ministry_levy']				=	round((($data['days_per_wk']/($data['days_per_wk']+$data['s_days_per_wk'])) * ($ministry_levy_rate / 100) * $subtotal));
	$data['s_ministry_levy']			=	round((($data['days_per_wk']/($data['days_per_wk']+$data['s_days_per_wk'])) * ($s_ministry_levy_rate / 100) * $subtotal));

	//check if both in same ministry - combine the ministry levy rather than duplicate
	if ($data['ministry'] == $data['s_ministry']) {
		$data['ministry_levy'] += $data['s_ministry_levy'];
		$data['s_ministry_levy'] = 0;
	}
} else {
	$subtotal = $data['employer_super'] + $data['joint_financial_package'] + $data['workers_comp'] + $data['mmr'];
	$ministry_row = mysql_fetch_assoc(mysql_query("SELECT * FROM Ministry WHERE MINISTRY_ID='".$data['ministry']."'"));
	$ministry_levy_rate = $ministry_row['MINISTRY_LEVY'];
	$data['ministry_levy']				=	(($ministry_levy_rate / 100) * $subtotal);
}

if($DEBUG) fb($ministry_row);

///////////////////TODO change this code for when multiple session is possible//////////////////////////////////////////////////////////
//atm session is set to be guid but for internal transfers it needs to be FAN so $data['fan'] is used instead of $formdata['session'] //

//Internal Contribution Transfers
$sql = mysql_query("SELECT TRANSFER_NAME,TRANSFER_AMOUNT FROM Internal_Transfers WHERE SESSION_ID='".$data['fan']."'"); //should refer to $formdata['session'] but atm fan is needed so that old transfers can be viewed
for ($i = 0; $i < mysql_num_rows($sql); $i++) {
	$transfers_row = mysql_fetch_assoc($sql);
	$transfer['name'] = $transfers_row['TRANSFER_NAME'];
	$transfer['amount'] = $transfers_row['TRANSFER_AMOUNT'];
	$transfers[$i] = $transfer;
}

$i = count($transfers);
if ($data['ministry_levy'] == '__')
	$data['ministry_levy'] = 0;
if ($data['s_ministry_levy'] == '__')
	$data['s_ministry_levy'] == 0;
//adding the levy to the list of internal transfers
if ($data['ministry_levy'] != 0 && $data['s_ministry_levy'] == 0) {	//if just user (no spouse) with levy
	$transfer['name'] = $data['ministry'];
	$transfer['amount'] = $data['ministry_levy'];
	$transfers[$i] = $transfer;
}
if ($iscouple) {
	if ($data['ministry_levy'] != 0 && $data['s_ministry_levy'] != 0 && $data['ministry'] == $data['ministry']) {	//if both spouses are in the same levy-ing ministry
		$transfer['name'] = $data['ministry'];
		$transfer['amount'] = $data['ministry_levy'] + $data['s_ministry_levy'];
		$transfers[$i] = $transfer;
	}
	if ($data['s_ministry_levy'] != 0 && $data['ministry_levy'] == 0) {	//if just spouse with levy
		$transfer['name'] = $data['s_ministry'];
		$transfer['amount'] = $data['s_ministry_levy'];
		$transfers[$i] = $transfer;
	}
}

//total transfers with minsitry levy
if (count($transfers) > 0)
	foreach($transfers as $r)
	{
		$total_transfers += $r['amount'];
	}
$data['transfers']					=	$transfers;
$data['total_transfers']			=	(is_null($total_transfers) ? 0 : $total_transfers);

//International Donations
if($formdata['INTERNATIONAL_DONATIONS'] < ($subtotal + $transfers_total))
	$data['international_donations']=	$formdata['INTERNATIONAL_DONATIONS'];
else
	$err .= "INTERNATIONAL_DONATIONS:\"This figure must be smaller than your TMN.\", ";

//CCCA Levy								//this has been changed so its not just a percentage of the subtotal but a percentage of the whole TMN
$data['ccca_levy']					=	round(($subtotal + $total_transfers - $data['international_donations']) * ($CCCA_LEVY_RATE/(1-$CCCA_LEVY_RATE)));

//Total Monthly Needs
$data['tmn']						=	round($subtotal + $total_transfers + $data['ccca_levy']);

//Buffer Required
$data['buffer']						=	$data['tmn'] * ($row['MPD'] ? 2 : 1.5);


//Calculate days per week multiplier
$MULTIPLIER = ($iscouple ?(($data['days_per_wk'] + $data['s_days_per_wk']) / 10) : ($data['days_per_wk'] / 5));
//Apply multiplier to limits and bands
$BAND_FP_COUPLE			=	$BAND_FP_COUPLE			*	$MULTIPLIER;
$BAND_FP_SINGLE			=	$BAND_FP_SINGLE			*	$MULTIPLIER;
$BAND_TMN_COUPLE_MIN	=	$BAND_TMN_COUPLE_MIN 	* 	$MULTIPLIER;
$BAND_TMN_COUPLE_MAX	=	$BAND_TMN_COUPLE_MAX	*	$MULTIPLIER;
$BAND_TMN_SINGLE_MIN	=	$BAND_TMN_SINGLE_MIN	*	$MULTIPLIER;
$BAND_TMN_SINGLE_MAX	=	$BAND_TMN_SINGLE_MAX	*	$MULTIPLIER;


//Financial Package Limits
if ($iscouple) {
	if ($data['joint_financial_package'] > $BAND_FP_COUPLE) {
		$data['auth_lv1'] = 1;
		if ($data['joint_financial_package'] < ($BAND_FP_COUPLE * 1.1)) {
			$data['auth_lv2'] = 1;
			$data['auth_lv2_reasons'][count($data['auth_lv2_reasons'])] = array('reason' => 'Joint Financial Package is above limit ($'.$BAND_FP_COUPLE.').');
		}
		else {
			$data['auth_lv3'] = 1;
			$data['auth_lv3_reasons'][count($data['auth_lv3_reasons'])] = array('reason' => 'Joint Financial Package is above limit ($'.$BAND_FP_COUPLE*1.1.').');
		}
	}
}
if (!$iscouple) {
	if ($data['joint_financial_package'] > $BAND_FP_SINGLE) {
		$data['auth_lv1'] = 1;
		if ($data['joint_financial_package'] < ($BAND_FP_SINGLE * 1.1)) {
			$data['auth_lv2'] = 1;
			$data['auth_lv2_reasons'][count($data['auth_lv2_reasons'])] = array('reason' => 'Financial Package is above limit ($'.$BAND_FP_SINGLE.').');
		}
		else
			$data['auth_lv3'] = 1;
			$data['auth_lv3_reasons'][count($data['auth_lv3_reasons'])] = array('reason' => 'Financial Package is more than 10% over limit ($'.$BAND_FP_SINGLE*1.1.').');
	}
}
if($DEBUG) fb($data);

//TMN BANDS
$data['auth_lv1'] = 1;
if ($iscouple) {
	if($data['tmn'] < $BAND_TMN_COUPLE_MIN) {
		$data['auth_lv2'] = 1;
		$data['auth_lv2_reasons'][count($data['auth_lv2_reasons'])] = array('reason' => 'TMN is outside recommended band: Under Minimum ($'.$BAND_TMN_COUPLE_MIN.').');
	}
	if($data['tmn'] > $BAND_TMN_COUPLE_MAX) {
		$data['auth_lv2'] = 1;
		$data['auth_lv2_reasons'][count($data['auth_lv2_reasons'])] = array('reason' => 'TMN is outside recommended band: Over Maximum($'.$BAND_TMN_COUPLE_MAX.').');
	}
}
if (!$iscouple) {
	if($data['tmn'] < $BAND_TMN_SINGLE_MIN) {
		$data['auth_lv2'] = 1;
		$data['auth_lv2_reasons'][count($data['auth_lv2_reasons'])] = array('reason' => 'TMN is outside recommended band: Under Minimum ($'.$BAND_TMN_SINGLE_MIN.').');
	}
	if($data['tmn'] > $BAND_TMN_SINGLE_MAX) {
		$data['auth_lv2'] = 1;
		$data['auth_lv2_reasons'][count($data['auth_lv2_reasons'])] = array('reason' => 'TMN is outside recommended band: Over Maximum ($'.$BAND_TMN_SINGLE_MAX.').');
	}
}

//ADDITIONAL HOUSING AUTH CHECK
if ($data['additional_housing'] != 0) {
	$data['auth_lv1'] = 1;
	$data['auth_lv2'] = 1;
	$data['auth_lv2_reasons'][count($data['auth_lv2_reasons'])] = array('reason' => 'You have an Additional Housing Allowance.');
}

//MPD AUTH CHECK
if ($row['MPD'] == 1) {
	$data['auth_lv1'] = 1;
}

//trim and wrap
//$data['auth_lv2_reasons'] = '['.trim($data['auth_lv2_reasons'], ', ').']';
//$data['auth_lv3_reasons'] = '['.trim($data['auth_lv3_reasons'], ', ').']';


//remove spouse entries if s_firstname is null
foreach ($data as $k=>$v) {
	if (is_null($data['s_firstname']) && substr($k, 0, 2) == 's_')
		unset($data[$k]);
}

//check that net stipend for both spouses is over $100
if ($data['net_stipend'] < $NET_STIPEND_MIN)
	$err .= "NET_STIPEND:\"You cannot have a net stipend less than $".$NET_STIPEND_MIN.".\", ";
if ($data['s_net_stipend'] < $NET_STIPEND_MIN && $iscouple)
	$err .= "S_NET_STIPEND:\"You cannot have a net stipend less than $".$NET_STIPEND_MIN.".\", ";
	
//check that housing is less than total mfbs
//if ($data['housing'] > ($data['mfb']+$data['s_mfb']))
//	$err .= "HOUSING:\"You cannot have a housing amount greater than your total MFB\'s ($".($data['mfb']+$data['s_mfb']).").\", ";


if ($err == '') {
	$result = array('success'=>'true');
	$result['tmn_data'] = $data;
	echo json_encode($result);
	
	if($DEBUG) fb($formdata);
	if($DEBUG) fb($data);
	
	//echo '{success: true}';
}
else {
	echo '{success: false, errors:{'.trim($err,", ").'} }'; //Return with errors
}

?>