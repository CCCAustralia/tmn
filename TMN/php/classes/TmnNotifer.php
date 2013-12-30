<?php
if(file_exists('../classes/TmnDatabase.php')) {
    include_once('../classes/TmnDatabase.php');
    include_once('../classes/TmnRoundOneNotifier.php');
    include_once('../classes/TmnRoundTwoNotifier.php');
    include_once('../classes/TmnRoundThreeNotifier.php');
    include_once('../classes/TmnRoundFourNotifier.php');
}
if(file_exists('classes/TmnDatabase.php')) {
    include_once('classes/TmnDatabase.php');
    include_once('classes/TmnRoundOneNotifier.php');
    include_once('classes/TmnRoundTwoNotifier.php');
    include_once('classes/TmnRoundThreeNotifier.php');
    include_once('classes/TmnRoundFourNotifier.php');
}
if(file_exists('php/classes/TmnDatabase.php')) {
    include_once('php/classes/TmnDatabase.php');
    include_once('php/classes/TmnRoundOneNotifier.php');
    include_once('php/classes/TmnRoundTwoNotifier.php');
    include_once('php/classes/TmnRoundThreeNotifier.php');
    include_once('php/classes/TmnRoundFourNotifier.php');
}

class TmnNotifer {

    protected $round    = "";

    protected $financialUnitsContacted = array();
    protected $subject  = "";
    protected $message  = "";

    public static function create($action) {

        $notifier   = null;

        switch ($action) {

            case "reminder_round_one":
                $notifier   = new TmnRoundOneNotifier();
                break;

            case "reminder_round_two":
                $notifier   = new TmnRoundTwoNotifier();
                break;

            case "reminder_round_three":
                $notifier   = new TmnRoundThreeNotifier();
                break;

            case "reminder_round_four":
                $notifier   = new TmnRoundFourNotifier();
                break;

            default:
                break;

        }

        return $notifier;

    }

    public function sendReportToMemberCare() {

        $address    = $this->memberCareEmails();
        $subject    = "TMN Reminder Report: Round " . ( $this->round ? $this->round : 1 );
        $body       = "Hi MemberCarers, <br /><br />Here is a report of what was just sent out. The following people have not submitted TMNs (the leaders to the right of their names have been cced on the email so that they can discuss):<br />";

        $email  = new Email($address, $subject, $body);
        $email->send();

    }

    public function memberCareEmails() {

        return "";

    }

    public function sendCount() {

        return count($this->financialUnitsContacted);

    }

} 