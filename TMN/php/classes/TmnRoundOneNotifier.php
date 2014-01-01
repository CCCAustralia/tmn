<?php
if(file_exists('../classes/TmnFinancialUnit.php')) {
    include_once('../interfaces/TmnNotifierInterface.php');
    include_once('../classes/TmnNotifier.php');
    include_once('../classes/TmnFinancialUnit.php');
    include_once('../classes/email.php');
}
if(file_exists('classes/TmnFinancialUnit.php')) {
    include_once('interfaces/TmnNotifierInterface.php');
    include_once('classes/TmnNotifier.php');
    include_once('classes/TmnFinancialUnit.php');
    include_once('classes/email.php');
}
if(file_exists('php/classes/TmnFinancialUnit.php')) {
    include_once('php/interfaces/TmnNotifierInterface.php');
    include_once('php/classes/TmnNotifier.php');
    include_once('php/classes/TmnFinancialUnit.php');
    include_once('php/classes/email.php');
}

class TmnRoundOneNotifier extends TmnNotifier implements TmnNotifierInterface {

    public function __construct() {

        $this->round    = "One";
        $this->subject  = "TMN: Friendly Reminder";
        $this->message  = "Hi {{names}}, <br /><br />This is a friendly reminder that your TMN is due soon. Go to http://mportal.ccca.org.au/TMN to complete yours.<br />Thanks for your help.<br /><br />Yours in Christ.<br />TMN Development Team.";

    }

    public function sendEmailsFor(TmnFinancialUnit $financialUnit) {

        array_push($this->financialUnitsContacted, $financialUnit);

//        $mustache   = new Mustache_Engine;
        $address    = $financialUnit->getEmails();
        $subject    = $this->subject;
//        $body       = $mustache->render($this->message, array("names" => $financialUnit->getNames()));
        $body       = $this->message;

        echo("Report - to:". $address . " subject:" . $subject . " body: " . $body);
//        $email  = new Email($address, $subject, $body);
//        $email->send();

    }

} 