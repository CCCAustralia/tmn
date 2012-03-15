<?php

function getVersionNumber() {
	
	return "2-2-0";
	
}

function getVersionNumberAsArray() {
	
	return array("VERSIONNUMBER" => getVersionNumber());
	
}

function getConstants($versionNumber) {
	
	$keyarray		= array(	
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
	
	$tax_keyarray	= array(
	
	);
	
	if (file_exists('dbconnect.php')) {			include_once 'dbconnect.php';}
	if (file_exists('../dbconnect.php')) {		include_once '../dbconnect.php';}
	if (file_exists('php/dbconnect.php')){		include_once 'php/dbconnect.php';}
	
	$connection = db_connect();
	
	//grab constants
	$sql = "SELECT * FROM `Constants` WHERE VERSIONNUMBER = '$versionNumber'";
	$sql = mysql_query($sql);
	$db_values = mysql_fetch_assoc($sql);
	$returnarray = array();
	foreach ($keyarray as $key) {
		$returnarray[$key] = $db_values[$key];
	}
	
	//grab tax constants
	$sql = "SELECT * FROM `Constants_Tax` WHERE VERSIONNUMBER = '$versionNumber'";
	$sql = mysql_query($sql);
	//go through each row (ie each tax variable, which is an array of tax band constants)
	while ($row = mysql_fetch_assoc($sql)) {
		
		//for each variable generate an array of each of the band values from this row
		$rowArray	= array();
		
		for ($bandCount = 1; $bandCount <= 9; $bandCount++) {
			
			$fieldName = "BAND$bandCount";
			
			if ( ($bandCount == 1) || ($bandCount > 1 && $row[$fieldName] > 0) ) {
				
				$rowArray[$bandCount - 1] = $row[$fieldName];
				
			}
			
		}
		
		//put the array into the return array
		$returnarray[$row["NAME"]] = $rowArray;
		
	}
	
	return $returnarray;
}


?>