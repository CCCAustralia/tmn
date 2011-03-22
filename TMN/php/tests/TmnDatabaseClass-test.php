<?php

/*******************************************                                                        
# Test Code                 
*******************************************/

include_once("../classes/TmnDatabase.php");
$LOGFILE	= "../logs/TmnDatabaseClass-test.log";
$DEBUG = 1;

	//Constructor test

fb("Constructor Test");
try {
	$obj	= TmnDatabase::getInstance($LOGFILE);
} catch (Exception $e) {
	Reporter::newInstance($LOGFILE)->exceptionHandler($e);
}

/*
 * Expected output
 * 
 * Console Output:
 * Constructor Test
 * 
 * Screen Output:
 * 
 */

/*
 * Expected output (With mysql server turned off)
 * 
 * Console Output:
 * Constructor Test
 * <filename>; ln <line num>; Fatal Exception; Database Exception: Cannot Connect; Error no.: 
 * 
 * Screen Output:
 * 
 * exception.log Output
 * <filename>; ln <line num>; Fatal Exception; Database Exception: Cannot Connect; Error no.: 
 * 
 */

	//Database test

fb("Database Test");

fb("disconnect");
$obj->disconnect();
fb("connect");
$obj->connect();

fb("CREATE");
fb("preparedQuery(sql, values, types)");
$table_name	= "Tmn_Sessions";
$sql		= "INSERT INTO " . $table_name . "(`FAN`, `GUID`, ";
$types		= "is";
$values		= array(":fan" => 1012299, ":guid" => "691EC152-0565-CEF4-B5D8-99286252652B");
$valueCount	= 2;

$session = array(
		'home_assignment_session_id'			=>	null,
		'international_assignment_session_id'	=>	null,
		'date_modified'							=>	"2011-1-12",
		'os_assignment_start_date'				=>	"2011-5-12",
		'os_assignment_end_date'				=>	"2011-6-12",
		'os_resident_for_tax_purposes'			=>	"Resident of Australia",
		'net_stipend'							=>	1111,
		'tax'									=>	5,
		'additional_tax'						=>	0,
		'post_tax_super'						=>	0,
		'taxable_income'						=>	1234,
		'pre_tax_super'							=>	0,
		'additional_life_cover'					=>	0,
		'mfb'									=>	0,
		'additional_housing_allowance'			=>	0,
		'os_overseas_housing_allowance'			=>	0,
		'financial_package'						=>	0,
		'employer_super'						=>	1,
		'mmr'									=>	1,
		'stipend'								=>	1,
		'housing_stipend'						=>	1,
		'housing_mfb'							=>	1,
		'mfb_rate'								=>	"Full",
		'claimable_mfb'							=>	1,
		'total_super'							=>	1,
		'resc'									=>	1,
		'super_fund'							=>	"IOOF",
		'income_protection_cover_source'		=>	"Support Account",
		's_net_stipend'							=>	1,
		's_tax'									=>	1,
		's_additional_tax'						=>	1,
		's_post_tax_super'						=>	1,
		's_taxable_income'						=>	1,
		's_pre_tax_super'						=>	1,
		's_additional_life_cover'				=>	1,
		's_mfb'									=>	1,
		's_additional_housing_allowance'		=>	1,
		's_os_overseas_housing_allowance'		=>	1,
		's_financial_package'					=>	1,
		's_employer_super'						=>	1,
		's_mmr'									=>	1,
		's_stipend'								=>	1,
		's_housing_stipend'						=>	1,
		's_housing_mfb'							=>	1,
		's_mfb_rate'							=>	"Half",
		's_claimable_mfb'						=>	1,
		's_total_super'							=>	1,
		's_resc'								=>	1,
		's_super_fund'							=>	"Other",
		's_income_protection_cover_source'		=>	"Support Account",
		'joint_financial_package'				=>	1,
		'total_transfers'						=>	1,
		'workers_comp'							=>	1,
		'ccca_levy'								=>	1,
		'tmn'									=>	1,
		'buffer'								=>	1,
		'international_donations'				=>	1,
		'additional_housing'					=>	1,
		'monthly_housing'						=>	1,
		'housing'								=>	1,
		'housing_frequency'						=>	"Monthly"
);

