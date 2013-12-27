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

} 