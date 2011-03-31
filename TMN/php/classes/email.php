<?php
include_once('../interfaces/emailInterface.php');

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

	public function validateAddress($addr) {
	return TRUE;
	}
	
	public function send() {
		mail($this->address, $this->subject, $this->bodytext, "From: ".$this->headerfrom);
	}
}


?>