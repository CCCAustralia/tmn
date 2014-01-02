<?php

if (file_exists('../interfaces/TmnInterface.php')) {
	include_once('../interfaces/TmnInterface.php');
	include_once('../classes/Reporter.php');
	include_once('../classes/TmnCrudUser.php');
	include_once('../classes/TmnAuthenticator.php');
}
if (file_exists('php/interfaces/TmnInterface.php')) {
	include_once('php/interfaces/TmnInterface.php');
	include_once('php/classes/Reporter.php');
	include_once('php/classes/TmnCrudUser.php');
	include_once('php/classes/TmnAuthenticator.php');
}
if (file_exists('interfaces/TmnInterface.php')) {
	include_once('interfaces/TmnInterface.php');
	include_once('classes/Reporter.php');
	include_once('classes/TmnCrudUser.php');
	include_once('classes/TmnAuthenticator.php');
}
class Tmn extends Reporter implements TmnInterface {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	
	private $authenticator;
	private $guid;
	private $user;
	private $logfile;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile) {
		
		parent::__construct($logfile);
		
		$this->authenticator= TmnAuthenticator::getInstance($logfile);
		$this->user			= null;
		$this->logfile		= $logfile;
	}
	
	
			////////////////AUTHENTICATION FUNCTIONS//////////////
	
	public function authenticate() {
		TmnAuthenticator::authenticate();
	}
	
	public function logout() {
		$this->authenticator->logout();
	}
	
	public function isAuthenticated() {
		return $this->authenticator->isAuthenticated();
	}
	
	public function getAuthenticatedGuid() {
		//return "691EC152-0565-CEF4-B5D8-99286252652B";
		return $this->authenticator->getGuid();
	}
	
	public function getEmail() {
		return $this->authenticator->getEmail();
	}
	
	public function getUser() {
		//if the user hasn't be created yet then make it
		if ($this->user == null) {
			$this->user = new TmnCrudUser($this->logfile, $this->getAuthenticatedGuid());
		}
		
		//return the user object
		return $this->user;
	}
	
	public function showErrorPage($errorMsg) {
		echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">';
			echo '<html>';
				echo '<head>';
					echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
					echo '<style type="text/css">	.body-look{		padding:10px;		border-color: #8db2e3;		background-color: #deecfd;		font: normal 14px tahoma,arial,helvetica;		color: #416aa3;	}	.title-look{		padding:6px;		background-image: url(lib/resources/images/default/panel/top-bottom.gif);		color:#DB2929;		font:bold 14px tahoma,arial,verdana,sans-serif;	}	</style>';
					echo '<title>Error!</title>';
				echo '</head>';
				echo '<body>';
					echo '<center>';
						echo '<div class="title-look" style="position:relative;left:20px;width:608px;text-align:left;">Error!</div>';
						echo '<div class="body-look" style="position:relative;left:20px;width:600px;">';
							echo $errorMsg;
							echo '<p>For help contact: <a href="mailto:tech.team@ccca.org.au?subject=TMN Error&body=Dear Tech Team,%0A%0AThe following error occured:%0A' . $errorMsg . '%0A%0APlease help.">tech.team@ccca.org.au</a></p>';
						echo '</div>';
					echo '</center>';
				echo '</body>';
			echo '</html>';
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>