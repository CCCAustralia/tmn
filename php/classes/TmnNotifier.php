<?php
if(file_exists('../classes/TmnDatabase.php')) {
//    include_once('../classes/Reporter.php');
    include_once('../classes/TmnDatabase.php');
    include_once("../classes/TmnMembercareAdminsUsersGroup.php");
    include_once('../classes/TmnRoundOneNotifier.php');
    include_once('../classes/TmnRoundTwoNotifier.php');
//    include_once('../classes/TmnRoundThreeNotifier.php');
//    include_once('../classes/TmnRoundFourNotifier.php');
    require_once '../../lib/mustache/src/Mustache/Autoloader.php';
    Mustache_Autoloader::register();
}
if(file_exists('classes/TmnDatabase.php')) {
//    include_once('classes/Reporter.php');
    include_once('classes/TmnDatabase.php');
    include_once("classes/TmnMembercareAdminsUsersGroup.php");
    include_once('classes/TmnRoundOneNotifier.php');
    include_once('classes/TmnRoundTwoNotifier.php');
//    include_once('classes/TmnRoundThreeNotifier.php');
//    include_once('classes/TmnRoundFourNotifier.php');
    require_once '../lib/mustache/src/Mustache/Autoloader.php';
    Mustache_Autoloader::register();
}
if(file_exists('php/classes/TmnDatabase.php')) {
//    include_once('php/classes/Reporter.php');
    include_once('php/classes/TmnDatabase.php');
    include_once("php/classes/TmnMembercareAdminsUsersGroup.php");
    include_once('php/classes/TmnRoundOneNotifier.php');
    include_once('php/classes/TmnRoundTwoNotifier.php');
//    include_once('php/classes/TmnRoundThreeNotifier.php');
//    include_once('php/classes/TmnRoundFourNotifier.php');
    require_once 'lib/mustache/src/Mustache/Autoloader.php';
    Mustache_Autoloader::register();
}

class TmnNotifier {

    protected $round                    = "";
    protected $level                    = 0;

    protected $financialUnitsContacted  = array();
    protected $subject                  = "";
    protected $message                  = "";

    protected $sinceForStudentLife      = null;
    protected $sinceForEveryone         = null;

    public static $ALL                          = "all";
    public static $USER_HAS_NOT_SUBMITTED       = "user-has-not-submitted";
    public static $AUTHORISER_HAS_NOT_APPROVED  = "authoriser-has-not-approved";

    public static function create($action) {

        $notifier   = null;

        switch ($action) {

            case "reminder_round_one":
                $notifier   = new TmnRoundOneNotifier();
                break;

            case "reminder_round_two":
                $notifier   = new TmnRoundTwoNotifier();
                break;

//            case "reminder_round_three":
//                $notifier   = new TmnRoundThreeNotifier();
//                break;
//
//            case "reminder_round_four":
//                $notifier   = new TmnRoundFourNotifier();
//                break;

            default:
                break;

        }

        $constants      = getConstants();
        $notifier->sinceForStudentLife  = new DateTime($constants['STUDENT_LIFE_ACTIVE_DATE']);
        $notifier->sinceForEveryone     = new DateTime($constants['EVERYONE_ACTIVE_DATE']);

        return $notifier;

    }

    public function sendEmailsFor(TmnFinancialUnit $financialUnit) {

        $mustache               = new Mustache_Engine;
        $address                = $financialUnit->getEmails() . ", " . $financialUnit->getAuthoriserEmailsForLevel($this->level);
        $subject                = $this->subject;
        $tmnsAwaitingApproval   = $financialUnit->getTmnsAwaitingApprovalSince($this->getSinceDateFor($financialUnit));
        $reason                 = "";

        if (count($tmnsAwaitingApproval) > 0) {

            $tmn                = $tmnsAwaitingApproval[0];
            $reasonTemplate     = $this->reasons[TmnNotifier::$AUTHORISER_HAS_NOT_APPROVED];
            $reason             = $mustache->render($reasonTemplate, array( "authoriser_name"   => $tmn->currentApproversName(),
                    "session_id"        => $tmn->getField('session_id')
                )
            );

            $this->logWaitingNotificationForFinancialUnit($financialUnit);

        } else {

            $reason             = $this->reasons[TmnNotifier::$USER_HAS_NOT_SUBMITTED];

            $this->logUnsubmittedNotificationForFinancialUnit($financialUnit);

        }

        $body                   = $mustache->render($this->message, array(  "names"         => $financialUnit->getNames(),
                "reason"        => $reason,
                "authorisers"   => $financialUnit->getAuthoriserNamesForLevel($this->level)
            )
        );

        //fb("Report - to:". $address . " subject:" . $subject . " body: " . $body);
//        $email  = new Email($address, $subject, $body);
//        $email->send();

    }

