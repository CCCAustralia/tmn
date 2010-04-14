<?php

/*******************************************
#                                                             
# index.php - Generic index for PHP GCX SSO login                    
*******************************************/

//GCX login
include_once('lib/cas/cas.php');		//include the CAS module
include_once('php/dbconnect.php');
//phpCAS::setDebug();			//Debugging mode
phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
phpCAS::setNoCasServerValidation();	//no SSL validation for the CAS server
phpCAS::forceAuthentication();		//require the user to log in to CAS




//user is now authenticated by the CAS server and the user's login name can be read with phpCAS::getUser()

//logout if desired
if (isset($_REQUEST['logout'])) {
	phpCAS::logout();
}


//fetch a ticket if absent
if ($_REQUEST['ticket'] == '' && $_REQUEST['id'] == '')
{
//echo GetMainBaseFromURL(curPageURL()). "<br />";
    header("Location: https://signin.mygcx.org/cas/login?service=http://mportal.ccca.org.au/TMN/index.php");
}

$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
$xmlobject = new SimpleXmlElement($xmlstr);
setcookie("guid", $xmlobject->authenticationSuccess->attributes->ssoGuid, 0, '/TMN/');
setcookie("email", $xmlobject->authenticationSuccess->user, 0, '/TMN/');

//check if they are a valid user (If not show the rego page)
$tmn_connection = db_connect();
$sql = mysql_query("SELECT GUID FROM User_Profiles WHERE GUID='".($xmlobject->authenticationSuccess->attributes->ssoGuid)."';", $tmn_connection);
if (mysql_num_rows($sql) == 1)
	echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">    <link rel="stylesheet" type="text/css" href="lib/resources/css/ext-all.css"/>    <link rel="stylesheet" type="text/css" href="lib/statusbar/css/statusbar.css" />    <!-- <link rel="stylesheet" type="text/css" href="lib/resources/icons/silk.css"/> -->    <link rel="stylesheet" type="text/css" href="lib/resources/css/customstyles.css" />    <!-- GC -->    <!-- LIBS -->    <script type="text/javascript" src="lib/ext-base.js"></script>    <!-- ENDLIBS -->    <script type="text/javascript" src="lib/ext-all.js"></script>    <script type="text/javascript" src="lib/cookie.js"> </script>    <script type="text/javascript" src="lib/statusbar/StatusBar.js"> </script>     <script type="text/javascript" src="lib/statusbar/ValidationStatus.js"> </script><script type="text/javascript" src="lib/Printer-all.js"> </script>  <!-- Defines and creates the form layout using the Ext lib --> <script type="text/javascript" src="ui/view_tmn.js"></script> <script type="text/javascript" src="ui/internal_transfers.js"></script> <script type="text/javascript" src="ui/financial_details.js"></script> <script type="text/javascript" src="ui/personal_details.js"></script><script type="text/javascript" src="ui/tmn.js"></script>     <title>TMN</title></head><body><center><div id="tmn-cont"></div></center></body></html>';
else
	echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html><head>	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	<style type="text/css">	.body-look{		padding:10px;		border-color: #8db2e3;		background-color: #deecfd;		font: normal 14px tahoma,arial,helvetica;		color: #416aa3;	}	.title-look{		padding:6px;		background-image: url(lib/resources/images/default/panel/top-bottom.gif);		color:#15428b;		font:bold 14px tahoma,arial,verdana,sans-serif;	}	</style>	<title>User Not Found!</title></head><body>	<center>		<div class="title-look" style="position:relative;left:20px;width:608px;text-align:left;">User Not Found!</div>		<div class="body-look" style="position:relative;left:20px;width:600px;">You where not found in our system.<br />If you think you should be able to submit a TMN then register your details for processing.<br />Our Security checks are usually take One buisness day to complete.</div>	</center>	<br />	<div class="title-look">Submit Details for Registration</div>	<form class="body-look" name="security_scan" action="security_scan.php" method="post">		<label for="firstname">First Name: </label><input type="text" name="firstname" value="'.$xmlobject->authenticationSuccess->attributes->firstName.'" style="position:relative;left:120px;" readonly /> <br />		<label for="lastname">Last Name: </label><input type="text" name="lastname" value="'.$xmlobject->authenticationSuccess->attributes->lastName.'" style="position:relative;left:121px;" readonly /> <br />		<label for="fan">Financial Account Number: </label><input type="text" name="fan" style="position:relative;left:26px;" /> <br />		<input type="submit" value="Submit" />	</form></body></html>';


function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}
?>
