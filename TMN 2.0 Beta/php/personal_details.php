<?php
$DEBUG = 0;
include_once "logger.php";
include_once "dbconnect.php";
if($DEBUG) require_once("../lib/FirePHPCore/fb.php");

$LOGFILE = "./logs/personal_details.log";

$connection = @db_connect();	//set up the database connection

if ($_POST['guid'] != "")
	$guid = $_POST['guid'];
else
	$guid = 'testuserguid';//$_REQUEST['guid'];
$mode = $_REQUEST['mode'];		//get/set
$method = $_REQUEST['method'];	//values(fieldnames)

//GET MODE
if ($mode == 'get') {
	$getsql = "SELECT ";	//the sql statement for get
	
	//modified for catch-all get method
	$method = "FIRSTNAME,SURNAME,MINISTRY,FT_PT_OS,DAYS_PER_WEEK,S_FIRSTNAME,S_SURNAME,S_MINISTRY,S_FT_PT_OS,S_DAYS_PER_WEEK,MPD,M_FIRSTNAME,M_SURNAME";
	
	//	sql statement for selective field requests
	$getsql .= $method;
	
	//Loop through the requested values, test the prefix, then replace the label with the appropriate sql SELECT sub-statement
	$method_arr = explode(",", $method);
	foreach ($method_arr as $v) {
		if (substr($v, 0, 2) == 'S_') {
			$getsql = str_replace($v, "(SELECT ".substr($v, 2)." FROM User_Profiles WHERE FIN_ACC_NUM=(SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID='".$guid."') && GUID !='".$guid."')",$getsql);
		}
		if (substr($v, 0, 2) == 'M_') {
			$getsql = str_replace($v, "(SELECT ".substr($v, 2)." FROM User_Profiles WHERE GUID=(SELECT M_GUID FROM User_Profiles WHERE GUID='".$guid."'))",$getsql);
		}
	}
	
	//MANUAL SUBSTATEMENT REPLACEMENT:
	/*
	$getsql = str_replace("S_FIRSTNAME",	"(SELECT FIRSTNAME FROM User_Profiles WHERE FIN_ACC_NUM=(SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID='".$guid."') && GUID !='".$guid."')",$getsql);
	$getsql = str_replace("S_SURNAME",		"(SELECT SURNAME FROM User_Profiles WHERE FIN_ACC_NUM=(SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID='".$guid."') && GUID !='".$guid."')",$getsql);
	$getsql = str_replace("S_MINISTRY",		"(SELECT MINISTRY FROM User_Profiles WHERE FIN_ACC_NUM=(SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID='".$guid."') && GUID != '".$guid."')",$getsql);
	$getsql = str_replace("S_FT_PT_OS",		"(SELECT FT_PT_OS FROM User_Profiles WHERE guid=(SELECT SPOUSE_GUID FROM User_Profiles WHERE guid='".$guid."'))",$getsql);
	$getsql = str_replace("S_DAYS_PER_WEEK","(SELECT DAYS_PER_WEEK FROM User_Profiles WHERE guid=(SELECT SPOUSE_GUID FROM User_Profiles WHERE guid='".$guid."'))",$getsql);
	$getsql = str_replace("M_FIRSTNAME",	"(SELECT FIRSTNAME FROM User_Profiles WHERE GUID=(SELECT M_GUID FROM User_Profiles WHERE GUID='".$guid."'))",$getsql);
	$getsql = str_replace("M_SURNAME",		"(SELECT SURNAME FROM User_Profiles WHERE GUID=(SELECT M_GUID FROM User_Profiles WHERE GUID='".$guid."'))",$getsql);
	*/
	
	$getsql = trim($getsql, ", ")." FROM User_Profiles WHERE GUID='".$guid."';";		//Form the sql statement
	
	
	if (!$DEBUG) logToFile($LOGFILE, "GET: ".$getsql);														//Log the sql statement
	//$getsql .= " SELECT FIRSTNAME, SURNAME FROM User_Profiles WHERE GUID=(SELECT MPD_SUPERVISOR FROM User_Profiles WHERE GUID='".$guid."')";
	$sql = mysql_query($getsql);														//Execute the sql statement
	
	$return_arr = mysql_fetch_assoc($sql);
	//checks that the person is actually in the DB
	if($return_arr){
		//Filter through the returned values to ensure the keys are the same as the requested field names
		$i = 0;		//iterator
		$method_arr = explode(",",$method);			//Split the comma delimited request string (method) into an array
		foreach($return_arr as $k=>$v){				//Loop through each key/value pair to be returned
			if ($method_arr[$i] == $v)				//Check the key against the array of requested values: if the key is the same...
				$temp_arr[$k]=$v;					//Add the k/v pair to a temporary array
			else									//If the key is different (in the case of values that require a nested lookup e.g. pfirst, plast)...
				$temp_arr[$method_arr[$i]]=$v;		//Add the k/v pair to a temporary array but use the key from the requested values array
			$i++;
		}
		$return_arr = $temp_arr;
		
		$return = array('success'=>'true', 'data'=>$return_arr);
	} else {
		$return = array('success'=>'false');
	}
	echo json_encode($return);
	if (!$DEBUG) logToFile($LOGFILE, $mode." | ".$method."\n".$getsql."\n".json_encode($return));
	
}

