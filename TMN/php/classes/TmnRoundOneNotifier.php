<?php
if(file_exists('../classes/TmnFinancialUnit.php')) {
    include_once('../classes/TmnFinancialUnit.php');
    include_once('../classes/email.php');
}
if(file_exists('classes/TmnFinancialUnit.php')) {
    include_once('classes/TmnFinancialUnit.php');
    include_once('classes/email.php');
}
if(file_exists('php/classes/TmnFinancialUnit.php')) {
    include_once('php/classes/TmnFinancialUnit.php');
    include_once('php/classes/email.php');
}

class TmnRoundOneNotifier implements TmnNotifierInterface {

    public function sendEmailsFor(TmnFinancialUnit $financialUnit) {

        $email  = new Email();
        $emial->send();

    }

} 