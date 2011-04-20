<?php
if (file_exists('../interfaces/TmnCrudSessionInterface.php')) {
	include_once('../interfaces/TmnCrudSessionInterface.php');
	include_once('../classes/TmnCrud.php');
	include_once('../classes/TmnCrudUser.php');
	include_once('../classes/TmnAuthorisationProcessor.php');
}
if (file_exists('interfaces/TmnCrudSessionInterface.php')) {
	include_once('interfaces/TmnCrudSessionInterface.php');
	include_once('classes/TmnCrud.php');
	include_once('classes/TmnCrudUser.php');
	include_once('classes/TmnAuthorisationProcessor.php');
}
if (file_exists('php/interfaces/TmnCrudSessionInterface.php')) {
	include_once('php/interfaces/TmnCrudSessionInterface.php');
	include_once('php/classes/TmnCrud.php');
	include_once('php/classes/TmnCrudUser.php');
	include_once('php/classes/TmnAuthorisationProcessor.php');
}

//This is an example of how to subclass TmnCrud
class TmnCrudSession extends TmnCrud implements TmnCrudSessionInterface {
	
	private $owner						=	null;
	private $homeAssignment				=	null;
	private $internationalAssignment	=	null;
	private $authorisationProcessor		=	null;
	
	public function __construct($logfile, $session_id=null) {
		
		parent::__construct(
			$logfile,						//path of logfile
			"Tmn_Sessions",					//name of table
			"session_id",					//name of table's primary key
			array(							//an assoc array of private field names and there types
				'fan'									=>	"i",
				'guid'									=>	"s"
			),
			array(							//an assoc array of public field names and there types
				'session_id'							=>	"i",
				'session_name'							=>	"s",
				'auth_session_id'						=>	"i",
				'home_assignment_session_id'			=>	"i",
				'international_assignment_session_id'	=>	"i",
				'date_modified'							=>	"s",
				'firstname'								=>	"s",
				'surname'								=>	"s",
				'fan'									=>	"i",
				'ministry'								=>	"s",
				'ministry_levy'							=>	"i",
				'ft_pt_os'								=>	"i",
				'days_per_wk'							=>	"i",
				's_firstname'							=>	"s",
				's_surname'								=>	"s",
				's_ministry'							=>	"s",
				's_ministry_levy'						=>	"i",
				's_ft_pt_os'							=>	"i",
				's_days_per_wk'							=>	"i",
				'os_assignment_start_date'				=>	"s",
				'os_assignment_end_date'				=>	"s",
				'os_resident_for_tax_purposes'			=>	"i",
				'os_overseas_housing'					=>	"i",
				'os_lafha'								=>	"i",
				's_os_lafha'							=>	"i",
				'net_stipend'							=>	"i",
				'tax'									=>	"i",
				'additional_tax'						=>	"i",
				'post_tax_super'						=>	"i",
				'taxable_income'						=>	"i",
				'pre_tax_super'							=>	"i",
				'pre_tax_super_mode'					=>	"s",
				'additional_life_cover'					=>	"i",
				'max_mfb'								=>	"i",
				'additional_housing_allowance'			=>	"i",
				'os_overseas_housing_allowance'			=>	"i",
				'financial_package'						=>	"i",
				'employer_super'						=>	"i",
				'mmr'									=>	"i",
				'stipend'								=>	"i",
				'housing_stipend'						=>	"i",
				'housing_mfb'							=>	"i",
				'mfb_rate'								=>	"i",
				'claimable_mfb'							=>	"i",
				'total_super'							=>	"i",
				'resc'									=>	"i",
				'super_fund'							=>	"i",
				'income_protection_cover_source'		=>	"i",
				's_net_stipend'							=>	"i",
				's_tax'									=>	"i",
				's_additional_tax'						=>	"i",
				's_post_tax_super'						=>	"i",
				's_taxable_income'						=>	"i",
				's_pre_tax_super'						=>	"i",
				's_pre_tax_super_mode'					=>	"s",
				's_additional_life_cover'				=>	"i",
				's_max_mfb'								=>	"i",
				's_additional_housing_allowance'		=>	"i",
				's_os_overseas_housing_allowance'		=>	"i",
				's_financial_package'					=>	"i",
				's_employer_super'						=>	"i",
				's_mmr'									=>	"i",
				's_stipend'								=>	"i",
				's_housing_stipend'						=>	"i",
				's_housing_mfb'							=>	"i",
				's_mfb_rate'							=>	"i",
				's_claimable_mfb'						=>	"i",
				's_total_super'							=>	"i",
				's_resc'								=>	"i",
				's_super_fund'							=>	"i",
				's_income_protection_cover_source'		=>	"i",
				'joint_financial_package'				=>	"i",
				'total_transfers'						=>	"i",
				'workers_comp'							=>	"i",
				'ccca_levy'								=>	"i",
				'tmn'									=>	"i",
				'buffer'								=>	"i",
				'regular_buffer'						=>	"i",
				'international_donations'				=>	"i",
				'additional_housing'					=>	"i",
				'monthly_housing'						=>	"i",
				'housing'								=>	"i",
				'housing_frequency'						=>	"i",
				'versionnumber'							=>	"s"
			)
		);
		
		try {
			if (isset($session_id)) {
				$this->setField('session_id', (int)$session_id);
				$this->retrieve();
			}
		} catch (Exception $e) {
			throw new FatalException(__CLASS__ . " Exception: " . $e->getMessage());
		}
	}
	
	
	////////////////////////ACCESSOR FUNCTIONS////////////////////////////
	
	
	
