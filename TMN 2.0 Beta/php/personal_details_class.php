<?php
include_once "logger.php";
include_once "dbconnect.php";
include_once "mysqldriver.php";
require_once("../lib/FirePHPCore/fb.php");

class pd {
	private $DEBUG;
	private $logger;
	private $userguid;
	private $connection;

	//constructor
	public function __construct($guid, $dbug) {
		$this->userguid = $guid;
		$this->DEBUG = $dbug;
		$this->connection = new MySqlDriver();
	}

	//set up the logger object
	public function addLog($logger) {
		$this->logger = $logger;
	}

	//'get' mode
	public function getPersonalDetails() {
		if($this->DEBUG) fb($this);
		$getsql = "SELECT ";	//the sql statement for get

		//which fields to get
		$method = "FIRSTNAME,SURNAME,MINISTRY,FT_PT_OS,DAYS_PER_WEEK,S_FIRSTNAME,S_SURNAME,S_MINISTRY,S_FT_PT_OS,S_DAYS_PER_WEEK,MPD,M_FIRSTNAME,M_SURNAME";

		//sql statement for selective field requests
		$getsql .= $method;

		//Loop through the requested values, test the prefix for S_, then replace the label with the appropriate sql SELECT sub-query
		$method_arr = explode(",", $method);
		foreach ($method_arr as $v) {
			if (substr($v, 0, 2) == 'S_') {
				$getsql = str_replace($v, "(SELECT ".substr($v, 2)." FROM User_Profiles WHERE FIN_ACC_NUM=(SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID='".$this->userguid."') && GUID !='".$this->userguid."')",$getsql);
			}
			if (substr($v, 0, 2) == 'M_') {
				$getsql = str_replace($v, "(SELECT ".substr($v, 2)." FROM User_Profiles WHERE GUID=(SELECT M_GUID FROM User_Profiles WHERE GUID='".$this->userguid."'))",$getsql);
			}
		}

		$getsql = trim($getsql, ", ")." FROM User_Profiles WHERE GUID='".$this->userguid."';";		//Form the sql statement
		if ($this->DEBUG) $this->logger->logToFile("GET: ".$getsql);						//Log the sql statement
		$sql = mysql_query($getsql);														//Execute the sql statement
		$return_arr = mysql_fetch_assoc($sql);												//Fetch the results

		if($return_arr){
			//Filter through the returned values to ensure the keys are the same as the requested field names;
			//this is because the keys for the spouse will be returned without the s_ prefix, this will cause problems when passing it back to the front-end,
			//so this will loop through and compare the keys from the request and response, and if they don't match, will use the prefixed key.
			$i = 0;										//iterator
			$method_arr = explode(",",$method);			//Split the comma delimited request string (method) into an array
			foreach($return_arr as $k=>$v){				//Loop through each key/value pair to be returned
				if ($method_arr[$i] == $v)				//Check the key against the array of requested values: if the key is the same...
				$temp_arr[$k]=$v;					//Add the k/v pair to a temporary array
				else									//If the key is different (in the case of values that require a nested lookup e.g. s_first, s_last)…
				$temp_arr[$method_arr[$i]]=$v;		//Add the k/v pair to a temporary array but use the key from the requested values array
				$i++;									//increment
			}
			$return_arr = $temp_arr;
				
			$return = array('success'=>'true', 'data'=>$return_arr);
		} else {
			$return = array('success'=>'false');
		}
		return json_encode($return);
		if ($this->DEBUG) $this->logger->logToFile("GET : ".$method."\n".$getsql."\n".json_encode($return));

	}
	//'set' mode
	public function setPersonalDetails($main_post, $spouse_post) {
		
		if ($this->DEBUG) fb($main_post); fb($spouse_post);
		
		//Check that if one field for spouse name is entered, the other is also
		if ($spouse_post['S_FIRSTNAME'] != "" && $spouse_post['S_SURNAME'] == "") {
			$err .= "S_SURNAME:\"Firstname entered: Please enter a surname.\", ";
		}
		if ($spouse_post['S_FIRSTNAME'] == "" && $spouse_post['S_SURNAME'] != "") {
			$err .= "S_FIRSTNAME:\"Surname entered: Please enter a firstname.\", ";
		}
			
		//Get the SPOUSE_GUID for the current User_Profile
		$temp_arr = mysql_fetch_assoc(mysql_query("SELECT SPOUSE_GUID, FIN_ACC_NUM FROM User_Profiles WHERE GUID='".$this->userguid."'"));
		if($this->DEBUG) fb($temp_arr);
		$mainfan = $temp_arr['FIN_ACC_NUM'];
		$spouseguid = $temp_arr['SPOUSE_GUID'];
		$spousefirstname = $spouse_post['S_FIRSTNAME'];
		$spousesurname = $spouse_post['S_SURNAME'];
			
		if ($spousefirstname != '' && $spousesurname != '') {	// If values in spouse fields

			//Server-side conditional allowblank - if spouse entered:
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
			if($this->DEBUG) fb($temp_arr);
			$spouseguid_fromname = $temp_arr['GUID'];
			$spousefan = $temp_arr['FIN_ACC_NUM'];
				
			$spouseguid = -1;
			
			//Spouse conditions:
			if ($spouseguid == '') {		//If spouse not already linked in user's profile:
				if ($mainfan == $spousefan && $spouseguid_fromname != '') {
					$spousecase = 0;		//If FinAccNums match and spouse's guid can be found using firstname and lastname (above sql): link the profiles
				}
				else {
					if ($mainfan != $spousefan && $spouseguid_fromname != '') {
						$spousecase = 1;	//This is true if the firstname/surname pair that was input matches a user in the database, but the two users have different FANs
					}
					else {
						$spousecase = 2;	//This is true if no linked spouse can be found, and the firstname/surname pair doesn't match a user
					}
				}
			} else {						//If the spouse is already linked
				if ($spouseguid_fromname == '') {
					$spousecase = 3;		//If the user has a linked spouse, but no guid can be found for the firstname/surname pair that was input
				}
			}
		}
		
		switch ($spousecase) {	//for case explanations, see the above set of conditionals
			case 0:
				if ($spouseguid_fromname != ''){
					$q = "UPDATE User_Profiles SET SPOUSE_GUID='".$spouseguid_fromname."' WHERE GUID='".$this->userguid."'";	//form sql
					mysql_query($q);							//update main user
					if ($this->DEBUG) $this->logger->logToFile($q);	//log sql
					
					$sq = "UPDATE User_Profiles SET SPOUSE_GUID='".$this->userguid."' WHERE GUID='".$spouseguid_fromname."'";	//form spouse sql
					mysql_query($sq);							//update spouse
					if ($this->DEBUG) $this->logger->logToFile($sq);	//log spouse sql
				}
			break;
			case 1:
				$err .= "S_FIRSTNAME:\"Invalid Spouse: Financial Account Numbers do not match.<br />Spousal Financial Account numbers must be the same. If this needs to be changed, talk to your Ministry Supervisor.\", ";
				$err .= "S_SURNAME:\"Invalid Spouse: Financial Account Numbers do not match.<br />Spousal Financial Account numbers must be the same. If this needs to be changed, talk to your Ministry Supervisor.\", ";
			break;
			case 2:
				$err .= "S_FIRSTNAME:\"Cannot find spouse's details in database. Your spouse may not be registered, to check, <a href=".addslashes("http://mportal.ccca.org.au/TMN/?logout").">click here to logout</a>. Then get your spouse to log in to the TMN form.\", ";
				$err .= "S_SURNAME:\"Cannot find spouse's details in database. Your spouse may not be registered, to check, <a href=".addslashes("http://mportal.ccca.org.au/TMN/?logout").">click here to logout</a>. Then get your spouse to log in to the TMN form.\", ";
			break;
			case 3:
				$err .= "S_FIRSTNAME:\"You cannot change details for an existing spouse. If this needs to be changed, talk to your Ministry Supervisor.\", ";
				$err .= "S_SURNAME:\"You cannot change details for an existing spouse. If this needs to be changed, talk to your Ministry Supervisor.\", ";
			break;
		}
		
		//MPD Supervisor:
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
			if ($this->DEBUG) $this->logger->logToFile("MPD_SUPER FETCH: ".$mpdsuper_sql);
			$temp_arr = mysql_fetch_assoc(mysql_query($mpdsuper_sql));
			if ($this->DEBUG) fb($temp_arr);

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
			
		//Set the supervisor guid (setting to null VALUE instead of null STRING (no apostrophe))
		if ($mpdsuper_guid == 'NULL')
			$setsql .= "M_GUID=".$mpdsuper_guid.", ";
		else
			if ($mpdsuper_guid == $this->userguid)
				$err .= "M_FIRSTNAME:\"You cannot be your own supervisor.\", M_SURNAME:\"You cannot be your own supervisor.\", ";
			else
				$setsql .= "M_GUID='".$mpdsuper_guid."', ";
			
		//Complete the sql
		$setsql = trim($setsql,", ");
		$setsql .= " WHERE GUID='".$this->userguid."'";
			
		//Execute and log the Main user sql if no errors
		if ($err == "") {
			$sqlresult = mysql_query($setsql);		//Execute the Main user sql
		} else if ($this->DEBUG) $this->logger->logToFile("Errors! ".$err);
		if ($this->DEBUG) $this->logger->logToFile("Main User SQL: ".$setsql."\nResult: ".$sqlresult);
			
		//setup the sql statment for the spouse
		$s_setsql = "UPDATE User_Profiles SET ";
			
		//Spouse sql formation
		if ($spouseguid != '') {
			$s_setsql .= "FT_PT_OS='".$spouse_post['S_FT_PT_OS']."', ";

			$s_setsql .= "DAYS_PER_WEEK='".$spouse_post['S_DAYS_PER_WEEK']."', ";

			$s_setsql .= "MINISTRY='".$spouse_post['S_MINISTRY']."', ";

			$s_setsql .= "MPD='".$main_post['MPD']."', ";
		}
		
		//finish the sql statement
		$s_setsql = trim($s_setsql,", ");
		$s_setsql .= " WHERE GUID ='".$spouseguid."'";

		//Execute and log the spouse sql if no errors
		if ($err == "") {
			$sqlspouseresult = mysql_query($s_setsql);
		}
		if ($this->DEBUG) $this->logger->logToFile("Spouse User SQL: ".$s_setsql."\nResult: ".$sqlspouseresult);
			
		//Return json success/failure with errors
		if ($sqlresult == 1 && $sqlspouseresult == 1 && $err == NULL) {
			$return = '{"success": true}';
			//echo '{success: true, spouse: "'.$spouseguid.'", ft_pt_os: "'.$main_post['FT_PT_OS'].'", s_ft_pt_os: "'.$spouse_post['S_FT_PT_OS'].'"}';
		} else {
			if ($err != NULL)
			$return = '{"success": false, "errors":{'.trim($err,", ").'} }'; //Return with errors
			else
			$return = '{"success": true}';
		}
			
		return $return;
	}
}