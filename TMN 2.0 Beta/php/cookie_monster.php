<?php
//TESTORLOLOLOLOL
$DEBUG = 1;

include_once("dbconnect.php");
include_once("./calc/calc_tax.php");
include_once("./calc/calc_mfbmax.php");
include_once("./calc/calc_employersuper.php");
include_once("./calc/calc_additionalhousing.php");
if($DEBUG) require_once("../lib/FirePHPCore/fb.php");

$financial_data = json_decode(stripslashes($_POST['financial_data']), true);

$COOKIEPATH = "/dev/TMN/";
$STIPEND_MIN = 100;
$MIN_ADD_SUPER_RATE = 0.09;

$connection = db_connect();

//if there is a spouse get the guid
if ($financial_data['spouse']){
	$sql = mysql_query("SELECT SPOUSE_GUID FROM User_Profiles WHERE guid='".$financial_data['guid']."'");
	if (mysql_num_rows($sql) == 1) {
		$row = mysql_fetch_assoc($sql);
		$financial_data['spouse'] = $row['SPOUSE_GUID'];
	}
}

//Taxable Income Panel
if (isset($financial_data['NET_STIPEND'])){
	if ($financial_data['NET_STIPEND'] < $STIPEND_MIN)
		$err .= "\"NET_STIPEND\":\"Net Stipend is too low: must be at least $".$STIPEND_MIN.".\", ";
	
	$annum = ($financial_data['NET_STIPEND'] * 12) + ($financial_data['POST_TAX_SUPER'] * 12) + ($financial_data['ADDITIONAL_TAX'] * 12);	//calculate yearly figure
	
	$financial_data['TAXABLE_INCOME'] = calculateTaxableIncome($annum);
	$financial_data['TAX'] = calculateTax($financial_data['TAXABLE_INCOME'], 'resident');
	$financial_data['EMPLOYER_SUPER'] = calculateEmployerSuper($financial_data['TAXABLE_INCOME']);
	
	$financial_data['TAXABLE_INCOME'] = round($financial_data['TAXABLE_INCOME'] / 12);
	$financial_data['TAX'] = round($financial_data['TAX'] / 12);
    $financial_data['EMPLOYER_SUPER'] = round($financial_data['EMPLOYER_SUPER'] / 12);
}

//Maximum MFB & Pre-tax Super
if (isset($financial_data['TAXABLE_INCOME'])) {

	//enumerate mfb rate
	switch ($financial_data['MFB_RATE']) {
		case 0:
		//Zero MFBs
			$mfbrate = 0;
			break;
		case 1:
		//Half MFBs
			$mfbrate = 0.5;
			break;
		case 2:
		//Full MFBs
			$mfbrate = 1;
			break;
	}
	
	//Pre Tax Super (if its not set then set it to the min
	$min_pre_tax_super = round($financial_data['TAXABLE_INCOME'] * $mfbrate * $MIN_ADD_SUPER_RATE);
	if (!isset($financial_data['PRE_TAX_SUPER']) || $financial_data['PRE_TAX_SUPER'] < $min_pre_tax_super){
		$financial_data['PRE_TAX_SUPER'] = $min_pre_tax_super;
	}
	
	//Fetch the user's days per week
	$sql = mysql_query("SELECT DAYS_PER_WEEK, FT_PT_OS FROM User_Profiles WHERE guid='".$financial_data['guid']."'");
	if (mysql_num_rows($sql) == 1) {
		$row = mysql_fetch_assoc($sql);
		if ($row['FT_PT_OS'] == 0){
			$financial_data['DAYS_PER_WEEK'] = 4;
		} else {
			$financial_data['DAYS_PER_WEEK'] = $row['DAYS_PER_WEEK'];
		}
	}
	
	$financial_data['MAX_MFB'] = round(calculateMaxMFB($financial_data['TAXABLE_INCOME'], $mfbrate, $financial_data['DAYS_PER_WEEK'] + 1)); //+1 because days per week is stored as an index not a number
}

//Housing
if (isset($financial_data['HOUSING'])){
	if (!isset($financial_data['HOUSING_FREQUENCY'])) $financial_data['HOUSING_FREQUENCY'] = 0;
	$financial_data['ADDITIONAL_HOUSING'] = calculateAdditionalHousing($financial_data['HOUSING'], $financial_data['HOUSING_FREQUENCY'], $financial_data['spouse']);
}