	public function getField($fieldname) {
		$value = parent::getField($fieldname);
		
		//if session name has been altered to get around type checking then remove that alteration
		if ($fieldname == 'session_name') {
			if (strlen($value) > 1) {
				$name_prefix	= substr($value, 0, 1);
				$name_suffix	= substr($value, 1);
				if ($name_prefix == "_") {
					if (is_numeric($name_suffix)) {
						$value = $name_suffix;
					}
				}
			}
		}
		
		return $value;
	}
	
	public function setField($fieldname, $value) {
		//if session name is a string that contains a number (ie is numeric) add '_' to the front to get around type checking
		if ($fieldname == 'session_name') {
			if (is_numeric($value)) {
				$value = "_" . $value;
			}
		}
		
		parent::setField($fieldname, $value);
	}
	
	public function financialYearsSinceSessionCreation() {
		//grab today and creation date and shift them 6 months for the financial year
		$todayShifted		= strtotime('-6 month', strtotime('now'));
		$creationShifted	= strtotime('-6 month', strtotime($this->getField('date_modified')));
		
		//Grab the years of each of those days
		$todayYear		= (int)date('Y', $todayShifted);
		$creationYear	= (int)date('Y', $creationShifted);
		
		//return the difference in the years
		return $todayYear - $creationYear;
	}
	
	public function applyInflation() {
		//rate
		$rate	= 1.025;
		
		//index stipend data
		$this->setField('stipend',				(int)round($this->getField('stipend')				* $rate));
		$this->setField('additional_tax',		(int)round($this->getField('additional_tax')		* $rate));
		$this->setField('post_tax_super',		(int)round($this->getField('post_tax_super')		* $rate));
		
		//index spouse stipend data
		$this->setField('s_stipend',			(int)round($this->getField('s_stipend')				* $rate));
		$this->setField('s_additional_tax',		(int)round($this->getField('s_additional_tax')		* $rate));
		$this->setField('s_post_tax_super',		(int)round($this->getField('s_post_tax_super')		* $rate));
		
		//index lafha
		$this->setField('os_lafha',				(int)round($this->getField('os_lafha')				* $rate));
		$this->setField('s_os_lafha',			(int)round($this->getField('s_os_lafha')			* $rate));
		
		//index pretax super if its not set automatically
		if ($this->getField('pre_tax_super_mode') != 'auto') {
			$this->setField('pre_tax_super',	(int)round($this->getField('pre_tax_super')			* $rate));
		}
		if ($this->getField('s_pre_tax_super_mode') != 'auto') {
			$this->setField('s_pre_tax_super',	(int)round($this->getField('s_pre_tax_super')		* $rate));
		}
		
		//index housing
		//$this->setField('housing',				(int)round($this->getField('housing')				* $rate));
		//$this->setField('os_overseas_housing',	(int)round($this->getField('os_overseas_housing')	* $rate));
		
		//index mmrs
		$this->setField('mmr',					(int)round($this->getField('mmr')					* $rate));
		$this->setField('s_mmr',				(int)round($this->getField('s_mmr')					* $rate));
	}
	
