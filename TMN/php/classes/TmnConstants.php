<?php

function getVersionNumber() {
	
	return "2-2-0";
	
}

function getVersionNumberAsArray() {
	
	return array("VERSIONNUMBER" => getVersionNumber());
	
}

function getConstants($versionNumber, $resident = true) {
	
	$keyArray		= array(	
						"STIPEND_MIN",
						"MIN_SUPER_RATE",
						"MIN_ADD_SUPER_RATE",
						"OS_STIPEND_MAX",
						"MAX_HOUSING_MFB",
						"MAX_HOUSING_MFB_COUPLES",
						"WORKERS_COMP_RATE",
						"CCCA_LEVY_RATE",
						"BAND_FP_COUPLE",
						"BAND_FP_SINGLE",
						"BAND_TMN_COUPLE_MIN",
						"BAND_TMN_COUPLE_MAX",
						"BAND_TMN_SINGLE_MIN",
						"BAND_TMN_SINGLE_MAX",
						"FINANCE_USER"
	);
	
	if (file_exists('dbconnect.php')) {			include_once 'dbconnect.php';}
	if (file_exists('../dbconnect.php')) {		include_once '../dbconnect.php';}
	if (file_exists('php/dbconnect.php')){		include_once 'php/dbconnect.php';}
	
	$connection = db_connect();
	
	//grab constants
	$sql = "SELECT * FROM `Constants` WHERE VERSIONNUMBER = '$versionNumber'";
	$sql = mysql_query($sql);
	$db_values = mysql_fetch_assoc($sql);
	$returnArray = array();
	
	foreach ($keyArray as $key) {
		$returnArray[$key] = $db_values[$key];
	}
	
	//grab tax constants
	
	//grab x, a and b values for a resident

	$x_resident	=	array();
	for ($xResidentBandCount = 1; $xResidentBandCount <= 8; $xResidentBandCount++) {
		$x_resident[$xResidentBandCount - 1] = $db_values["x_resident_band_$xResidentBandCount"];
	}
	$returnArray["x_resident"] = $x_resident;
	
	$a_resident	=	array();
	for ($aResidentBandCount = 1; $aResidentBandCount <= 9; $aResidentBandCount++) {
		$a_resident[$aResidentBandCount - 1] = $db_values["a_resident_band_$aResidentBandCount"];
	}
	$returnArray["a_resident"] = $a_resident;
	
	$b_resident	=	array();
	for ($bResidentBandCount = 1; $bResidentBandCount <= 9; $bResidentBandCount++) {
		$b_resident[$bResidentBandCount - 1] = $db_values["b_resident_band_$bResidentBandCount"];
	}
	$returnArray["b_resident"] = $b_resident;
	
	$x_non_resident	=	array();
	for ($xNonResidentBandCount = 1; $xNonResidentBandCount <= 4; $xNonResidentBandCount++) {
		$x_non_resident[$xNonResidentBandCount - 1] = $db_values["x_non_resident_band_$xNonResidentBandCount"];
	}
	$returnArray["x_non_resident"] = $x_non_resident;
	
	$a_non_resident	=	array();
	for ($aNonResidentBandCount = 1; $aNonResidentBandCount <= 4; $aNonResidentBandCount++) {
		$a_non_resident[$aNonResidentBandCount - 1] = $db_values["a_non_resident_band_$aNonResidentBandCount"];
	}
	$returnArray["a_non_resident"] = $a_non_resident;
	
	$b_non_resident	=	array();
	for ($bNonResidentBandCount = 1; $bNonResidentBandCount <= 4; $bNonResidentBandCount++) {
		$b_non_resident[$bNonResidentBandCount - 1] = $db_values["b_non_resident_band_$bNonResidentBandCount"];
	}
	$returnArray["b_non_resident"] = $b_non_resident;
	
	
	
	return $returnArray;
}


?>