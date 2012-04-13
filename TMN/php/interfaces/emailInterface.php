<?php
interface emailInterface {
	
	//CONSTRUCTOR
	/**
	 * Creates an email object into which an address, subject and body can be loaded.
	 * 
	 * @param string	$addr		-the email address to which it will be sent
	 * @param string	$subj		-the text that will appear in the subject line of the email
	 * @param string	$body		-the content of the email, newlines with "\n"
	 * @param string	$from		-the display name to say who the email is from
	 */
	
	public function __construct($addr, $subj, $body, $from);
	
	/**
	 * Updates the details of the email
	 * @param string	$addr		-the email address to which it will be sent
	 * @param string	$subj		-the text that will appear in the subject line of the email
	 * @param string	$body		-the content of the email, newlines with "\n"
	 * @param string	$from		-the display name to say who the email is from
	 */
	public function update($addr, $subj, $body, $from=null);
	
	/**
	 * Checks the current address to see if it is a proper email address.
	 * i.e. user@domain.net
	 * 
	 * @param string	$addr		-the address to validate. 
	 * 
	 * @return bool
	 */
	public function validateAddress($addr);
	
	/**
	 * Sends the email
	 * 
	 */
	public function send();
}