	public function getOwner() {
		//if a guid is set
		if ($this->getField('guid') != null) {
			
			//if the user object hasn't been made from the guid then create it
			if ($this->owner == null) {
				$this->owner = new TmnCrudUser($this->logfile, $this->getField('guid'));
			}
		
			//if it is already there or creation happened without throwing exceptions then return the object
			return $this->owner;
			
		} else {
			//if no guid set then make sure owner is null (data may have been wiped by parent in mean time so
			//if reset has been done then apply it here too) and return false
			$this->owner = null;
			return false;
		}
	}
	
	public function setOwner(TmnCrudUser $owner = null) {
		$this->owner	=	$owner;
		if ($owner != null) {
			$this->setField('guid', $this->owner->getGuid());
			$this->setField('fan', $this->owner->getFan());
		} else {
			$this->setField('guid', null);
			$this->setField('fan', null);
		}
	}
	
	public function getHomeAssignment() {
		//if an id is set
		if ($this->getField('home_assignment_session_id') != null) {
			
			//and if the homeAssignment object hasn't been made from the home_assignment_session_id then create it
			if ($this->homeAssignment == null) {
				$this->homeAssignment = new TmnCrudSession($this->logfile, $this->getField('home_assignment_session_id'));
			}
		
			//if it is already there or creation happened without throwing exceptions then return the object
			return $this->homeAssignment;
			
		} else {
			//if no home_assignment_session_id set then make sure owner is null (data may have been wiped by parent in mean time so
			//if reset has been done then apply it here too) and return false
			$this->homeAssignment = null;
			return false;
		}
	}
	
	public function setHomeAssignment(TmnCrudSession $home_assignment = null) {
		$this->homeAssignment	=	$home_assignment;
		if ($home_assignment != null) {
			$this->setField('home_assignment_session_id', $this->homeAssignment->getField('session_id'));
		} else {
			$this->setField('home_assignment_session_id', null);
		}
	}
	
	public function getInternationalAssignment() {
		//if an id is set
		if ($this->getField('international_assignment_session_id') != null) {
			
			//and if the internationalAssignment object hasn't been made from the international_assignment_session_id then create it
			if ($this->internationalAssignment == null) {
				$this->internationalAssignment = new TmnCrudSession($this->logfile, $this->getField('international_assignment_session_id'));
			}
		
			//if it is already there or creation happened without throwing exceptions then return the object
			return $this->internationalAssignment;
			
		} else {
			//if no international_assignment_session_id set then make sure owner is null (data may have been wiped by parent in mean time so
			//if reset has been done then apply it here too) and return false
			$this->internationalAssignment = null;
			return false;
		}
	}
	
	public function setInternationalAssignment(TmnCrudSession $international_assignment = null) {
		$this->internationalAssignment	=	$international_assignment;
		if ($international_assignment != null) {
			$this->setField('international_assignment_session_id', $this->internationalAssignment->getField('session_id'));
		} else {
			$this->setField('international_assignment_session_id', null);
		}
	}
	
	public function getOwnerGuid() {
		return $this->getField('guid');
	}
	
	public function setOwnerGuid($guid) {
		
			//if an owner already exists then load the user from the Database
		if ($this->owner != null) {
			$this->owner->setGuid($guid);
		} else {
			//if the owner object doesn't exist then make it
			$this->owner = new TmnCrudUser($this->logfile, $guid);
		}
		
		//if the owner creation/switching worked without throwing an exception then update the guid field
		$this->setField('guid', $guid);
		$this->setField('fan', $this->owner->getFan());
	}
	
