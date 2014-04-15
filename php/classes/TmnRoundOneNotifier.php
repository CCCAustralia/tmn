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
        $this->level    = 0;
        $this->subject  = "TMN reminder: needs to be approved by end of month";
        $this->message  = "Hi {{names}}, <br /><br />This is a reminder that your 2014 TMN is due by the end of this month. {{reason}}<br /><br />Thanks for your cooperation.<br /><br />- Member Care";

        $this->reasons[TmnNotifier::$USER_HAS_NOT_SUBMITTED]  = "We noticed that you have not submitted a TMN to be reviewed by your leaders. To complete your TMN go to http://mportal.ccca.org.au/TMN .";
        $this->reasons[TmnNotifier::$AUTHORISER_HAS_NOT_APPROVED]  = "We noticed that you have submitted it and it is waiting on {{authoriser_name}}. We recommend that you remind {{authoriser_name}}, to go to http://mportal.ccca.org.au/TMN/tmn-authviewer.php?session={{session_id}} to review your TMN.";

    }

} 