<?php
/**
 * Personal Details processing file
 *
 * This is the file called to get and set a user's personal profile details.
 * @author Thomas Flynn <tom.flynn[at]ccca.org.au>, Michael Harrison <michael.harrison[at]ccca.org.au>
 * @package TMN
 */

//Include the relevent php files
include_once "classes/Tmn.php";
include_once "PersonalDetails.php";
$logfile    = "logs/personal_details.log";
$DEBUG      = 1;
$mode       = $_REQUEST['mode'];		//get/set

try {

    $tmn = new Tmn($logfile);

    if ($tmn->isAuthenticated() && $tmn->isCurrentUserARegisteredTmnUser()) {

        $personal_details = new PersonalDetails($logfile, $tmn->getAuthenticatedGuid());

        if ($mode == 'get') {
            echo $personal_details->getPersonalDetails();
        }

        if ($mode == 'set') {

            echo $personal_details->setPersonalDetails($_POST);

            // //Split the POST variables into two arrays: Main user and spouse
            // foreach($_POST as $k=>$v) {			//Loop through the POST key/val pairs
            //
            //     if (strpos($v, ",")) {			//Invalid character check, also makes sql injection harder
            //         $err .= $k.":\" Invalid character in field.\", ";
            //     }
            //
            //     if (substr($k,0,2) == 'S_') {	//If spouse variable (defined by S_ prefix)
            //
            //         $spouse_post[$k]=$v;		//Add to spouse variable array
            //
            //     } else {							//No spouse prefix
            //
            //         if ($k != 'mode') {			//mode variable will be false positive in this case and break the sql
            //             $main_post[$k]=$v;		//Add to main user variable array
            //         }
            //
            //     }
            // }
            //
            // echo $personal_details->setPersonalDetails($main_post, $spouse_post);
        }

    } else {

        echo '{"success": false, "errors": {FIRSTNAME: "User not in database, please contact <a href="tech.team@ccca.org.au">tech.team@ccca.org.au</a>.", SURNAME: "User not in database, please contact <a href="tech.team@ccca.org.au">tech.team@ccca.org.au</a>." } }';
    }

} catch (Exception $exception) {

    die(json_encode(array("success" => false, "errors" => array("FIRSTNAME" => 'Authentication failed due to Database Error. Please contact <a href="tech.team@ccca.org.au">tech.team@ccca.org.au</a>.'))));
}