$session_type = array(
		'home_assignment_session_id'			=>	"i",
		'international_assignment_session_id'	=>	"i",
		'date_modified'							=>	"s",
		'os_assignment_start_date'				=>	"s",
		'os_assignment_end_date'				=>	"s",
		'os_resident_for_tax_purposes'			=>	"s",
		'net_stipend'							=>	"i",
		'tax'									=>	"i",
		'additional_tax'						=>	"i",
		'post_tax_super'						=>	"i",
		'taxable_income'						=>	"i",
		'pre_tax_super'							=>	"i",
		'additional_life_cover'					=>	"i",
		'mfb'									=>	"i",
		'additional_housing_allowance'			=>	"i",
		'os_overseas_housing_allowance'			=>	"i",
		'financial_package'						=>	"i",
		'employer_super'						=>	"i",
		'mmr'									=>	"i",
		'stipend'								=>	"i",
		'housing_stipend'						=>	"i",
		'housing_mfb'							=>	"i",
		'mfb_rate'								=>	"s",
		'claimable_mfb'							=>	"i",
		'total_super'							=>	"i",
		'resc'									=>	"i",
		'super_fund'							=>	"s",
		'income_protection_cover_source'		=>	"s",
		's_net_stipend'							=>	"i",
		's_tax'									=>	"i",
		's_additional_tax'						=>	"i",
		's_post_tax_super'						=>	"i",
		's_taxable_income'						=>	"i",
		's_pre_tax_super'						=>	"i",
		's_additional_life_cover'				=>	"i",
		's_mfb'									=>	"i",
		's_additional_housing_allowance'		=>	"i",
		's_os_overseas_housing_allowance'		=>	"i",
		's_financial_package'					=>	"i",
		's_employer_super'						=>	"i",
		's_mmr'									=>	"i",
		's_stipend'								=>	"i",
		's_housing_stipend'						=>	"i",
		's_housing_mfb'							=>	"i",
		's_mfb_rate'							=>	"s",
		's_claimable_mfb'						=>	"i",
		's_total_super'							=>	"i",
		's_resc'								=>	"i",
		's_super_fund'							=>	"s",
		's_income_protection_cover_source'		=>	"s",
		'joint_financial_package'				=>	"i",
		'total_transfers'						=>	"i",
		'workers_comp'							=>	"i",
		'ccca_levy'								=>	"i",
		'tmn'									=>	"i",
		'buffer'								=>	"i",
		'international_donations'				=>	"i",
		'additional_housing'					=>	"i",
		'monthly_housing'						=>	"i",
		'housing'								=>	"i",
		'housing_frequency'						=>	"s"
);

foreach ($session as $key=>$value) {
	if ($value != NULL) {
		$sql					.=	"`" . strtoupper($key) . "`, ";
	}
}

$sql = trim($sql, ", ") . ") VALUES ( :fan, :guid, ";

foreach ($session as $key=>$value) {
	//if ($session_type[$key]) {throw new LightException("Session Exception: Type mismatch on INSERT. " . $key . "=" . $value . ". It should be of type: " . $session_type[$key]);}
	if ($value != NULL) {
		$variableName			 =	":" . $key;
		$sql					.=	$variableName . ", ";
		$values[$variableName]	 =	$session[$key];
	}
}

$sql = trim($sql, ", ") . ")";

fb($sql);
fb($values);
$stmt		= $obj->prepare($sql);
$stmt->execute($values);
$session_id	= $obj->lastInsertId();
fb($session_id);

fb("RETRIEVE");
fb("preparedSelect(sql, values, types, resultTypes)");
$sql			= "SELECT `MINISTRY_ID`, `MINISTRY_LEVY` FROM `Ministry` WHERE `MINISTRY_ID` = :session_id";
$values 		= "StudentLife";
$types			= "s";
$resultTypes	= "si";

fb($sql);
fb($values);
$stmt	= $obj->prepare($sql);
$stmt->bindParam(":session_id", &$values);
$stmt->execute();
fb($stmt->fetch(PDO::FETCH_ASSOC));

fb("UPDATE");
fb("preparedQuery(sql, values, types)");
$sql				= "UPDATE `" . $table_name . "` SET ";
$types				= "";
$values				= array();
$valueCount			= 0;

$session['tax'] = 55555;

foreach ($session as $key=>$value) {
	if ($value != NULL) {
		$variableName			 =	":" . $key;
		$sql					.= "`" . strtoupper($key) . "` = " . $variableName . ", ";
		$values[$variableName]	 =	$session[$key];
	}
}