//SET MODE
if ($mode == 'set'){
	
	//Check that the main user exists
	$sql = mysql_query("SELECT GUID FROM User_Profiles WHERE GUID='".$guid."'");
	if (mysql_num_rows($sql) != 0) {
		
		//Split the POST variables into two arrays: Main user and spouse
		foreach($_POST as $k=>$v) {			//Loop through the POST key/val pairs
			if (strpos($v, ",")){			//Invalid character check, also makes sql injection harder
				$err .= $k.":\" Invalid character in field.\", ";
			}
			if (substr($k,0,2) == 'S_'){	//If spouse variable (defined by S_ prefix)
				$spouse_post[$k]=$v;		//Add to spouse variable array
			}else {							//No spouse prefix
				if ($k != 'mode') {			//mode variable will be false positive in this case and break the sql
					$main_post[$k]=$v;		//Add to main user variable array
				}
			}	
		}
		
		
		//Check that if one field for spouse name is entered, the other is also
		if ($spouse_post['S_FIRSTNAME'] != "" && $spouse_post['S_SURNAME'] == "") {
			$err .= "S_SURNAME:\"Firstname entered: Please enter a surname.\", ";
		}
		if ($spouse_post['S_FIRSTNAME'] == "" && $spouse_post['S_SURNAME'] != "") {
			$err .= "S_FIRSTNAME:\"Surname entered: Please enter a firstname.\", ";
		}
		
		//Get the SPOUSE_GUID for the current User_Profile
		$temp_arr = mysql_fetch_assoc(mysql_query("SELECT SPOUSE_GUID, FIN_ACC_NUM FROM User_Profiles WHERE GUID='".$guid."'"));
		//print_r($temp_arr);
		$mainfan = $temp_arr['FIN_ACC_NUM'];
		$spouseguid = $temp_arr['SPOUSE_GUID'];
		$spousefirstname = $spouse_post['S_FIRSTNAME'];
		$spousesurname = $spouse_post['S_SURNAME'];
		
		if ($spousefirstname != '' && $spousesurname != '') {	// If values in spouse fields
			
			//Conditional allowblank - if spouse entered:
			//Don't allow ministry to be blank
			if ($spouse_post['S_MINISTRY']  == "") {
				$err .= "S_MINISTRY:\"Spouse entered: Please enter a ministry.\", ";
			}
			//Don't allow ftptos to be blank
			if ($spouse_post['S_FT_PT_OS'] == "") {
				$err .= "S_FT_PT_OS:\"Spouse entered: Please select an option.\", ";
			}
			
			//try to fetch the spouse's guid & FAN from firstname and lastname
			$temp_arr = mysql_fetch_assoc(mysql_query("SELECT GUID, FIN_ACC_NUM FROM User_Profiles WHERE FIRSTNAME='".$spousefirstname."' && SURNAME='".$spousesurname."'"));
			$spouseguid_fromname = $temp_arr['GUID'];
			$spousefan = $temp_arr['FIN_ACC_NUM'];
		
		
			//New Spouse: Insert spouse guid from names into user's profile
			if ($spouseguid == '') {	//If spouse not linked in user's profile:
				if ($mainfan == $spousefan && $spouseguid_fromname != '') {		//If FinAccNums are the same and spouse's guid can be found using firstname and lastname (above sql):		
					mysql_query("UPDATE User_Profiles SET SPOUSE_GUID='".$spouseguid_fromname."' WHERE GUID='".$guid."'");
				}else {
					if ($mainfan != $spousefan) {
						$err .= "S_FIRSTNAME:\"Invalid Spouse: Financial Account Numbers do not match. Spousal Financial Account numbers must be the same. If this needs to be changed, talk to your Ministry Supervisor.\", ";
						$err .= "S_SURNAME:\"Invalid Spouse: Financial Account Numbers do not match. Spousal Financial Account numbers must be the same. If this needs to be changed, talk to your Ministry Supervisor.\", ";
					} else {
						$err .= "S_FIRSTNAME:\"Cannot find spouse's details in database. Your spouse may not be registered, to check, <a href=".addslashes("http://mportal.ccca.org.au/TMN/?logout").">click here to logout</a>. Then get your spouse to log in to the TMN form.\", ";
						$err .= "S_SURNAME:\"Cannot find spouse's details in database. Your spouse may not be registered, to check, <a href=".addslashes("http://mportal.ccca.org.au/TMN/?logout").">click here to logout</a>. Then get your spouse to log in to the TMN form.\", ";
					}
				}
			} else {
				if ($spouseguid_fromname == '') {
					$err .= "S_FIRSTNAME:\"You cannot change details for an existing spouse. If this needs to be changed, talk to your Ministry Supervisor.\", ";
					$err .= "S_SURNAME:\"You cannot change details for an existing spouse. If this needs to be changed, talk to your Ministry Supervisor.\", ";
				}
			}
		}
		
		//Process the supervisor name - Redundant
		/*
		$mpdsupervisor = explode(" ", $main_post['MPD_SUPERVISOR']);		//split at spaces
		$mpdsuper_firstname = $mpdsupervisor[0];							//firstname
		$i=0;
		while (true)				//surname: all other words
		{
			$i++;
			if ($mpdsupervisor[$i] == NULL) {
				break;
			}
			$mpdsuper_surname .= " ".$mpdsupervisor[$i];
		}
		*/
		if ($main_post['MPD'] == '0') {
			
			$mpdsuper_guid = 'NULL';
		} else {
			//Set the mpd supervisor firstname, then remove it from the array (so it's not put into the update sql)
			$mpdsuper_firstname = $main_post['M_FIRSTNAME'];
			unset($main_post['M_FIRSTNAME']);
			//Surname
			$mpdsuper_surname = $main_post['M_SURNAME'];
			unset($main_post['M_SURNAME']);
			
			//Lookup the guid for the given mpd supervisor
			$mpdsuper_sql = "SELECT GUID FROM User_Profiles WHERE FIRSTNAME LIKE '".$mpdsuper_firstname."' && SURNAME LIKE '".$mpdsuper_surname."'";
			if (!$DEBUG) logToFile($LOGFILE, "MPD_SUPER FETCH: ".$mpdsuper_sql);
			$temp_arr = mysql_fetch_assoc(mysql_query($mpdsuper_sql));
			
			//if found, set the mpdsupervisor guid, otherwise remove the names from the array and add an error
			if ($temp_arr['GUID'] != "") {
				$mpdsuper_guid = $temp_arr['GUID'];
			} else {
				$mpdsuper_guid = 'NULL';
				unset($main_post['M_FIRSTNAME']);
				unset($main_post['M_SURNAME']);
				$err .= "M_FIRSTNAME:\"Cannot find MPD supervisor in database. This must be exact. Check with your supervisor to confirm.\", ";
				$err .= "M_SURNAME:\"Cannot find MPD supervisor in database. This must be exact. Check with your supervisor to confirm.\", ";
			}
		}
		
		//Main user sql formation
		$setsql = "UPDATE User_Profiles SET ";
		foreach($main_post as $k=>$v) {
			$setsql .= $k."='".$v."', ";
		}
		
		/*
		//TODO: FIX FOREIGN KEY CONSTRAINT PROBLEM
		//Set the spouse guid if found
		if ($spouseguid != '') {
			$setsql .= "SPOUSE_GUID='".$spouseguid."', ";
		}
		*/
		
		//Set the supervisor guid (setting to null VALUE instead of null STRING (no apostrophe))
		if ($mpdsuper_guid == 'NULL')
			$setsql .= "M_GUID=".$mpdsuper_guid.", ";
		else
			if ($mpdsuper_guid == $guid)
				$err .= "M_FIRSTNAME:\"You cannot be your own supervisor.\", M_SURNAME:\"You cannot be your own supervisor.\", ";
			else
				$setsql .= "M_GUID='".$mpdsuper_guid."', ";
		
		//Complete the sql
		$setsql = trim($setsql,", ");
		$setsql .= " WHERE GUID='".$guid."'";
		
		//Execute and log the Main user sql if no errors
		if ($err == "") {
			$sqlresult = mysql_query($setsql);		//Execute the Main user sql
		} else if (!$DEBUG) logToFile($LOGFILE, "Errors! ".$err);
		if (!$DEBUG) logToFile($LOGFILE, "Main User SQL: ".$setsql."\nResult: ".$sqlresult);
		
		
		//setup the sql statment for the spouse
		$s_setsql = "UPDATE User_Profiles SET ";
		
		//print_r($spouse_post);
		
		//Spouse sql formation
		if ($spouseguid != '') {
			$s_setsql .= "FT_PT_OS='".$spouse_post['S_FT_PT_OS']."', ";
			
			$s_setsql .= "DAYS_PER_WEEK='".$spouse_post['S_DAYS_PER_WEEK']."', ";
			
			$s_setsql .= "MINISTRY='".$spouse_post['S_MINISTRY']."', ";
			
			$s_setsql .= "MPD='".$main_post['MPD']."', ";
		}
		/*
			foreach($spouse_post as $k=>$v) {
				$s_setsql .= substr($k, 2)."='".$v."', ";
			}
		*/
		
		//TODO: FIX FK CONSTRAINT PROBLEM
		//$s_setsql .= "SPOUSE_GUID='".$guid."'";		//backlink the main user to the spouse
		
		//finish the sql statement
		$s_setsql = trim($s_setsql,", ");
		$s_setsql .= " WHERE GUID ='".$spouseguid."'";
		
			
		//Execute and log the spouse sql if no errors
		if ($err == "") {
			$sqlspouseresult = mysql_query($s_setsql);
		}
		if (!$DEBUG) logToFile($LOGFILE, "Spouse User SQL: ".$s_setsql."\nResult: ".$sqlspouseresult);
		
		//Return json success/failure with errors
		if ($sqlresult == 1 && $sqlspouseresult == 1 && $err == NULL) {
			echo '{"success": true}';
			//echo '{success: true, spouse: "'.$spouseguid.'", ft_pt_os: "'.$main_post['FT_PT_OS'].'", s_ft_pt_os: "'.$spouse_post['S_FT_PT_OS'].'"}';
		} else {
			if ($err != NULL)
				echo '{"success": false, "errors":{'.trim($err,", ").'} }'; //Return with errors
			else
				echo '{"success": true}';
		}
		
	}
	else {
	
		echo '{"success": false, "errors": {FIRSTNAME: "User not in database, please contact tech-team@ccca.org.au.", SURNAME: "User not in database, please contact tech-team@ccca.org.au." } }';
	}
		
};