//Spouse Taxable Income Panel
if (isset($financial_data['S_NET_STIPEND'])){
	if ($financial_data['S_NET_STIPEND'] < $STIPEND_MIN)
		$err .= "\"S_NET_STIPEND\":\"Spouse Net Stipend is too low: must be at least $".$STIPEND_MIN.".\", ";
	
	$s_annum = ($financial_data['S_NET_STIPEND'] * 12) + ($financial_data['S_POST_TAX_SUPER'] * 12) + ($financial_data['S_ADDITIONAL_TAX'] * 12);	//calculate yearly figure
	
	$financial_data['S_TAXABLE_INCOME'] = calculateTaxableIncome($s_annum);
	$financial_data['S_TAX'] = calculateTax($financial_data['S_TAXABLE_INCOME'], 'resident');
	$financial_data['S_EMPLOYER_SUPER'] = calculateEmployerSuper($financial_data['S_TAXABLE_INCOME']);
	
	$financial_data['S_TAXABLE_INCOME'] = round($financial_data['S_TAXABLE_INCOME'] / 12);
	$financial_data['S_TAX'] = round($financial_data['S_TAX'] / 12);
    $financial_data['S_EMPLOYER_SUPER'] = round($financial_data['S_EMPLOYER_SUPER'] / 12);
}

//Spouse Maximum MFB && Pre Tax Super
if (isset($financial_data['S_TAXABLE_INCOME'])) {

	//enumerate mfb rate
	switch ($financial_data['S_MFB_RATE']) {
		case 0:
		//Zero MFBs
			$mfbrate = 0;
			break;
		case 1:
		//Half MFBs
			$mfbrate = 0.5;
			break;
		case 2:
		//Full MFBs
			$mfbrate = 1;
			break;
	}
	
	//Pre Tax Super (if its not set then set it to the min
	$s_min_pre_tax_super = round($financial_data['S_TAXABLE_INCOME'] * $mfbrate * $MIN_ADD_SUPER_RATE);
	if (!isset($financial_data['S_PRE_TAX_SUPER']) || $financial_data['S_PRE_TAX_SUPER'] < $s_min_pre_tax_super){
		$financial_data['S_PRE_TAX_SUPER'] = $s_min_pre_tax_super;
	}
	
	//Fetch the user's days per week
	$sql = mysql_query("SELECT DAYS_PER_WEEK FT_PT_OS FROM User_Profiles WHERE guid='".$financial_data['spouse']."'"); //needs to change ($financial_data['spouse'] doesn't hold spouse guid)
	if (mysql_num_rows($sql) == 1) {
		$row = mysql_fetch_assoc($sql);
		if ($row['FT_PT_OS'] == 0){
			$financial_data['S_DAYS_PER_WEEK'] = 4;
		} else {
			$financial_data['S_DAYS_PER_WEEK'] = $row['DAYS_PER_WEEK'];
		}
	}
	
	$financial_data['S_MAX_MFB'] = round(calculateMaxMFB($financial_data['S_TAXABLE_INCOME'], $mfbrate, $financial_data['S_DAYS_PER_WEEK'] + 1)); //+1 because days per week is stored as an index not a number
}

if ($DEBUG) fb($financial_data);

if ($err == '') {
	/*
	if ($financial_data['TAXABLE_INCOME'] > 0 && $financial_data['NET_STIPEND'] > 0) {
		//setcookie('NET_STIPEND', $netstipend, 0, $COOKIEPATH);
		$financial_data['taxable_income'] = $taxableincome;
		//setcookie('TAXABLE_INCOME', $taxableincome, 0,$COOKIEPATH);
   		//setcookie('TAX', $tax, 0, $COOKIEPATH);
		//setcookie('DAYS_PER_WEEK', $daysperweek, 0, $COOKIEPATH);
		//setcookie('MAX_MFB', $maxmfb, 0, $COOKIEPATH);
		//setcookie('EMPLOYER_SUPER', $employersuper, 0, $COOKIEPATH);
	}
	
	if ($s_taxableincome > 0 && $s_netstipend > 0) {
		setcookie('S_NETSTIPEND', $s_netstipend, 0, $COOKIEPATH);
		setcookie('S_TAXABLE_INCOME', $s_taxableincome, 0,$COOKIEPATH);
    	setcookie('S_TAX', $s_tax, 0, $COOKIEPATH);
		setcookie('S_DAYS_PER_WEEK', $s_daysperweek, 0, $COOKIEPATH);
		setcookie('S_MAX_MFB', $s_maxmfb, 0, $COOKIEPATH);
		setcookie('S_EMPLOYER_SUPER', $s_employersuper, 0, $COOKIEPATH);
	}
	
	if ($housing > 0) {
		setcookie('HOUSING', $housing, 0, $COOKIEPATH);
		setcookie('ADDITIONAL_HOUSING', $additionalhousing, 0, $COOKIEPATH);
	}
	*/

	$result = array('success'=>'true');
	$result['financial_data'] = $financial_data;
	echo json_encode($result);
	
	//echo "$s_maxmfb, $s_taxableincome, $s_mfbrate, $s_daysperweek";
	
	
}
else {
	echo '{"success": false, "errors":{'.trim($err,", ").'} }';	//Return with errors
}
?>