	public function getAuthorisationProcessor() {
		//if an id is set
		if ($this->getField('auth_session_id') != null) {
			
			//and if the internationalAssignment object hasn't been made from the international_assignment_session_id then create it
			if ($this->authorisationProcessor == null) {
				$this->authorisationProcessor = new TmnAuthorisationProcessor($this->getLogfile(), $this->getField('auth_session_id'));
			}
		
			//if it is already there or creation happened without throwing exceptions then return the object
			return $this->authorisationProcessor;
			
		} else {
			//if no international_assignment_session_id set then make sure owner is null (data may have been wiped by parent in mean time so
			//if reset has been done then apply it here too) and return false
			$this->authorisationProcessor = null;
			return false;
		}
	}
	
	
	
			////////////////////////////JSON METHODS////////////////////////////
			
	
	
	public function loadDataFromJsonString($string) {
		//parse json string
		$jsonObj	= json_decode($string, true);
		
		//check if it parsed and returned a data object
		if (isset($jsonObj['data'])) {
			//check to see if the data object has an array of data to be loaded
			if (is_array($jsonObj['data'])) {
				//if there is data then load it
				$this->loadDataFromAssocArray($jsonObj['data']);
			} else {
				throw new LightException(__CLASS__ . " Exception: No Data in JSON String");
			}
		} else {
			throw new LightException(__CLASS__ . " Exception: JSON String could not be parsed");
		}
	}
	
	public function loadDataFromAssocArray($array) {
		$processedArray = $this->removeFormatingFromFields($array);
		
		//if session name is a string that contains a number (ie is numeric) add '_' to the front to get around type checking
		if (is_numeric($processedArray['session_name'])) {
			$processedArray['session_name'] = "_" . $processedArray['session_name'];
		}
		
		parent::loadDataFromAssocArray($processedArray);
	}
	
	private function removeFormatingFromFields($array) {
		
		if (!is_numeric($array['os_resident_for_tax_purposes'])) {
			//format os_resident_for_tax_purposes for display
			switch ($array['os_resident_for_tax_purposes']) {
				case "Non-Resident Of Australia":
					$array['os_resident_for_tax_purposes'] = 0;
					break;
				case "Resident Of Australia":
					$array['os_resident_for_tax_purposes'] = 1;
					break;
				default:
					$array['os_resident_for_tax_purposes'] = 1;
					break; 
			}
		}
		
		//format days per week for display
		$ftptosStmt = $this->db->query("SELECT * FROM FT_PT_OS");
		for ($i = 0; $i < $ftptosStmt->rowCount(); $i++) {
			$ftptos_row = $ftptosStmt->fetch(PDO::FETCH_ASSOC);
			$ftptos_map[$ftptos_row['value']] = $ftptos_row['key'];
		}
		
		if (!is_numeric($array['ft_pt_os'])) {
			$obj['ft_pt_os']		= $ftptos_map[$array['ft_pt_os']];
		}
		
		if (!is_numeric($array['s_ft_pt_os'])) {
			$obj['s_ft_pt_os']		= $ftptos_map[$array['s_ft_pt_os']];	//DAYS_PER_WEEK is an index
		}
		
		
		if (!is_numeric($array['mfb_rate'])) {
			//format mfb_rate for display
			switch ($array['mfb_rate']) {
				case "Zero":
					$array['mfb_rate'] = 0;
					break;
				case "Half":
					$array['mfb_rate'] = 1;
					break;
				case "Full":
					$array['mfb_rate'] = 2;
					break;
				default:
					$array['mfb_rate'] = 2;
					break; 
			}
		}
		
		if (!is_numeric($array['s_mfb_rate'])) {
			//format s_mfb_rate for display
			switch ($array['s_mfb_rate']) {
				case "Zero":
					$array['s_mfb_rate'] = 0;
					break;
				case "Half":
					$array['s_mfb_rate'] = 1;
					break;
				case "Full":
					$array['s_mfb_rate'] = 2;
					break;
				default:
					$array['s_mfb_rate'] = 2;
					break; 
			}
		}
		
		if (!is_numeric($array['super_fund'])) {
			//format super_fund for display
			switch ($array['super_fund']) {
				case "Other":
					$array['super_fund'] = 0;
					break;
				case "IOOF":
					$array['super_fund'] = 1;
					break;
				default:
					$array['super_fund'] = 1;
					break; 
			}
		}
		
		if (!is_numeric($array['s_super_fund'])) {
			//format s_super_fund for display
			switch ($array['s_super_fund']) {
				case "Other":
					$array['s_super_fund'] = 0;
					break;
				case "IOOF":
					$array['s_super_fund'] = 1;
					break;
				default:
					$array['s_super_fund'] = 1;
					break; 
			}
		}
		
		if (!is_numeric($array['income_protection_cover_source'])) {
			//format income_protection_cover_source for display
			switch ($array['income_protection_cover_source']) {
				case "Support Account":
					$array['income_protection_cover_source'] = 0;
					break;
				case "Super Fund":
					$array['income_protection_cover_source'] = 1;
					break;
				default:
					$array['income_protection_cover_source'] = 0;
					break; 
			}
		}
		
		if (!is_numeric($array['s_income_protection_cover_source'])) {
			//format s_income_protection_cover_source for display
			switch ($array['s_income_protection_cover_source']) {
				case "Support Account":
					$array['s_income_protection_cover_source'] = 0;
					break;
				case "Super Fund":
					$array['s_income_protection_cover_source'] = 1;
					break;
				default:
					$array['s_income_protection_cover_source'] = 0;
					break; 
			}
		}
		
		if (!is_numeric($array['housing_frequency'])) {
			//format housing_frequency for display
			switch ($array['housing_frequency']) {
				case "Monthly":
					$array['housing_frequency'] = 0;
					break;
				case "Fortnightly":
					$array['housing_frequency'] = 1;
					break;
				default:
					$array['housing_frequency'] = 0;
					break; 
			}
		}
		
		return $array;
	}
	
