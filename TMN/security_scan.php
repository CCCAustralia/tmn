<?php

$LOGFILE	= "php/logs/security_scan.log";

//Authenticate the user in GCX with phpCAS
include_once('lib/cas/cas.php');		//include the CAS module
include_once('php/classes/TmnDatabase.php');
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('You can not access this page without logging into GCX on the TMN page!');

$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
$xmlobject = new SimpleXmlElement($xmlstr);
$validReasonForOpeningPage	= false;

if (isset($_POST['varification_code'])) {
	
	try {
	
		//find the user with that varification code
		$db			= new TmnDatabase($LOGFILE);
		$sql		= "SELECT GUID FROM `User_Profiles` WHERE GUID=:varification_code AND EMAIL=:email";
		$values		= array(":varification_code" => $_POST['varification_code'], ":email" => phpCAS::getUser());
		
		$stmt		= $db->prepare($sql);
		$stmt->execute();
		
		if ($stmt->rowCount() == 1) {
			
			//update the user's details
			$sql				= "UPDATE `User_Profiles` SET GUID=:guid WHERE GUID=:varification_code AND EMAIL=:email"
			$values[':guid']	= (string)($xmlobject->authenticationSuccess->attributes->ssoGuid);
			
			$stmt		= $db->prepare($sql);
			$stmt->execute();
			
			//if the details were updated without any exceptions being thrown the following messages will be shown & sent
			
			//let teach team know about rego
			mail("tech.team@ccca.org.au","TMN AUTO REGISTRATION", "The person with the following details just successfully completed an automatic registration.\n\nGuid: " . (string)($xmlobject->authenticationSuccess->attributes->ssoGuid) . "\nEmail: " . phpCAS::getUser(), "From: TMN");
			//let user know it worked
			mail(phpCAS::getUser(),"TMN Registration Successfull", 'You are now able to access the TMN. If you are Married make sure your spouse is also Registered, the TMN requires it. Click <a href="http://mportal.ccca.org.au/TMN/">here</a> to go to the TMN and have them register too.', "From: CCCA Tech Team");
			
			//ouput msg
			echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">';
			echo '<html>';
				echo '<head>';
					echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
					echo '<style type="text/css">	.body-look{		padding:10px;		border-color: #8db2e3;		background-color: #deecfd;		font: normal 14px tahoma,arial,helvetica;		color: #416aa3;	}	.title-look{		padding:6px;		background-image: url(lib/resources/images/default/panel/top-bottom.gif);		color:#15428b;		font:bold 14px tahoma,arial,verdana,sans-serif;	}	</style>';
					echo '<title>Registration Complete!</title>';
				echo '</head>';
				echo '<body>';
					echo '<center>';
						echo '<div class="title-look" style="position:relative;left:20px;width:608px;text-align:left;">Registration Complete!</div>';
						echo '<div class="body-look" style="position:relative;left:20px;width:600px;">';
							echo 'You are now able to access the TMN. <br />';
							echo 'Click <a href="http://mportal.ccca.org.au/TMN/">here</a> to go start your TMN.<br />';
							echo 'If you are Married make sure your spouse is also Registered, the TMN requires it. <br />';
							echo '<b style="color: red;">If you are Married make sure your spouse is also Registered. Click <a href="http://mportal.ccca.org.au/TMN/?logout">here</a> to logout, then get them to return to http://mportal.ccca.org.au/TMN and register too.</b>';
						echo '</div>';
					echo '</center>';
				echo '</body>';
			echo '</html>';
			
			
			$validReasonForOpeningPage	= true;
			
		//wrong code message
		} else {
			
			
			
			//ouput msg
			echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">';
			echo '<html>';
				echo '<head>';
					echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
					echo '<style type="text/css">	.body-look{		padding:10px;		border-color: #8db2e3;		background-color: #deecfd;		font: normal 14px tahoma,arial,helvetica;		color: #416aa3;	}	.title-look{		padding:6px;		background-image: url(lib/resources/images/default/panel/top-bottom.gif);		color:#15428b;		font:bold 14px tahoma,arial,verdana,sans-serif;	}	</style>';
					echo '<title>Invalid Varification Code!</title>';
				echo '</head>';
				echo '<body>';
					echo '<center>';
						echo '<div class="title-look" style="position:relative;left:20px;width:608px;text-align:left;">Invalid Varification Code!</div>';
						echo '<div class="body-look" style="position:relative;left:20px;width:600px;">';
							echo 'You are now able to access the TMN. <br />';
							echo 'Invalid Varification Code. Click <a href="http://mportal.ccca.org.au/TMN/">here</a> to try again.';
						echo '</div>';
					echo '</center>';
				echo '</body>';
			echo '</html>';
		}
		
	//if a DB error is caught
	} catch (Exception $e) {
		echo 'Your security scan couldnt complete. Contact <a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a> due to:' . $e->getMessage() . '<br /><br />' . $e->getTraceAsString(); 
	}
	
}

if (isset($_POST['email']) && isset($_POST['fan'])) {
	//ask tech team to register user
	mail("tech.team@ccca.org.au","TMN REGISTRATION", "Guid: ".(string)($xmlobject->authenticationSuccess->attributes->ssoGuid)."\nFinancial Account Number: ".$_POST['fan']."\nEmail: " . $_POST['email'], "From: TMN");
	//tell user that its being processed
	echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">';
	echo '<html>';
		echo '<head>';
			echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
			echo '<style type="text/css">	.body-look{		padding:10px;		border-color: #8db2e3;		background-color: #deecfd;		font: normal 14px tahoma,arial,helvetica;		color: #416aa3;	}	.title-look{		padding:6px;		background-image: url(lib/resources/images/default/panel/top-bottom.gif);		color:#15428b;		font:bold 14px tahoma,arial,verdana,sans-serif;	}	</style>';
			echo '<title>Registration Submitted!</title>';
		echo '</head>';
		echo '<body>';
			echo '<center>';
				echo '<div class="title-look" style="position:relative;left:20px;width:608px;text-align:left;">Registration Submitted!</div>';
				echo '<div class="body-look" style="position:relative;left:20px;width:600px;">';
					echo 'Your Details are now being processed.<br />';
					echo '<b style="color: red;">If you are Married make sure your spouse is also Registered. Click <a href="http://mportal.ccca.org.au/TMN/?logout">here</a> to logout, then get them to return to http://mportal.ccca.org.au/TMN and register too.</b><br />';
					echo 'Try submiting your TMN tomorrow.';
				echo '</div>';
			echo '</center>';
		echo '</body>';
	echo '</html>';

	$validReasonForOpeningPage	= true;
}

if ($validReasonForOpeningPage	== false;) {
	echo "You didn't provide enough data to do the security scan.";
}
?>