    public function sendReportToMemberCare() {

        $mustache   = new Mustache_Engine;
        $address    = $this->memberCareEmails();
        $subject    = "TMN Reminder Report - Round " . ( $this->round ? $this->round : 1 );
        $body       = "Hi MemberCarers, <br /><br />Here is a report of the TMN reminders that was just sent out.<br /><br />The following people have not submitted TMNs (the leaders to the right of their names have been cced on the email so that they can discuss why it has not been submitted):<br /><table><tr><th>Missionary</th><th>Leaders</th></tr>";

        $template   = '<tr><td><a href="mailto:{{email_addresses}}">{{names}}</a></td><td><a href="mailto:{{approver_email_addresses}}">{{approver_names}}</a></td></tr>';

        $arrayOfFinancialUnitsWithUnsubmittedTmns   = ( isset($this->financialUnitsContacted[TmnNotifier::$USER_HAS_NOT_SUBMITTED]) ? $this->financialUnitsContacted[TmnNotifier::$USER_HAS_NOT_SUBMITTED] : array() );

        foreach($arrayOfFinancialUnitsWithUnsubmittedTmns as $key => $financialUnit) {

            $body   .= $mustache->render($template, array(
                "email_addresses" => $financialUnit->getEmails(),
                "names" => $financialUnit->getNames(true),
                "approver_email_addresses" => $financialUnit->getAuthoriserEmailsForLevel($this->level),
                "approver_names" => $financialUnit->getAuthoriserNamesForLevel($this->level, true),
            ));

        }

        $body   .= "</table><br />They received the following email:<br />";
        $body   .= $mustache->render($this->message, array("reason" => $this->reasons[TmnNotifier::$USER_HAS_NOT_SUBMITTED]) );

        $body   .= "<br /><br /><br />The following people have TMNs waiting to be approved (the leaders to the right of their names have been cced on the email so that they can discuss why it has not been approved):<br /><table><tr><th>Missionary</th><th>Leaders</th></tr>";

        $arrayOfFinancialUnitsWaitingOnTmns   = ( isset($this->financialUnitsContacted[TmnNotifier::$AUTHORISER_HAS_NOT_APPROVED]) ? $this->financialUnitsContacted[TmnNotifier::$AUTHORISER_HAS_NOT_APPROVED] : array() );

        foreach($arrayOfFinancialUnitsWaitingOnTmns as $key => $financialUnit) {

            $body   .= $mustache->render($template, array(
                "email_addresses" => $financialUnit->getEmails(),
                "names" => $financialUnit->getNames(true),
                "approver_email_addresses" => $financialUnit->getAuthoriserEmailsForLevel($this->level),
                "approver_names" => $financialUnit->getAuthoriserNamesForLevel($this->level, true),
            ));

        }

        $body   .= "</table><br />They received the following email:<br />";
        $body   .= $mustache->render($this->message, array("reason" => $this->reasons[TmnNotifier::$AUTHORISER_HAS_NOT_APPROVED]) );

        $body   .= "<br /><br />We hope this report was informative.<br /><br />God Bless<br />- TMN Development Team.";

        echo("to: ". $address . "<br />subject: " . $subject . "<br />body:<br />" . $body);
//        $email  = new Email($address, $subject, $body);
//        $email->send();

    }

    public function memberCareEmails() {

        $membercareAdminsUserGroup	= new TmnMembercareAdminsUsersGroup();
        return $membercareAdminsUserGroup->getEmailsAsString();

    }

    public function sendCount() {

        return count($this->financialUnitsContacted[TmnNotifier::$ALL]);

    }

    protected function getSinceDateFor(TmnFinancialUnit $financialUnit) {

        $since  = null;

        if ($financialUnit->getMinistry() == 'StudentLife') {
            $since  = $this->sinceForStudentLife;
        } else {
            $since  = $this->sinceForEveryone;
        }

        return $since;

    }

    protected function logUnsubmittedNotificationForFinancialUnit(TmnFinancialUnit $financialUnit) {

        $this->pushFinancialUnitForKey($financialUnit, TmnNotifier::$USER_HAS_NOT_SUBMITTED);

        $this->logNotificationForFinancialUnit($financialUnit);
    }

    protected function logWaitingNotificationForFinancialUnit(TmnFinancialUnit $financialUnit) {

        $this->pushFinancialUnitForKey($financialUnit, TmnNotifier::$AUTHORISER_HAS_NOT_APPROVED);

        $this->logNotificationForFinancialUnit($financialUnit);
    }

    protected function logNotificationForFinancialUnit(TmnFinancialUnit $financialUnit) {

        $this->pushFinancialUnitForKey($financialUnit, TmnNotifier::$ALL);

        for ($levelCount = 1; $levelCount < $this->level; $levelCount++) {

            $this->pushFinancialUnitForKey($financialUnit, $financialUnit->auth_guid_array[$levelCount]);

        }

    }

    private function pushFinancialUnitForKey(TmnFinancialUnit $financialUnit, $key) {

        if (!isset($this->financialUnitsContacted[$key])) {
            $this->financialUnitsContacted[$key] = array();
        }

        array_push($this->financialUnitsContacted[$key], $financialUnit);

    }

} 