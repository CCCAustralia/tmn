<?php
if(file_exists('../classes/TmnFinancialUnit.php')) {
    include_once('../interfaces/TmnNotifierInterface.php');
    include_once('../classes/TmnNotifier.php');
}
if(file_exists('classes/TmnFinancialUnit.php')) {
    include_once('interfaces/TmnNotifierInterface.php');
    include_once('classes/TmnNotifier.php');
}
if(file_exists('php/classes/TmnFinancialUnit.php')) {
    include_once('php/interfaces/TmnNotifierInterface.php');
    include_once('php/classes/TmnNotifier.php');
}

class TmnRoundFourNotifier extends TmnNotifier implements TmnNotifierInterface {

    protected $reasons                  = array();

    public function __construct() {

        $this->round    = "Four";
        $this->level    = 3;
        $this->subject  = "TMN reminder: needs to be approved by end of month";
        $this->message  = "Hi {{names}}, <br /><br />This is a reminder that your 2014 TMN is due by the end of this month.<br /><br />Please note that if your submitted and authorised TMN is not received by Finances by the end of this month your next stipend will be delayed by at least 2 weeks.<br /><br />{{reason}}<br /><br /> The following people have been included on this email so that you can discuss why it is not complete:<br />{{authorisers}}.<br /><br />Thanks for your cooperation.<br /><br />- Member Care";

        $this->reasons[TmnNotifier::$USER_HAS_NOT_SUBMITTED]  = "We noticed that you have not submitted a TMN to be reviewed by your leaders. To complete your TMN go to http://mportal.ccca.org.au/TMN .";
        $this->reasons[TmnNotifier::$AUTHORISER_HAS_NOT_APPROVED]  = "We noticed that you have submitted it and it is waiting on {{authoriser_name}}. {{authoriser_name}}, please go to http://mportal.ccca.org.au/TMN/tmn-authviewer.php?session={{session_id}} to review the TMN.";

    }

} 