	public function produceTransferArray() {
		try {
			$returnArray	= array();
			
			$sql		= "SELECT TRANSFER_NAME, TRANSFER_AMOUNT FROM `Internal_Transfers` WHERE SESSION_ID=:session_id";
			$id			= array(":session_id" => $this->getField('session_id'));
			
			$transferStmt	= $this->db->prepare($sql);
			$transferStmt->execute($id);
			
			for ($transferCount=0; $transferCount < $transferStmt->rowCount(); $transferCount++) {
				$tranferResult = $transferStmt->fetch(PDO::FETCH_ASSOC);
				$returnArray[$transferCount]['name']	= $tranferResult['TRANSFER_NAME'];
				$returnArray[$transferCount]['amount']	= (int)$tranferResult['TRANSFER_AMOUNT'];
			}
			
			if ($this->getField('ministry_levy') > 0) {
				$returnArray[$transferCount]['name']	= $this->getField('ministry');
				$returnArray[$transferCount]['amount']	= (int)$this->getField('ministry_levy');
				$transferCount++;
			}
			
			if ($this->getField('s_ministry_levy') > 0) {
				$returnArray[$transferCount]['name']	= $this->getField('s_ministry');
				$returnArray[$transferCount]['amount']	= (int)$this->getField('s_ministry_levy');
				$transferCount++;
			}
			
			return $returnArray;
			
		} catch (Exception $e) {
			return array();
		}
	}
	
	public function produceJson() {
		return json_encode($this->produceAssocArray());
	}
	
	public function produceAssocArray() {
		$array			= parent::produceAssocArray();
		
		//if session name has been altered to get around type checking then remove that alteration
		if (strlen($array['session_name']) > 1) {
			$name_prefix	= substr($array['session_name'], 0, 1);
			$name_suffix	= substr($array['session_name'], 1);
			if ($name_prefix == "_") {
				if (is_numeric($name_suffix)) {
					$array['session_name'] = $name_suffix;
				}
			}
		}
		
		return $array;
	}
	
