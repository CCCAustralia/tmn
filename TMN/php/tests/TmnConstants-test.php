<?php

include_once("../../lib/FirePHPCore/fb.php");
include_once('../classes/TmnConstants.php');

fb("Version Number:");
fb(getVersionNumber());

fb("Constants:");
fb(getConstants(getVersionNumber()));

?>