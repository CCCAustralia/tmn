<?php

include_once("../classes/email.php");

$address    = "michael.harrison@cru.org";
$subject    = "TMN Email Test";
$body       = "This is a MESSAGE!!!";
$email      = new Email($address, $subject, $body);
$email->send();