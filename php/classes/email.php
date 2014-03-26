<?php
if (file_exists('../interfaces/emailInterface.php')) {
	include_once('../interfaces/emailInterface.php');
    include_once('../../lib/unirest-php/lib/Unirest.php');
    include_once('../../lib/sendgrid-php/lib/SendGrid.php');
    include_once('../../lib/smtpapi-php/lib/Smtpapi.php');
    SendGrid::register_autoloader();
    Smtpapi::register_autoloader();
} elseif(file_exists('interfaces/emailInterface.php')) {
	include_once('interfaces/emailInterface.php');
    include_once('../lib/unirest-php/lib/Unirest.php');
    include_once('../lib/sendgrid-php/lib/SendGrid.php');
    include_once('../lib/smtpapi-php/lib/Smtpapi.php');
    SendGrid::register_autoloader();
    Smtpapi::register_autoloader();
} else {
	include_once('php/interfaces/emailInterface.php');
    include_once('lib/unirest-php/lib/Unirest.php');
    include_once('lib/sendgrid-php/lib/SendGrid.php');
    include_once('lib/smtpapi-php/lib/Smtpapi.php');
    SendGrid::register_autoloader();
    Smtpapi::register_autoloader();
}

class Email implements emailInterface{

    private $sendgrid;
	public $address;
	public $subject;
	public $bodytext;
	public $headerfrom;
	
	public function __construct($addr, $subj, $body, $from = null) {

        //don't allow the from variable to be set.
        //if (!isset($from)) {
            $from = "CCCA TMN <network.admin@ccca.org.au>";
        //}

        $configString   = "";

        if (file_exists('config.json')) {
            $configString = file_get_contents("config.json");
        } elseif (file_exists('../config.json')) {
            $configString = file_get_contents("../config.json");
        } elseif (file_exists('../../config.json')) {
            $configString = file_get_contents("../../config.json");
        } else {
            $configString = file_get_contents("../../../config.json");
        }

        $config = json_decode($configString,true);

        $this->sendgrid = new SendGrid($config['sendgrid_username'], $config['sendgrid_password'], array("turn_off_ssl_verification" => true));

		$this->update($addr, $subj, $body, $from);
		
	}

	public function update($addr, $subj, $body, $from=null) {
		
		if (!is_null($addr)) {
			$this->address = $addr;
		}
		
		if (!is_null($subj)) {
			$this->subject = $subj;
		}
		
		if (!is_null($body)) {
			$this->bodytext = $body;
		}
		
		if (!is_null($from)) {
			$this->headerfrom = $from;
		}
		
	}
	
	/*
	 * The regular expression used is taken from http://fightingforalostcause.net/misc/2006/compare-email-regex.php and works as follows:
	 * 
	 * These should be valid
		abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@letters-in-local.org	valid
		01234567890@numbers-in-local.net	valid
		&'*+-./=?^_{}~@other-valid-characters-in-local.net	valid
		mixed-1234-in-{+^}-local@sld.net	valid
		a@single-character-in-local.org	valid
		"quoted"@sld.com	invalid
		"\e\s\c\a\p\e\d"@sld.com	invalid
		"quoted-at-sign@sld.org"@sld.com	invalid
		"escaped\"quote"@sld.com	invalid
		"back\slash"@sld.com	invalid
		single-character-in-sld@x.org	valid
		local@dash-in-sld.com	valid
		letters-in-sld@123.com	valid
		uncommon-tld@sld.museum	valid
		uncommon-tld@sld.travel	valid
		uncommon-tld@sld.mobi	valid
		country-code-tld@sld.uk	valid
		country-code-tld@sld.rw	valid
		local@sld.newTLD	valid
		numbers-in-tld@sld.xn--3e0b707e	invalid
		local@sub.domains.com	valid
		bracketed-IP-instead-of-domain@[127.0.0.1]	invalid
		 
		These should be invalid
		@missing-local.org	invalid
		! #$%(),:;<>@[]\`|@invalid-characters-in-local.org	invalid
		.local-starts-with-dot@sld.com	invalid
		local-ends-with-dot.@sld.com	invalid
		two..consecutive-dots@sld.com	invalid
		partially."quoted"@sld.com	invalid
		missing-sld@.com	invalid
		sld-starts-with-dashsh@-sld.com	invalid
		sld-ends-with-dash@sld-.com	invalid
		invalid-characters-in-sld@! "#$%(),/;<>_[]`|.org	invalid
		local@second-level-domains-are-invalid-if-they-are-longer-than-sixty-three-characters.org	invalid
		missing-dot-before-tld@com	invalid
		missing-tld@sld.	invalid
		invalid
		missing-at-sign.net	invalid
		unbracketed-IP@127.0.0.1	valid
		invalid-ip@127.0.0.1.26	invalid
		another-invalid-ip@127.0.0.256	valid
		IP-and-port@127.0.0.1:25	valid
	 */
	public function validateAddress($addr) {
		$regular_expression = "/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i";
		
		if (preg_match($regular_expression, $addr) == 1) {
			return true;
		} else {
			return false;
		}
		
	}
	
	public function send() {

        $email = new SendGrid\Email();
        $email->
            setFrom($this->headerfrom)->
            setSubject($this->subject);

        if ( strlen($this->bodytext) != strlen(strip_tags($this->bodytext)) ) {

            $email->setHtml($this->bodytext);

        } else {

            $email->setText($this->bodytext);

        }

        if(count($this->address)) {

            $recipientArray = explode(",", $this->address);

            foreach($recipientArray as $recipient) {
                $email->addTo(trim($recipient));
            }

            $response =  $this->sendgrid->send($email);

        }

	}
}


?>