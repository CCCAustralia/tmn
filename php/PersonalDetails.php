<?php
/**
 * The PersonalDetails Class file
 *
 * File containing definition for the PersonalDetails class.
 * @author Thomas Flynn [tom.flynn@ccca.org.au], Michael Harrison [michael.harrison@ccca.org.au]
 * @package TMN
 */

//Include the relevent php files
if(file_exists('../classes/TmnCrudUser.php')) {
  include_once('../classes/TmnCrudUser.php');
}
if(file_exists('classes/TmnCrudUser.php')) {
  include_once('classes/TmnCrudUser.php');
}
if(file_exists('php/classes/TmnCrudUser.php')) {
  include_once('php/classes/TmnCrudUser.php');
}

class PersonalDetails extends TmnCrudUser {

	//'get' mode
	public function getPersonalDetails() {

    $userArray    = $this->produceAssocArray();
    if ( $this->getSpouse() ) {
    	$spouseArray  = $this->getSpouse()->produceAssocArray();
    }
    if ((int)$userArray['mpd'] == 1) {
      $mpdCoachArray= $this->getMpdCoach()->produceAssocArray();
    }

    $data["FIRSTNAME"]      = $userArray['firstname'];
    $data["SURNAME"]        = $userArray['surname'];
    $data["MINISTRY"]       = $userArray['ministry'];
    $data["FT_PT_OS"]       = $userArray['ft_pt_os'];
    $data["DAYS_PER_WEEK"]  = $userArray['days_per_week'];
    $data["S_FIRSTNAME"]    = $spouseArray['firstname'];
    $data["S_SURNAME"]      = $spouseArray['surname'];
    $data["S_MINISTRY"]     = $spouseArray['ministry'];
    $data["S_FT_PT_OS"]     = $spouseArray['ft_pt_os'];
    $data["S_DAYS_PER_WEEK"]= $spouseArray['days_per_week'];
    $data["MPD"]            = $userArray['mpd'];
    $data["M_FIRSTNAME"]    = $mpdCoachArray['firstname'];
    $data["M_SURNAME"]      = $mpdCoachArray['surname'];

    return json_encode(array('success'=>'true', 'data'=>$data));
	}