	public function produceAssocArrayForDisplay($add_auth_reasons=null) {
		//grab data
		$obj						= parent::produceAssocArray();
		
		//add transfer array
		$obj['transfers']			= $this->produceTransferArray();
		
		$obj['date']				= date("d M Y", strtotime($obj['data_modified']));
		
		//format os_resident_for_tax_purposes for display
		switch ($this->getField('os_resident_for_tax_purposes')) {
			case 0:
				$obj['os_resident_for_tax_purposes']			= "Non-Resident Of Australia";
				break;
			case 1:
				$obj['os_resident_for_tax_purposes']			= "Resident Of Australia";
				break;
			default:
				$obj['os_resident_for_tax_purposes']			= "Resident Of Australia";
				break; 
		}
		
		//format days per week for display
		$ftptosStmt = $this->db->query("SELECT * FROM FT_PT_OS");
		for ($i = 0; $i < $ftptosStmt->rowCount(); $i++) {
			$ftptos_row = $ftptosStmt->fetch(PDO::FETCH_ASSOC);
			$ftptos_map[$ftptos_row['key']] = $ftptos_row['value'];
		}
		
		$obj['ft_pt_os']		= $ftptos_map[$this->getField('ft_pt_os')];
		$obj['s_ft_pt_os']		= $ftptos_map[$this->getField('ft_pt_os')];	//DAYS_PER_WEEK is an index
		
		//format mfb_rate for display
		switch ($this->getField('mfb_rate')) {
			case 0:
				$obj['mfb_rate']			= "Zero";
				break;
			case 1:
				$obj['mfb_rate']			= "Half";
				break;
			case 2:
				$obj['mfb_rate']			= "Full";
				break;
			default:
				$obj['mfb_rate']			= "Full";
				break; 
		}
		
		//format mfb_rate for display
		switch ($this->getField('s_mfb_rate')) {
			case 0:
				$obj['s_mfb_rate']			= "Zero";
				break;
			case 1:
				$obj['s_mfb_rate']			= "Half";
				break;
			case 2:
				$obj['s_mfb_rate']			= "Full";
				break;
			default:
				$obj['s_mfb_rate']			= "Full";
				break; 
		}
		
		//format super_fund for display
		switch ($this->getField('super_fund')) {
			case 0:
				$obj['super_fund']			= "Other";
				break;
			case 1:
				$obj['super_fund']			= "IOOF";
				break;
			default:
				$obj['super_fund']			= "IOOF";
				break; 
		}
		
		//format s_super_fund for display
		switch ($this->getField('s_super_fund')) {
			case 0:
				$obj['s_super_fund']			= "Other";
				break;
			case 1:
				$obj['s_super_fund']			= "IOOF";
				break;
			default:
				$obj['s_super_fund']			= "IOOF";
				break; 
		}
		
		//format income_protection_cover_source for display
		switch ($this->getField('income_protection_cover_source')) {
			case 0:
				$obj['income_protection_cover_source']			= "Support Account";
				break;
			case 1:
				$obj['income_protection_cover_source']			= "Super Fund";
				break;
			default:
				$obj['income_protection_cover_source']			= "Support Account";
				break; 
		}
		
		//format s_income_protection_cover_source for display
		switch ($this->getField('s_income_protection_cover_source')) {
			case 0:
				$obj['s_income_protection_cover_source']			= "Support Account";
				break;
			case 1:
				$obj['s_income_protection_cover_source']			= "Super Fund";
				break;
			default:
				$obj['s_income_protection_cover_source']			= "Support Account";
				break; 
		}
		
		//format housing_frequency for display
		switch ($this->getField('housing_frequency')) {
			case 0:
				$obj['housing_frequency']			= "Monthly";
				break;
			case 1:
				$obj['housing_frequency']			= "Fortnightly";
				break;
			default:
				$obj['housing_frequency']			= "Monthly";
				break; 
		}
		
		
		//format authorisation warnings for display
		if ($add_auth_reasons != null) {
			if ($this->getAuthorisationProcessor()) {
				//add level 1 warnings
				$obj['auth_lv1_reasons']	= $this->authorisationProcessor->getReasonsArray(1);
				
				if (count($obj['auth_lv1_reasons']) > 0) {
					$obj['auth_lv1']		= 1;
				} else {
					$obj['auth_lv1']		= 0;
				}
				
				//add level 2 warnings
				$obj['auth_lv2_reasons']	= $this->authorisationProcessor->getReasonsArray(2);
				
				if (count($obj['auth_lv2_reasons']) > 0) {
					$obj['auth_lv2']		= 1;
				} else {
					$obj['auth_lv2']		= 0;
				}
				
				//add level 3 warnings
				$obj['auth_lv3_reasons']	= $this->authorisationProcessor->getReasonsArray(3);
				
				if (count($obj['auth_lv3_reasons']) > 0) {
					$obj['auth_lv3']		= 1;
				} else {
					$obj['auth_lv3']		= 0;
				}
			} else {
				$obj['auth_lv1_reasons']	= array();
				$obj['auth_lv1']			= 0;
				
				//add level 2 warnings
				$obj['auth_lv2_reasons']	= array();
				$obj['auth_lv2']			= 0;
				
				//add level 3 warnings
				$obj['auth_lv3_reasons']	= array();
				$obj['auth_lv3']			= 0;
			}
		}
		
		return $obj;
	}
	
