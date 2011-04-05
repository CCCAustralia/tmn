<?php
//Authenticate the user in GCX with phpCAS
include_once('lib/cas/cas.php');		//include the CAS module
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('You can not access this page without logging into GCX on the TMN page!');


mail("tech.team@ccca.org.au","TMN REGISTRATION", "Guid: " . phpCAS::getAttribute('ssoGuid') . "\nFirst Name: ".$_POST['firstname']."\nLast Name: ".$_POST['lastname']."\nFinancial Account Number: ".$_POST['fan']."\nEmail: " . phpCAS::getEmail(), "From: TMN");
echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html><head>	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">	<style type="text/css">	.body-look{		padding:10px;		border-color: #8db2e3;		background-color: #deecfd;		font: normal 14px tahoma,arial,helvetica;		color: #416aa3;	}	.title-look{		padding:6px;		background-image: url(lib/resources/images/default/panel/top-bottom.gif);		color:#15428b;		font:bold 14px tahoma,arial,verdana,sans-serif;	}	</style>	<title>Registration Submitted!</title></head><body>	<center>		<div class="title-look" style="position:relative;left:20px;width:608px;text-align:left;">Registration Submitted!</div>		<div class="body-look" style="position:relative;left:20px;width:600px;">Your Details are now being processed.<br /><b style="color: red;">If you are Married make sure your spouse is also Registered. Click <a href="http://mportal.ccca.org.au/TMN/?logout">here</a> to logout, then get them to return to http://mportal.ccca.org.au/TMN and register too.</b><br />Try submiting your TMN tomorrow.</div>	</center></body></html>';
?>