	//'set' mode
	public function setPersonalDetails($post) {

    $errors       = array();
    $userData     = array();
    $spouseData   = array();
    $mpdData      = array();

    //split up the data
    foreach ($post as $key => $value) {

        if (substr($key, 0, 2) === "S_") {

          $keyWithoutPrefix  = substr($key, 2, strlen($key) - 2);
          $spouseData[strtolower($keyWithoutPrefix)] = $value;

        } else if (substr($key, 0, 2) === "M_") {

          $keyWithoutPrefix  = substr($key, 2, strlen($key) - 2);
          $mpdData[strtolower($keyWithoutPrefix)] = $value;

        } else {

          $userData[strtolower($key)] = $value;

        }

    }

    $errors = array_merge($errors, $this->validateUserData($userData));
    $errors = array_merge($errors, $this->validateSpouseData($spouseData));
    $errors = array_merge($errors, $this->validateMpdData($mpdData));

    if (count($errors) > 0) {
      return json_encode(array('success' => false, 'errors' => $errors));
    }

    //update the spouse data
    $spouse = $this->findSpouseFromData($spouseData);

    if ($spouse != null) {

      $spouse->setField('ministry', $spouseData['ministry']);
      $spouse->setField('ft_pt_os', $spouseData['ft_pt_os']);
      $spouse->setField('days_per_week', ( isset($spouseData['days_per_week']) ? $spouseData['days_per_week'] : $spouse->getField('days_per_week') ) );
      $spouse->update();
      $this->setField('spouse_guid', $spouse->getGuid());

    } else {
      fb($errors);
      $errors = array_merge($errors, array('S_FIRSTNAME' => 'Could not match this person to you. A Spouse must have a theKey account, which has previously logged into the TMN. The TMN must also have the same Financial Account Number registered for both of you. If you think there has been a mistake contact <a href="tech.team@ccca.org.au">tech.team@ccca.org.au</a>'));
      fb($errors);
      return json_encode(array('success' => false, 'errors' => $errors));
    }

    //update mpd data
    if ($userData['mpd'] == 1) {

      $mpdCoach = $this->findMpdCoachFromData($mpdData);

      if ($mpdCoach != null) {

        $this->setField('mpd', 1);
        $this->setMpdGuid($mpdCoach->getGuid());

      } else {

        $errors = array_merge($errors, array('M_FIRSTNAME' => 'Could not find this person. Try another name.'));
        return json_encode(array('success' => false, 'errors' => $errors));

      }

    } else {
      $this->setField('m_guid', null);
      $this->setField('mpd', 0);
    }

    $this->setField('ministry', $userData['ministry']);
    $this->setField('ft_pt_os', $userData['ft_pt_os']);
    $this->setField('days_per_week', ( isset($userData['days_per_week']) ? $userData['days_per_week'] : $this->getField('days_per_week') ) );

fb($this);
    $this->update();

    return json_encode(array('success' => true));

		// if ($this->DEBUG) {fb($main_post); fb($spouse_post);}
    //
		// //Check that if one field for spouse name is entered, the other is also
		// if ($spouse_post['S_FIRSTNAME'] != "" && $spouse_post['S_SURNAME'] == "") {
		// 	$err .= "S_SURNAME:\"Firstname entered: Please enter a surname.\", ";
		// }
		// if ($spouse_post['S_FIRSTNAME'] == "" && $spouse_post['S_SURNAME'] != "") {
		// 	$err .= "S_FIRSTNAME:\"Surname entered: Please enter a firstname.\", ";
		// }
    //
		// //Get the SPOUSE_GUID for the current User_Profile
		// $temp_arr = mysql_fetch_assoc(mysql_query("SELECT SPOUSE_GUID, FIN_ACC_NUM FROM User_Profiles WHERE GUID='".$this->userguid."'"));
		// if($this->DEBUG) fb($temp_arr);
		// $mainfan = $temp_arr['FIN_ACC_NUM'];
		// $spouseguid = $temp_arr['SPOUSE_GUID'];
		// $spousefirstname = $spouse_post['S_FIRSTNAME'];
		// $spousesurname = $spouse_post['S_SURNAME'];
    //
		// if ($spousefirstname != '' && $spousesurname != '') {	// If values in spouse fields
    //
		// 	//Server-side conditional allowblank - if spouse entered:
		// 	//Don't allow ministry to be blank
		// 	if ($spouse_post['S_MINISTRY']  == "") {
		// 		$err .= "S_MINISTRY:\"Spouse entered: Please enter a ministry.\", ";
		// 	}
		// 	//Don't allow ftptos to be blank
		// 	if ($spouse_post['S_FT_PT_OS'] == "") {
		// 		$err .= "S_FT_PT_OS:\"Spouse entered: Please select an option.\", ";
		// 	}
    //
		// 	//try to fetch the spouse's guid & FAN from firstname and lastname
		// 	$temp_arr = mysql_fetch_assoc(mysql_query("SELECT GUID, FIN_ACC_NUM FROM User_Profiles WHERE FIRSTNAME='".$spousefirstname."' && SURNAME='".$spousesurname."'"));
		// 	if($this->DEBUG) fb($temp_arr);
		// 	$spouseguid_fromname = $temp_arr['GUID'];
		// 	$spousefan = $temp_arr['FIN_ACC_NUM'];
    //
		// 	$spousecase = -1;
    //
		// 	//Spouse conditions:
		// 	if ($spouseguid == '') {		//If spouse not already linked in user's profile:
		// 		if ($mainfan == $spousefan && $spouseguid_fromname != '') {
		// 			$spousecase = 0;		//If FinAccNums match and spouse's guid can be found using firstname and lastname (above sql): link the profiles
		// 		}
		// 		else {
		// 			if ($mainfan != $spousefan && $spouseguid_fromname != '') {
		// 				$spousecase = 1;	//This is true if the firstname/surname pair that was input matches a user in the database, but the two users have different FANs
		// 			}
		// 			else {
		// 				$spousecase = 2;	//This is true if no linked spouse can be found, and the firstname/surname pair doesn't match a user
		// 			}
		// 		}
		// 	} else {						//If the spouse is already linked
		// 		if ($spouseguid_fromname == '') {
		// 			$spousecase = 3;		//If the user has a linked spouse, but no guid can be found for the firstname/surname pair that was input
		// 		}
		// 	}
		// }
    //
		// switch ($spousecase) {	//for case explanations, see the above set of conditionals
		// 	case 0:
		// 		if ($spouseguid_fromname != ''){
		// 			$q = "UPDATE User_Profiles SET SPOUSE_GUID='".$spouseguid_fromname."' WHERE GUID='".$this->userguid."'";	//form sql
		// 			mysql_query($q);							//update main user
		// 			if ($this->DEBUG) $this->logger->logToFile($q);	//log sql
    //
		// 			$sq = "UPDATE User_Profiles SET SPOUSE_GUID='".$this->userguid."' WHERE GUID='".$spouseguid_fromname."'";	//form spouse sql
		// 			mysql_query($sq);							//update spouse
		// 			if ($this->DEBUG) $this->logger->logToFile($sq);	//log spouse sql
    //
		// 			//set $spouseguid to the newly added spouse guid for later use
		// 			$spouseguid = $spouseguid_fromname;
		// 		}
		// 	break;
		// 	case 1:
		// 		$err .= "S_FIRSTNAME:\"Invalid Spouse: Financial Account Numbers do not match.<br />Spousal Financial Account numbers must be the same. If this needs to be changed, talk to your Ministry Supervisor.\", ";
		// 		$err .= "S_SURNAME:\"Invalid Spouse: Financial Account Numbers do not match.<br />Spousal Financial Account numbers must be the same. If this needs to be changed, talk to your Ministry Supervisor.\", ";
		// 	break;
		// 	case 2:
		// 		$err .= "S_FIRSTNAME:\"Cannot find spouse's details in database. Your spouse may not be registered, to check, <a href=".addslashes("http://mportal.ccca.org.au/TMN/?logout").">click here to logout</a>. Then get your spouse to log in to the TMN form.\", ";
		// 		$err .= "S_SURNAME:\"Cannot find spouse's details in database. Your spouse may not be registered, to check, <a href=".addslashes("http://mportal.ccca.org.au/TMN/?logout").">click here to logout</a>. Then get your spouse to log in to the TMN form.\", ";
		// 	break;
		// 	case 3:
		// 		$err .= "S_FIRSTNAME:\"Spouse's GCX account is not registered. To register, please <a href=".addslashes("http://mportal.ccca.org.au/TMN/?logout").">logout</a> and get your spouse to login to the TMN using their own GCX account.\", ";
		// 		$err .= "S_SURNAME:\"Spouse's GCX account is not registered. To register, please <a href=".addslashes("http://mportal.ccca.org.au/TMN/?logout").">logout</a> and get your spouse to login to the TMN using their own GCX account.\", ";
		// 	break;
		// }
    //
		// //MPD Supervisor:
		// if ($main_post['MPD'] == '0') {
		// 	$mpdsuper_guid = 'NULL';
		// } else {
		// 	//Set the mpd supervisor firstname, then remove it from the array (so it's not put into the update sql)
		// 	$mpdsuper_firstname = $main_post['M_FIRSTNAME'];
		// 	unset($main_post['M_FIRSTNAME']);
		// 	//Surname
		// 	$mpdsuper_surname = $main_post['M_SURNAME'];
		// 	unset($main_post['M_SURNAME']);
    //
		// 	//Lookup the guid for the given mpd supervisor
		// 	$mpdsuper_sql = "SELECT GUID FROM User_Profiles WHERE FIRSTNAME LIKE '".$mpdsuper_firstname."' && SURNAME LIKE '".$mpdsuper_surname."'";
		// 	if ($this->DEBUG) $this->logger->logToFile("MPD_SUPER FETCH: ".$mpdsuper_sql);
		// 	$temp_arr = mysql_fetch_assoc(mysql_query($mpdsuper_sql));
		// 	if ($this->DEBUG) fb($temp_arr);
    //
		// 	//if found, set the mpdsupervisor guid, otherwise remove the names from the array and add an error
		// 	if ($temp_arr['GUID'] != "") {
		// 		$mpdsuper_guid = $temp_arr['GUID'];
		// 	} else {
		// 		$mpdsuper_guid = 'NULL';
		// 		unset($main_post['M_FIRSTNAME']);
		// 		unset($main_post['M_SURNAME']);
		// 		$err .= "M_FIRSTNAME:\"Cannot find MPD supervisor in database. This must be exact. Check with your supervisor to confirm.\", ";
		// 		$err .= "M_SURNAME:\"Cannot find MPD supervisor in database. This must be exact. Check with your supervisor to confirm.\", ";
		// 	}
		// }
    //
		// //Ensure DAYS_PER_WEEK is set to 5 days if not part time (i.e. if FT or OS)
		// if ($main_post['FT_PT_OS'] != 1) {		//if not PT
		// 	$main_post['DAYS_PER_WEEK'] = 4;	//NB: DAYS_PER_WEEK is an index
		// }
		// if ($spouse_post['S_FT_PT_OS'] != 1) {	//if not PT
		// 	$spouse_post['S_DAYS_PER_WEEK'] = 4;//NB: DAYS_PER_WEEK is an index
		// }
    //
		// //Main user sql formation
		// $setsql = "UPDATE User_Profiles SET ";
		// foreach($main_post as $k=>$v) {
		// 	$setsql .= $k."='".$v."', ";
		// }
    //
		// //Set the supervisor guid (setting to null VALUE instead of null STRING (no apostrophe))
		// if ($mpdsuper_guid == 'NULL')
		// 	$setsql .= "M_GUID=".$mpdsuper_guid.", ";
		// else
		// 	if ($mpdsuper_guid == $this->userguid)
		// 		$err .= "M_FIRSTNAME:\"You cannot be your own supervisor.\", M_SURNAME:\"You cannot be your own supervisor.\", ";
		// 	else
		// 		$setsql .= "M_GUID='".$mpdsuper_guid."', ";
    //
		// //Complete the sql
		// $setsql = trim($setsql,", ");
		// $setsql .= " WHERE GUID='".$this->userguid."'";
    //
		// //Execute and log the Main user sql if no errors
		// if ($err == "") {
		// 	$sqlresult = mysql_query($setsql);		//Execute the Main user sql
		// } else if ($this->DEBUG) $this->logger->logToFile("Errors! ".$err);
		// if ($this->DEBUG) $this->logger->logToFile("Main User SQL: ".$setsql."\nResult: ".$sqlresult);
    //
		// //setup the sql statment for the spouse
		// $s_setsql = "UPDATE User_Profiles SET ";
    //
		// //Spouse sql formation
		// if ($spouseguid != '') {
		// 	$s_setsql .= "FT_PT_OS='".$spouse_post['S_FT_PT_OS']."', ";
    //
		// 	$s_setsql .= "DAYS_PER_WEEK='".$spouse_post['S_DAYS_PER_WEEK']."', ";
    //
		// 	$s_setsql .= "MINISTRY='".$spouse_post['S_MINISTRY']."', ";
    //
		// 	$s_setsql .= "MPD='".$main_post['MPD']."', ";
		// }
		// if ($DEBUG) fb($spouseguid);
		// //finish the sql statement
		// $s_setsql = trim($s_setsql,", ");
		// $s_setsql .= " WHERE GUID ='".$spouseguid."'";
    //
		// //Execute and log the spouse sql if no errors
		// if ($err == "") {
		// 	$sqlspouseresult = mysql_query($s_setsql);
		// }
		// if ($this->DEBUG) $this->logger->logToFile("Spouse User SQL: ".$s_setsql."\nResult: ".$sqlspouseresult);
    //
		// //Return json success/failure with errors
		// if ($sqlresult == 1 && $sqlspouseresult == 1 && $err == NULL) {
		// 	$return = '{"success": true}';
		// 	//echo '{success: true, spouse: "'.$spouseguid.'", ft_pt_os: "'.$main_post['FT_PT_OS'].'", s_ft_pt_os: "'.$spouse_post['S_FT_PT_OS'].'"}';
		// } else {
		// 	if ($err != NULL)
		// 	$return = '{"success": false, "errors":{'.trim($err,", ").'} }'; //Return with errors
		// 	else
		// 	$return = '{"success": true}';
		// }
    //
		// return $return;
	}