	public function produceJsonForDisplay($add_auth_reasons=null) {
		return json_encode($this->produceAssocArrayForDisplay($add_auth_reasons));
	}
	
	
	
			///////////////////////AUTHORISATION METHODS////////////////////////
			
	
	
	public function submit( TmnCrudUser $auth_user, $auth_user_reasons = null, TmnCrudUser $auth_level_1, $auth_level_1_reasons = null, TmnCrudUser $auth_level_2 = null, $auth_level_2_reasons = null, TmnCrudUser $auth_level_3 = null, $auth_level_3_reasons = null ) {
		
		//initiate the authorisation process and if it works store the id of the session authorisation process
		$this->authorisationProcessor	= new TmnAuthorisationProcessor($this->logfile);
		$submitarray = $this->authorisationProcessor->submit($auth_user, $auth_user_reasons, $auth_level_1, $auth_level_1_reasons, $auth_level_2, $auth_level_2_reasons, $auth_level_3, $auth_level_3_reasons, $this->getField('session_id'));
		//update the session row with the authsessionid
		$this->setField('auth_session_id', $submitarray['authsessionid']);
		$this->update();
		
		//returns an array of success and email
		return array('success' => $submitarray['success'], 'authsessionid' => $submitarray['authsessionid'], 'email' => $submitarray['useremailaddress']);
	}
	
	public function userIsAuthoriser(TmnCrudUser $user) {
		//make sure that the session has been authorised first
		if ($this->getField('auth_session_id') != null) {
			
			//if the authprocessor exists, create one
			if ($this->authorisationProcessor == null) {
				$this->authorisationProcessor = new TmnAuthorisationProcessor($this->logfile, $this->getField('auth_session_id'));
			}
			
			$this->authorisationProcessor->userIsAuthoriser($user);
			
		} else {
			throw new LightException(__CLASS__ . " Exception: Can't check if user is an Authoriser because the session has not been submitted.");;
		}
	}
	
	public function authorise(TmnCrudUser $user, $response) {
		if ($this->authorisationProcessor == null) {
			$this->authorisationProcessor = new TmnAuthorisationProcessor($this->logfile, $this->getField('auth_session_id'));
		}
		
		$this->authorisationProcessor->authorise($user, $response, $this->getField('session_id'));
	}
	
	/**
	 * Fetches the current authorisation progress of the session
	 * 
	 * @return an assoc array containing the current response (Yes, No, Pending) and a name (who is responsible for that response)
	 * 			ie {response:<authorisers response>, name: <authorisers full name>, date: <the data of this action>}
	 * 				if no auth processor found <authorisers response> = <authorisers full name> = <the data of this action> = ""
	 */
	public function getOverallProgress() {
		$authProcessor	= $this->getAuthorisationProcessor();
		
		if ($authProcessor != null) {
			return $authProcessor->getOverallProgress();
		} else {
			return array("response" => "", "name" => "", "date" => "");
		}
	}
	
	 /**
	  * Gets the details of the authoriser that matches the user passed to this function
	  * 
	  * @param TmnCrudUser $user		- the authoriser
	  * 
	  * @return an assoc array containing the current response (Yes, No, Pending)
	  * 		ie {response:<authorisers response>}
	  * 			if no auth processor found <authorisers response> = null
	  */
	public function getAuthoriserDetailsForUser(TmnCrudUser $user) {
		$authProcessor	= $this->getAuthorisationProcessor();
		
		if ($authProcessor != null) {
			return $authProcessor->getAuthoriserDetailsForUser($user);
		} else {
			return array("response" => "Pending", "reasons" => "[]", "total" => 0);
		}
	}
	
}

?>