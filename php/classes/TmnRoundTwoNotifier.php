<?php
if(file_exists('../classes/TmnFinancialUnit.php')) {
    include_once('../interfaces/TmnNotifierInterface.php');
    include_once('../classes/TmnConstants.php');
    include_once('../classes/TmnNotifier.php');
    include_once('../classes/TmnFinancialUnit.php');
    include_once('../classes/email.php');
}
if(file_exists('classes/TmnFinancialUnit.php')) {
    include_once('interfaces/TmnNotifierInterface.php');
    include_once('classes/TmnConstants.php');
    include_once('classes/TmnNotifier.php');
    include_once('classes/TmnFinancialUnit.php');
    include_once('classes/email.php');
}
if(file_exists('php/classes/TmnFinancialUnit.php')) {
    include_once('php/interfaces/TmnNotifierInterface.php');
    include_once('php/classes/TmnConstants.php');
    include_once('php/classes/TmnNotifier.php');
    include_once('php/classes/TmnFinancialUnit.php');
    include_once('php/classes/email.php');
}

class TmnRoundTwoNotifier extends TmnNotifier implements TmnNotifierInterface {

    protected $reasons              = array();
    protected $sinceForStudentLife  = null;
    protected $sinceForEveryone     = null;

    public function __construct() {

        $this->round    = "Two";
        $this->subject  = "TMN: Friendly Reminder";
        $this->message  = "Hi {{names}}, <br /><br />This is a friendly reminder that your TMN due date is getting closer.<br /><br />{{reason}}<br /><br /> The following people have been included on this email so that you can discuss why it is not complete:<br />{{authorisers}}.<br />Thanks for your help.<br /><br />Yours in Christ.<br />TMN Development Team.";

        $this->reasons["user-has-not-submitted"]  = "We noticed that you have not submitted a TMN to be reviewed by your leaders. To complete your TMN go to http://mportal.ccca.org.au/TMN .";
        $this->reasons["authoriser-has-not-approved"]  = "We noticed that you have submitted it and it is waiting on {{authoriser_name}}. {{authoriser_name}}, please go to http://mportal.ccca.org.au/TMN/tmn-authviewer.php?session={{session_id}} to review the TMN.";

        $constants      = getConstants();
        $this->sinceForStudentLife  = new DateTime($constants['STUDENT_LIFE_ACTIVE_DATE']);
        $this->sinceForEveryone     = new DateTime($constants['EVERYONE_ACTIVE_DATE']);
    }

    public function sendEmailsFor(TmnFinancialUnit $financialUnit) {

        array_push($this->financialUnitsContacted, $financialUnit);

        $mustache               = new Mustache_Engine;
        $address                = $financialUnit->getEmails() . ", " . $financialUnit->getAuthoriserEmailsForLevel(1);
        $subject                = $this->subject;
        $tmnsAwaitingApproval   = $financialUnit->getTmnsAwaitingApprovalSince($this->getSinceDateFor($financialUnit));
        $reason                 = "";

        if (count($tmnsAwaitingApproval) > 0) {
            $tmn                = $tmnsAwaitingApproval[0];
            $reasonTemplate     = $this->reasons["authoriser-has-not-approved"];
            $reason             = $mustache->render($reasonTemplate, array( "authoriser_name"   => $tmn->currentApproversName(),
                                                                            "session_id"        => $tmn->getField('session_id')
                                                                            )
                                                    );
        } else {
            $reason             = $this->reasons["user-has-not-submitted"];
        }

        $body                   = $mustache->render($this->message, array(  "names"         => $financialUnit->getNames(),
                                                                            "reason"        => $reason,
                                                                            "authorisers"   => $financialUnit->getAuthoriserNamesForLevel(1)
                                                                            )
                                                    );

        fb("Report - to:". $address . " subject:" . $subject . " body: " . $body);
//        $email  = new Email($address, $subject, $body);
//        $email->send();

    }

    private function getSinceDateFor(TmnFinancialUnit $financialUnit) {

        $since  = null;

        if ($financialUnit->getMinistry() == 'StudentLife') {
            $since  = $this->sinceForStudentLife;
        } else {
            $since  = $this->sinceForEveryone;
        }

        return $since;

    }

} 