  private function validateUserData($userData) {

    $errors = array();

    if ($userData['firstname'] != "" && $userData['surname'] == "") {
      $errors['SURNAME'] = "Firstname entered: Please enter a surname.";
    }

    if ($userData['firstname'] == "" && $userData['surname'] != "") {
       $errors['FIRSTNAME'] = "Surname entered: Please enter a firstname.";
    }

    if ($userData['ministry']  == "") {
      $errors['MINISTRY'] = "Please enter a ministry.";
    }

    if ($userData['ft_pt_os'] == "") {
      $errors['FT_PT_OS'] = "Please select an option.";
    }

    return $errors;

  }

  private function validateSpouseData($spouseData) {

    $errors = array();

    if ($spouseData['firstname'] == "" && $spouseData['surname'] == "") {

      return array();

    } else {

      if ($spouseData['firstname'] != "" && $spouseData['surname'] == "") {
        $errors['S_SURNAME'] = "Firstname entered: Please enter a surname.";
      }

      if ($spouseData['firstname'] == "" && $spouseData['surname'] != "") {
         $errors['S_FIRSTNAME'] = "Surname entered: Please enter a firstname.";
      }

      if ($spouseData['ministry']  == "") {
        $errors['S_MINISTRY'] = "Spouse entered: Please enter a ministry.";
      }

      if ($spouseData['ft_pt_os'] == "") {
        $errors['S_FT_PT_OS'] = "Spouse entered: Please select an option.";
      }

    }

    return $errors;
  }

  private function validateMpdData($mpdData) {

    $errors = array();

    if ($mpdData['firstname'] != "" && $mpdData['surname'] == "") {
      $errors['M_SURNAME'] = "Firstname entered: Please enter a surname.";
    }

    if ($mpdData['firstname'] == "" && $mpdData['surname'] != "") {
       $errors['M_FIRSTNAME'] = "Surname entered: Please enter a firstname.";
    }

    return $errors;
  }

  private function findMpdCoachFromData($mpdData) {
fb($mpdData);
    $mpdGuid  = $this->findUserWithName($mpdData['firstname'], $mpdData['surname']);
fb($mpdGuid);
    $mpdCoach = new TmnCrudUser($this->getLogfile(), $mpdGuid);

    return $mpdCoach;

  }

  private function findSpouseFromData($spouseData) {

    $spouseGuid = $this->findUserWithName($spouseData['firstname'], $spouseData['surname']);

    if ($spouseGuid != null) {

      $spouse = new TmnCrudUser($this->getLogfile(), $spouseGuid);

      if ($this->getFan() == $spouse->getFan()) {

        return $spouse;

      } else {

        return null;

      }

    } else {

      return null;

    }

  }


}
