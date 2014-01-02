<?php

namespace tests;

include_once("../php/classes/TmnNotifier.php");

class TmnNotiferTest extends PHPUnit_Framework_TestCase {

    protected static $notifier = null;

    public static function setUpBeforeClass() {

        self::$notifier = new TmnNotifer("reminder_round_one");

    }

    public function testRoundOneSendEmailsFor() {

        $this->assertFalse(TRUE);

    }

    public static function tearDownAfterClass() {

    }

}
 