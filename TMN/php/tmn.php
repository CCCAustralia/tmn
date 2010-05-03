<?php
include_once "logger.php";
include_once "dbconnect.php";
include_once "taxcalc.php";

$LOGFILE = "./logs/tmn.log";

$connection = @db_connect();

$guid = 'testuserguid';//$_REQUEST['guid'];
$mode = $_REQUEST['mode'];		//get/set
$method = $_REQUEST['method'];	//values(fieldnames)


//GET MODE
if ($mode == 'get') {
	$getsql = "SELECT ";	//the sql statement for get
	
	//modified for catch-all get method
	$method = "";
	
	//sql statement for selective field requests
	$getsql .= $method;
	
	////Loop through the requested values, test the prefix, then replace the label with the appropriate sql SELECT sub-statement
	////Probably not needed - if all values in the same table.
	//$method_arr = explode(",", $method);
	//foreach ($method_arr as $v) {
		
	
		/*
		if (substr($v, 0, 2) == 'S_')
			$getsql = str_replace($v, "(SELECT ".substr($v, 2)." FROM User_Profiles WHERE FIN_ACC_NUM=(SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID='".$guid."') && GUID !='".$guid."')", $getsql);
			//TODO: change the table from which the values are retrieved - depending on where the values are acutally stored (@ when the database is finalised)
			//NOTE: S_ may not actually be required - if the one session row contains all values - for both the user and partner.
			*/
				
	//}
	
	$getsql .= " FROM Sessions WHERE SESSION_ID";
}




//SET MODE
if ($mode == 'set') {
	
	//echo 'TAX: '.calculateTax(40000, 'resident');
	echo calculateTax($method, 'resident');
}

function set_MFB_label($isoverseas) {
	if ($isoverseas == true)
		setcookie("MFBlabel", "LAFHA");
	else
		setcookie("MFBlabel", "MFBs");
}


?>