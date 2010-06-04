<?php
/**
 * Personal Details processing file
 * 
 * This is the file called to get and set a user's personal profile details.
 * @author Thomas Flynn <tom.flynn[at]ccca.org.au>, Michael Harrison <michael.harrison[at]ccca.org.au>
 * @package TMN
 */

//Include the relevent php files
include_once "logger.php";
include_once "dbconnect.php";
include_once "mysqldriver.php";
include_once "PersonalDetails.php";
include_once("../lib/FirePHPCore/fb.php");


//Authenticate the user in GCX with phpCAS
include_once('../lib/cas/cas.php');		//include the CAS module
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('{success: false}');

//grab guid
if (isset($_SESSION['phpCAS'])) {
	$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
	$xmlobject = new SimpleXmlElement($xmlstr);
	$guid = $xmlobject->authenticationSuccess->attributes->ssoGuid;
}

$DEBUG = 1;
if ($DEBUG) ob_start();
$pd_logger = new logger("logs/personal_details.log", $DEBUG);
$pd_logger->setDebug($DEBUG);

$connection = new MySqlDriver();	//set up the database connection

$mode = $_REQUEST['mode'];		//get/set
$method = $_REQUEST['method'];	//values(fieldnames)

$personal_details = new PersonalDetails($guid, $DEBUG);
if ($DEBUG) $personal_details->addLog($pd_logger);

if ($mode == 'get') {
	if($DEBUG) fb($_POST);
	echo $personal_details->getPersonalDetails();
}

if ($mode == 'set') {
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
		
		echo $personal_details->setPersonalDetails($main_post, $spouse_post);
	}
	else {
		
		echo '{"success": false, "errors": {FIRSTNAME: "User not in database, please contact tech-team@ccca.org.au.", SURNAME: "User not in database, please contact tech-team@ccca.org.au." } }';
	}
}

?>