$sql				 = trim($sql, ", ");
$sql				.= " WHERE `SESSION_ID` = :session_id";
$values[":session_id"]	 = $session_id;

fb($sql);
fb($values);
$stmt	= $obj->prepare($sql);
$stmt->execute($values);


fb("DELETE");
fb("preparedQuery(sql, values, types)");
$sql					= "DELETE FROM `" . $table_name . "` WHERE `SESSION_ID` = :session_id";
$values					= array(":session_id" => $session_id);
		
fb($sql);
fb($values);
$stmt	= $obj->prepare($sql);
$stmt->execute($values);


/*
 * Expected output
 * 
 * Console Output:
 * Database Test
 * disconnect
 * connect
 * CREATE
 * preparedQuery(sql, values, types)
 * INSERT INTO Tmn_Sessions(`FAN`, `GUID`, `DATE_MODIFIED`, `OS_ASSIGNMENT_START_DATE`, `OS_ASSIGNMENT_END_DATE`, `OS_RESIDENT_FOR_TAX_PURPOSES`, `NET_STIPEND`, `TAX`, `TAXABLE_INCOME`, `EMPLOYER_SUPER`, `MMR`, `STIPEND`, `HOUSING_STIPEND`, `HOUSING_MFB`, `MFB_RATE`, `CLAIMABLE_MFB`, `TOTAL_SUPER`, `RESC`, `SUPER_FUND`, `INCOME_PROTECTION_COVER_SOURCE`, `S_NET_STIPEND`, `S_TAX`, `S_ADDITIONAL_TAX`, `S_POST_TAX_SUPER`, `S_TAXABLE_INCOME`, `S_PRE_TAX_SUPER`, `S_ADDITIONAL_LIFE_COVER`, `S_MFB`, `S_ADDITIONAL_HOUSING_ALLOWANCE`, `S_OS_OVERSEAS_HOUSING_ALLOWANCE`, `S_FINANCIAL_PACKAGE`, `S_EMPLOYER_SUPER`, `S_MMR`, `S_STIPEND`, `S_HOUSING_STIPEND`, `S_HOUSING_MFB`, `S_MFB_RATE`, `S_CLAIMABLE_MFB`, `S_TOTAL_SUPER`, `S_RESC`, `S_SUPER_FUND`, `S_INCOME_PROTECTION_COVER_SOURCE`, `JOINT_FINANCIAL_PACKAGE`, `TOTAL_TRANSFERS`, `WORKERS_COMP`, `CCCA_LEVY`, `TMN`, `BUFFER`, `INTERNATIONAL_DONATIONS`, `ADDITIONAL_HOUSING`, `MONTHLY_HOUSING`, `HOUSING`, `HOUSING_FREQUENCY`) VALUES ( :fan, :guid, :date_modified, :os_assignment_start_date, :os_assignment_end_date, :os_resident_for_tax_purposes, :net_stipend, :tax, :taxable_income, :employer_super, :mmr, :stipend, :housing_stipend, :housing_mfb, :mfb_rate, :claimable_mfb, :total_super, :resc, :super_fund, :income_protection_cover_source, :s_net_stipend, :s_tax, :s_additional_tax, :s_post_tax_super, :s_taxable_income, :s_pre_tax_super, :s_additional_life_cover, :s_mfb, :s_additional_housing_allowance, :s_os_overseas_housing_allowance, :s_financial_package, :s_employer_super, :s_mmr, :s_stipend, :s_housing_stipend, :s_housing_mfb, :s_mfb_rate, :s_claimable_mfb, :s_total_super, :s_resc, :s_super_fund, :s_income_protection_cover_source, :joint_financial_package, :total_transfers, :workers_comp, :ccca_levy, :tmn, :buffer, :international_donations, :additional_housing, :monthly_housing, :housing, :housing_frequency)
 * array(':fan'=> 1012299, ':guid'=>'691EC152-0565-CEF4-B5D8-99286252652B', ':date_modified'=> ... )
 * 21
 * RETRIEVE
 * preparedSelect(sql, values, types, resultTypes)
 * SELECT `MINISTRY_ID`, `MINISTRY_LEVY` FROM `Ministry` WHERE `MINISTRY_ID` = :session_id
 * StudentLife
 * array('MINISTRY_ID'=>'StudentLife', 'MINISTRY_LEVY'=>'2')
 * UPDATE
 * preparedQuery(sql, values, types)
 * UPDATE `Tmn_Sessions` SET `DATE_MODIFIED` = :date_modified, `OS_ASSIGNMENT_START_DATE` = :os_assignment_start_date, `OS_ASSIGNMENT_END_DATE` = :os_assignment_end_date, `OS_RESIDENT_FOR_TAX_PURPOSES` = :os_resident_for_tax_purposes, `NET_STIPEND` = :net_stipend, `TAX` = :tax, `TAXABLE_INCOME` = :taxable_income, `EMPLOYER_SUPER` = :employer_super, `MMR` = :mmr, `STIPEND` = :stipend, `HOUSING_STIPEND` = :housing_stipend, `HOUSING_MFB` = :housing_mfb, `MFB_RATE` = :mfb_rate, `CLAIMABLE_MFB` = :claimable_mfb, `TOTAL_SUPER` = :total_super, `RESC` = :resc, `SUPER_FUND` = :super_fund, `INCOME_PROTECTION_COVER_SOURCE` = :income_protection_cover_source, `S_NET_STIPEND` = :s_net_stipend, `S_TAX` = :s_tax, `S_ADDITIONAL_TAX` = :s_additional_tax, `S_POST_TAX_SUPER` = :s_post_tax_super, `S_TAXABLE_INCOME` = :s_taxable_income, `S_PRE_TAX_SUPER` = :s_pre_tax_super, `S_ADDITIONAL_LIFE_COVER` = :s_additional_life_cover, `S_MFB` = :s_mfb, `S_ADDITIONAL_HOUSING_ALLOWANCE` = :s_additional_housing_allowance, `S_OS_OVERSEAS_HOUSING_ALLOWANCE` = :s_os_overseas_housing_allowance, `S_FINANCIAL_PACKAGE` = :s_financial_package, `S_EMPLOYER_SUPER` = :s_employer_super, `S_MMR` = :s_mmr, `S_STIPEND` = :s_stipend, `S_HOUSING_STIPEND` = :s_housing_stipend, `S_HOUSING_MFB` = :s_housing_mfb, `S_MFB_RATE` = :s_mfb_rate, `S_CLAIMABLE_MFB` = :s_claimable_mfb, `S_TOTAL_SUPER` = :s_total_super, `S_RESC` = :s_resc, `S_SUPER_FUND` = :s_super_fund, `S_INCOME_PROTECTION_COVER_SOURCE` = :s_income_protection_cover_source, `JOINT_FINANCIAL_PACKAGE` = :joint_financial_package, `TOTAL_TRANSFERS` = :total_transfers, `WORKERS_COMP` = :workers_comp, `CCCA_LEVY` = :ccca_levy, `TMN` = :tmn, `BUFFER` = :buffer, `INTERNATIONAL_DONATIONS` = :international_donations, `ADDITIONAL_HOUSING` = :additional_housing, `MONTHLY_HOUSING` = :monthly_housing, `HOUSING` = :housing, `HOUSING_FREQUENCY` = :housing_frequency WHERE `SESSION_ID` = :session_id
 * array(':date_modified'=>'2011-1-12', ':os_assignment_start_date'=>'2011-5-12', ':os_assignment_end_date'=> ... )
 * DELETE
 * preparedQuery(sql, values, types)
 * DELETE FROM `Tmn_Sessions` WHERE `SESSION_ID` = :session_id
 * array(':session_id'=>'21')
 * 
 * Screen Output:
 * 
 */

	//Singleton test

fb("Singleton Test");
fb("this:"); fb($obj);
fb("::getInstance():"); fb(TmnDatabase::getInstance($LOGFILE));
fb("clone obj: ");
try {
	clone $obj;
} catch (Exception $e) {
	Reporter::newInstance($LOGFILE)->exceptionHandler($e);
}

/*
 * Expected output
 * 
 * Console Output:
 * Singleton Test
 * this:
 * <instance of TmnAuth with your GCX data>
 * ::getInstance():
 * <same instance of TmnAuth with your GCX data>
 * clone authObj:
 * <filename>; ln <line num>; Light Exception; Database Exception: TmnDatabase Cannot be cloned
 * 
 * Screen Output:
 * 
 * exception.log Output
 * <filename>; ln <line num>; Light Exception; Database Exception: TmnDatabase Cannot be cloned
 * 
 */

?>
