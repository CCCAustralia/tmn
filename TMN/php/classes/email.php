<?php
if (file_exists('../interfaces/emailInterface.php')) {
	include_once('../interfaces/emailInterface.php');
} elseif(file_exists('interfaces/emailInterface.php')) {
	include_once('interfaces/emailInterface.php');
} else {
	include_once('php/interfaces/emailInterface.php');
}

class Email implements emailInterface{
	
	public $address;
	public $subject;
	public $bodytext;
	public $headerfrom;
	
	public function __construct($addr, $subj, $body, $from) {
		if ($addr) {
			if ($this->validateAddress($addr)) {
				$this->address = $addr;
			}
		}
		
		if ($subj) {
			$this->subject = $subj;
		}
		
		if ($body) {
			$this->bodytext = $body;
		}
		
		if ($from) {
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
		mail($this->address, $this->subject, $this->bodytext, "From: ".$this->headerfrom);
	}
}


?>