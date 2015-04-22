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

		$userArray    				= $this->produceAssocArray();
		
		$data["FIRSTNAME"]      	= $userArray['firstname'];
		$data["SURNAME"]        	= $userArray['surname'];
		$data["MINISTRY"]       	= $userArray['ministry'];
		$data["FT_PT_OS"]      	 	= $userArray['ft_pt_os'];
		$data["DAYS_PER_WEEK"]  	= $userArray['days_per_week'];
		
		if ( $this->getSpouse() ) {
			$spouseArray  			= $this->getSpouse()->produceAssocArray();
			
			$data["S_FIRSTNAME"]    = $spouseArray['firstname'];
			$data["S_SURNAME"]      = $spouseArray['surname'];
			$data["S_MINISTRY"]     = $spouseArray['ministry'];
			$data["S_FT_PT_OS"]     = $spouseArray['ft_pt_os'];
			$data["S_DAYS_PER_WEEK"]= $spouseArray['days_per_week'];
		}
		
		if ((int)$userArray['mpd'] == 1) {
		  	$mpdCoachArray= $this->getMpdCoach()->produceAssocArray();
		  
		  	$data["MPD"]            = $userArray['mpd'];
			$data["M_FIRSTNAME"]    = $mpdCoachArray['firstname'];
			$data["M_SURNAME"]      = $mpdCoachArray['surname'];
		} else {
			$data["MPD"]            = 0;
		}

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

		if ($this->hasSpouseFromData($spouseData)) {
		
			//update the spouse data
			$spouse = $this->findSpouseFromData($spouseData);

			if ($spouse != null) {

			  $spouse->setField('ministry', $spouseData['ministry']);
			  $spouse->setField('ft_pt_os', $spouseData['ft_pt_os']);
			  $spouse->setField('days_per_week', ( isset($spouseData['days_per_week']) ? $spouseData['days_per_week'] : $spouse->getField('days_per_week') ) );
			  $spouse->update();
			  $this->setField('spouse_guid', $spouse->getGuid());

			} else {
			  
			  $errors = array_merge($errors, array('S_FIRSTNAME' => 'Could not match this person to you. A Spouse must have a theKey account, which has previously logged into the TMN. The TMN must also have the same Financial Account Number registered for both of you. If you think there has been a mistake contact <a href="tech.team@ccca.org.au">tech.team@ccca.org.au</a>'));
			  return json_encode(array('success' => false, 'errors' => $errors));
			}
			
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

		//fb($this);
		$this->update();

		return json_encode(array('success' => true));

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

    if ($this->hasSpouseFromData($spouseData)) {

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
  
  private function hasSpouseFromData($spouseData) {
  
  	if (($spouseData['firstname'] == "" && $spouseData['surname'] == "") || empty($spouseData)) {
		return false;
    } else {
    	return true;
	}
	
  }

  private function findMpdCoachFromData($mpdData) {

    $mpdGuid  = $this->findUserWithName($mpdData['firstname'], $mpdData['surname']);

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
