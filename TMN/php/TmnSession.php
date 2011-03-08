<?php

include_once('TmnUser.php');

class TmnSession extends TmnUser {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	private static $table_name = "`Tmn_Sessions`";
	protected $session_id;
	protected $session = array(
			home_assignment_session_id				=>	null,
			international_assignment_session_id		=>	null,
			date_modified							=>	null,
			os_assignment_start_date				=>	null,
			os_assignment_end_date					=>	null,
			os_resident_for_tax_purposes			=>	null,
			net_stipend								=>	null,
			tax										=>	null,
			additional_tax							=>	null,
			post_tax_super							=>	null,
			taxable_income							=>	null,
			pre_tax_super							=>	null,
			additional_life_cover					=>	null,
			mfb										=>	null,
			additional_housing_allowance			=>	null,
			os_overseas_housing_allowance			=>	null,
			financial_package						=>	null,
			employer_super							=>	null,
			mmr										=>	null,
			stipend									=>	null,
			housing_stipend							=>	null,
			housing_mfb								=>	null,
			mfb_rate								=>	null,
			claimable_mfb							=>	null,
			total_super								=>	null,
			resc									=>	null,
			super_fund								=>	null,
			income_protection_cover_source			=>	null,
			s_net_stipend							=>	null,
			s_tax									=>	null,
			s_additional_tax						=>	null,
			s_post_tax_super						=>	null,
			s_taxable_income						=>	null,
			s_pre_tax_super							=>	null,
			s_additional_life_cover					=>	null,
			s_mfb									=>	null,
			s_additional_housing_allowance			=>	null,
			s_os_overseas_housing_allowance			=>	null,
			s_financial_package						=>	null,
			s_employer_super						=>	null,
			s_mmr									=>	null,
			s_stipend								=>	null,
			s_housing_stipend						=>	null,
			s_housing_mfb							=>	null,
			s_mfb_rate								=>	null,
			s_claimable_mfb							=>	null,
			s_total_super							=>	null,
			s_resc									=>	null,
			s_super_fund							=>	null,
			s_income_protection_cover_source		=>	null,
			joint_financial_package					=>	null,
			total_transfers							=>	null,
			workers_comp							=>	null,
			ccca_levy								=>	null,
			tmn										=>	null,
			buffer									=>	null,
			international_donations					=>	null,
			additional_housing						=>	null,
			monthly_housing							=>	null,
			housing									=>	null,
			housing_frequency						=>	null
	);
	
		protected $session_type = array(
			home_assignment_session_id				=>	"i",
			international_assignment_session_id		=>	"i",
			date_modified							=>	"s",
			os_assignment_start_date				=>	"s",
			os_assignment_end_date					=>	"s",
			os_resident_for_tax_purposes			=>	"s",
			net_stipend								=>	"i",
			tax										=>	"i",
			additional_tax							=>	"i",
			post_tax_super							=>	"i",
			taxable_income							=>	"i",
			pre_tax_super							=>	"i",
			additional_life_cover					=>	"i",
			mfb										=>	"i",
			additional_housing_allowance			=>	"i",
			os_overseas_housing_allowance			=>	"i",
			financial_package						=>	"i",
			employer_super							=>	"i",
			mmr										=>	"i",
			stipend									=>	"i",
			housing_stipend							=>	"i",
			housing_mfb								=>	"i",
			mfb_rate								=>	"s",
			claimable_mfb							=>	"i",
			total_super								=>	"i",
			resc									=>	"i",
			super_fund								=>	"s",
			income_protection_cover_source			=>	"s",
			s_net_stipend							=>	"i",
			s_tax									=>	"i",
			s_additional_tax						=>	"i",
			s_post_tax_super						=>	"i",
			s_taxable_income						=>	"i",
			s_pre_tax_super							=>	"i",
			s_additional_life_cover					=>	"i",
			s_mfb									=>	"i",
			s_additional_housing_allowance			=>	"i",
			s_os_overseas_housing_allowance			=>	"i",
			s_financial_package						=>	"i",
			s_employer_super						=>	"i",
			s_mmr									=>	"i",
			s_stipend								=>	"i",
			s_housing_stipend						=>	"i",
			s_housing_mfb							=>	"i",
			s_mfb_rate								=>	"s",
			s_claimable_mfb							=>	"i",
			s_total_super							=>	"i",
			s_resc									=>	"i",
			s_super_fund							=>	"s",
			s_income_protection_cover_source		=>	"s",
			joint_financial_package					=>	"i",
			total_transfers							=>	"i",
			workers_comp							=>	"i",
			ccca_levy								=>	"i",
			tmn										=>	"i",
			buffer									=>	"i",
			international_donations					=>	"i",
			additional_housing						=>	"i",
			monthly_housing							=>	"i",
			housing									=>	"i",
			housing_frequency						=>	"s"
	);
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile, $session_id) {
		
		parent::__construct($logfile);
		
		$this->session_id	= $session_id;
		
		$this->retrieveSession();
	}
	
	
			///////////////////ACCESSOR FUNCTIONS/////////////////////
	
			
	public function getSessionID() {
		return $this->session_id;
	}
	
	public function setSessionID($session_id) {
		$this->session_id = $session_id;
	}
	
	public function resetSession() {
		foreach ($this->session as $key=>$value) {
			$this->session[$key] = null;
		}
	}
	
	
			///////////////////CRUD BY SESSION_ID/////////////////////
	
	
	private function create() {
		
		$sql		= "INSERT INTO " . $this->table_name . " VALUES ( , ?, , ?, ?, ?, , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,)";
		$types		= "is";
		$values		= array($this->getFan(),$this->getAuthGuid());
		$valueCount	= 2;
		
		foreach ($this->session as $key=>$value) {
			$values[$valueCount]	=	$this->session[$key];
			$types					.=	$this->session_type[$key];
			$valueCount++;
		}
		
		$this->session_id	= $this->preparedQuery($sql, $values, $types);
	}
	
	public function retrieve() {
		$sql				= "SELECT ";
		$types				= "i";
		$resultTypes		= "";
		$values				= array($this->session_id);
		$keys				= array();
		$keyCount			= 0;
		
		foreach ($this->session as $key=>$value) {
			$sql			.= "`" . strtoupper($this->session[$key]) . "`, ";
			$resultTypes	.= $this->session_type[$key];
			$keys[$keyCount] = $key; //store order of keys
			$keyCount++;
		}
		
		$sql				 = trim($sql, ", ");
		$sql				.= " FROM " . $this->table_name . " WHERE `SESSION_ID` = ?";
		
		$result				 = $this->preparedSelect($sql, $values, $types, $resultTypes); //do query
		
		for ($keyCount=0; $keyCount < count($result); $keyCount++) {
			$this->session[$keys[$keyCount]] = $result[$keyCount]; // put results into array using keys and the order they were requested
		}
	}
	
	public function update() {
		$sql				= "UPDATE " . $this->table_name . " SET ";
		$types				= "";
		$values				= array();
		$valueCount			= 0;
		
		foreach ($this->session as $key=>$value) {
			$sql					.= "`" . strtoupper($this->session[$key]) . "` = ?, ";
			$values[$valueCount]	 =	$this->session[$key];
			$types					.= $this->session_type[$key];
			$valueCount++;
		}
		
		$sql				 = trim($sql, ", ");
		$sql				.= " WHERE `SESSION_ID` = ?";
		$values[valueCount]	 = $this->session_id;
		$types				.= "i";
		
		$this->preparedQuery($sql, $values, $types);
	}
	
	private function delete() {
		$sql				= "DELETE FROM " . $this->table_name . " WHERE `SESSION_ID` = ?";
		$types				= "i";
		$values				= array($this->session_id);
		
		$this->preparedQuery($sql, $values, $types);
	}
	
	private function createSession() {
		
		$sessionStmt	= $this->newStmt();
		
		$sessionStmt->prepare("INSERT INTO " . $this->table_name . " VALUES ( , ?, , ?, ?, ?, , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,)");
		$sessionStmt->bind_param(
			'isiisssiiiiiiiiiiiiiiiisiiissiiiiiiiiiiiiiiiisiiissiiiiiiiiisi',
			$this->getFan(),
			$this->getAuthGuid(),
			$this->session['home_assignment_session_id'],
			$this->session['international_assignment_session_id'],
			$this->session['os_assignment_start_date'],
			$this->session['os_assignment_end_date'],
			$this->session['os_resident_for_tax_purposes'],
			$this->session['net_stipend'],
			$this->session['tax'],
			$this->session['additional_tax'],
			$this->session['post_tax_super'],
			$this->session['taxable_income'],
			$this->session['pre_tax_super'],
			$this->session['additional_life_cover'],
			$this->session['mfb'],
			$this->session['additional_housing_allowance'],
			$this->session['os_overseas_housing_allowance'],
			$this->session['financial_package'],
			$this->session['employer_super'],
			$this->session['mmr'],
			$this->session['stipend'],
			$this->session['housing_stipend'],
			$this->session['housing_mfb'],
			$this->session['mfb_rate'],
			$this->session['claimable_mfb'],
			$this->session['total_super'],
			$this->session['resc'],
			$this->session['super_fund'],
			$this->session['income_protection_cover_source'],
			$this->session['s_net_stipend'],
			$this->session['s_tax'],
			$this->session['s_additional_tax'],
			$this->session['s_post_tax_super'],
			$this->session['s_taxable_income'],
			$this->session['s_pre_tax_super'],
			$this->session['s_additional_life_cover'],
			$this->session['s_mfb'],
			$this->session['s_additional_housing_allowance'],
			$this->session['s_os_overseas_housing_allowance'],
			$this->session['s_financial_package'],
			$this->session['s_employer_super'],
			$this->session['s_mmr'],
			$this->session['s_stipend'],
			$this->session['s_housing_stipend'],
			$this->session['s_housing_mfb'],
			$this->session['s_mfb_rate'],
			$this->session['s_claimable_mfb'],
			$this->session['s_total_super'],
			$this->session['s_resc'],
			$this->session['s_super_fund'],
			$this->session['s_income_protection_cover_source'],
			$this->session['joint_financial_package'],
			$this->session['total_transfers'],
			$this->session['workers_comp'],
			$this->session['ccca_levy'],
			$this->session['tmn'],
			$this->session['buffer'],
			$this->session['additional_housing'],
			$this->session['monthly_housing'],
			$this->session['housing'],
			$this->session['housing_frequency'],
			$this->session_id
		);
		
		$sessionStmt->execute();
		//TODO: Fix this its not inserting properly
		$this->session_id = $sessionStmt->insert_id;
		
		$sessionStmt->close();
		
		return $this->session_id;
	}
	
	public function retrieveSession() {
		
		if ($this->session_id != null) {
			$sessionStmt	= $this->newStmt();
			$sessionStmt->prepare("SELECT `HOME_ASSIGNMENT_SESSION_ID`, `INTERNATIONAL_ASSIGNMENT_SESSION_ID`, `DATE_MODIFIED`, `OS_ASSIGNMENT_START_DATE`, `OS_ASSIGNMENT_END_DATE`, `OS_RESIDENT_FOR_TAX_PURPOSES`, `NET_STIPEND`, `TAX`, `ADDITIONAL_TAX`, `POST_TAX_SUPER`, `TAXABLE_INCOME`, `PRE_TAX_SUPER`, `ADDITIONAL_LIFE_COVER`, `MFB`, `ADDITIONAL_HOUSING_ALLOWANCE`, `OS_OVERSEAS_HOUSING_ALLOWANCE`, `FINANCIAL_PACKAGE`, `EMPLOYER_SUPER`, `MMR`, `STIPEND`, `HOUSING_STIPEND`, `HOUSING_MFB`, `MFB_RATE`, `CLAIMABLE_MFB`, `TOTAL_SUPER`, `RESC`, `SUPER_FUND`, `INCOME_PROTECTION_COVER_SOURCE`, `S_NET_STIPEND`, `S_TAX`, `S_ADDITIONAL_TAX`, `S_POST_TAX_SUPER`, `S_TAXABLE_INCOME`, `S_PRE_TAX_SUPER`, `S_ADDITIONAL_LIFE_COVER`, `S_MFB`, `S_ADDITIONAL_HOUSING_ALLOWANCE`, `S_OS_OVERSEAS_HOUSING_ALLOWANCE`, `S_FINANCIAL_PACKAGE`, `S_EMPLOYER_SUPER`, `S_MMR`, `S_STIPEND`, `S_HOUSING_STIPEND`, `S_HOUSING_MFB`, `S_MFB_RATE`, `S_CLAIMABLE_MFB`, `S_TOTAL_SUPER`, `S_RESC`, `S_SUPER_FUND`, `S_INCOME_PROTECTION_COVER_SOURCE`, `JOINT_FINANCIAL_PACKAGE`, `TOTAL_TRANSFERS`, `WORKERS_COMP`, `CCCA_LEVY`, `TMN`, `BUFFER`, `ADDITIONAL_HOUSING`, `MONTHLY_HOUSING`, `HOUSING`, `HOUSING_FREQUENCY` FROM " . $this->table_name . " WHERE `SESSION_ID` = ?");
			$sessionStmt->bind_param('i', $this->session_id);
			$sessionStmt->execute();
			
			$sessionStmt->bind_result(
				$this->session['home_assignment_session_id'],
				$this->session['international_assignment_session_id'],
				$this->session['date_modified'],
				$this->session['os_assignment_start_date'],
				$this->session['os_assignment_end_date'],
				$this->session['os_resident_for_tax_purposes'],
				$this->session['net_stipend'],
				$this->session['tax'],
				$this->session['additional_tax'],
				$this->session['post_tax_super'],
				$this->session['taxable_income'],
				$this->session['pre_tax_super'],
				$this->session['additional_life_cover'],
				$this->session['mfb'],
				$this->session['additional_housing_allowance'],
				$this->session['os_overseas_housing_allowance'],
				$this->session['financial_package'],
				$this->session['employer_super'],
				$this->session['mmr'],
				$this->session['stipend'],
				$this->session['housing_stipend'],
				$this->session['housing_mfb'],
				$this->session['mfb_rate'],
				$this->session['claimable_mfb'],
				$this->session['total_super'],
				$this->session['resc'],
				$this->session['super_fund'],
				$this->session['income_protection_cover_source'],
				$this->session['s_net_stipend'],
				$this->session['s_tax'],
				$this->session['s_additional_tax'],
				$this->session['s_post_tax_super'],
				$this->session['s_taxable_income'],
				$this->session['s_pre_tax_super'],
				$this->session['s_additional_life_cover'],
				$this->session['s_mfb'],
				$this->session['s_additional_housing_allowance'],
				$this->session['s_os_overseas_housing_allowance'],
				$this->session['s_financial_package'],
				$this->session['s_employer_super'],
				$this->session['s_mmr'],
				$this->session['s_stipend'],
				$this->session['s_housing_stipend'],
				$this->session['s_housing_mfb'],
				$this->session['s_mfb_rate'],
				$this->session['s_claimable_mfb'],
				$this->session['s_total_super'],
				$this->session['s_resc'],
				$this->session['s_super_fund'],
				$this->session['s_income_protection_cover_source'],
				$this->session['joint_financial_package'],
				$this->session['total_transfers'],
				$this->session['workers_comp'],
				$this->session['ccca_levy'],
				$this->session['tmn'],
				$this->session['buffer'],
				$this->session['additional_housing'],
				$this->session['monthly_housing'],
				$this->session['housing'],
				$this->session['housing_frequency']
			);
			
			$sessionStmt->fetch();
				
			if (mysqli_connect_errno()) {
				//$this->failWithMsg("Session Conflict: session_id = " . $this->session_id);
				$this->d("Database Error: " . mysqli_connect_errno());
				$this->resetSession();
			}
			
			$sessionStmt->close();
		}
	}
	
	public function updateSession() {
		
		$sessionStmt	= $this->newStmt();
		$sessionStmt->prepare("UPDATE " . $this->table_name . " SET `HOME_ASSIGNMENT_SESSION_ID`=?, `INTERNATIONAL_ASSIGNMENT_SESSION_ID`=?, `DATE_MODIFIED`=?, `OS_ASSIGNMENT_START_DATE`=?, `OS_ASSIGNMENT_END_DATE`=?, `OS_RESIDENT_FOR_TAX_PURPOSES`=?, `NET_STIPEND`=?, `TAX`=?, `ADDITIONAL_TAX`=?, `POST_TAX_SUPER`=?, `TAXABLE_INCOME`=?, `PRE_TAX_SUPER`=?, `ADDITIONAL_LIFE_COVER`=?, `MFB`=?, `ADDITIONAL_HOUSING_ALLOWANCE`=?, `OS_OVERSEAS_HOUSING_ALLOWANCE`=?, `FINANCIAL_PACKAGE`=?, `EMPLOYER_SUPER`=?, `MMR`=?, `STIPEND`=?, `HOUSING_STIPEND`=?, `HOUSING_MFB`=?, `MFB_RATE`=?, `CLAIMABLE_MFB`=?, `TOTAL_SUPER`=?, `RESC`=?, `SUPER_FUND`=?, `INCOME_PROTECTION_COVER_SOURCE`=?, `S_NET_STIPEND`=?, `S_TAX`=?, `S_ADDITIONAL_TAX`=?, `S_POST_TAX_SUPER`=?, `S_TAXABLE_INCOME`=?, `S_PRE_TAX_SUPER`=?, `S_ADDITIONAL_LIFE_COVER`=?, `S_MFB`=?, `S_ADDITIONAL_HOUSING_ALLOWANCE`=?, `S_OS_OVERSEAS_HOUSING_ALLOWANCE`=?, `S_FINANCIAL_PACKAGE`=?, `S_EMPLOYER_SUPER`=?, `S_MMR`=?, `S_STIPEND`=?, `S_HOUSING_STIPEND`=?, `S_HOUSING_MFB`=?, `S_MFB_RATE`=?, `S_CLAIMABLE_MFB`=?, `S_TOTAL_SUPER`=?, `S_RESC`=?, `S_SUPER_FUND`=?, `S_INCOME_PROTECTION_COVER_SOURCE`=?, `JOINT_FINANCIAL_PACKAGE`=?, `TOTAL_TRANSFERS`=?, `WORKERS_COMP`=?, `CCCA_LEVY`=?, `TMN`=?, `BUFFER`=?, `ADDITIONAL_HOUSING`=?, `MONTHLY_HOUSING`=?, `HOUSING`=?, `HOUSING_FREQUENCY`=? WHERE `SESSION_ID` = ?");
		$sessionStmt->bind_param(
			'iissssiiiiiiiiiiiiiiiisiiissiiiiiiiiiiiiiiiisiiissiiiiiiiiisi',
			$this->session['home_assignment_session_id'],
			$this->session['international_assignment_session_id'],
			$this->session['date_modified'],
			$this->session['os_assignment_start_date'],
			$this->session['os_assignment_end_date'],
			$this->session['os_resident_for_tax_purposes'],
			$this->session['net_stipend'],
			$this->session['tax'],
			$this->session['additional_tax'],
			$this->session['post_tax_super'],
			$this->session['taxable_income'],
			$this->session['pre_tax_super'],
			$this->session['additional_life_cover'],
			$this->session['mfb'],
			$this->session['additional_housing_allowance'],
			$this->session['os_overseas_housing_allowance'],
			$this->session['financial_package'],
			$this->session['employer_super'],
			$this->session['mmr'],
			$this->session['stipend'],
			$this->session['housing_stipend'],
			$this->session['housing_mfb'],
			$this->session['mfb_rate'],
			$this->session['claimable_mfb'],
			$this->session['total_super'],
			$this->session['resc'],
			$this->session['super_fund'],
			$this->session['income_protection_cover_source'],
			$this->session['s_net_stipend'],
			$this->session['s_tax'],
			$this->session['s_additional_tax'],
			$this->session['s_post_tax_super'],
			$this->session['s_taxable_income'],
			$this->session['s_pre_tax_super'],
			$this->session['s_additional_life_cover'],
			$this->session['s_mfb'],
			$this->session['s_additional_housing_allowance'],
			$this->session['s_os_overseas_housing_allowance'],
			$this->session['s_financial_package'],
			$this->session['s_employer_super'],
			$this->session['s_mmr'],
			$this->session['s_stipend'],
			$this->session['s_housing_stipend'],
			$this->session['s_housing_mfb'],
			$this->session['s_mfb_rate'],
			$this->session['s_claimable_mfb'],
			$this->session['s_total_super'],
			$this->session['s_resc'],
			$this->session['s_super_fund'],
			$this->session['s_income_protection_cover_source'],
			$this->session['joint_financial_package'],
			$this->session['total_transfers'],
			$this->session['workers_comp'],
			$this->session['ccca_levy'],
			$this->session['tmn'],
			$this->session['buffer'],
			$this->session['additional_housing'],
			$this->session['monthly_housing'],
			$this->session['housing'],
			$this->session['housing_frequency'],
			$this->session_id
		);
		
		$sessionStmt->execute();
		
		$sessionStmt->close();
	}
	
	public function deleteSession() {
		
		$sessionStmt	= $this->newStmt();
		$sessionStmt->prepare("DELETE FROM " . $this->table_name . " WHERE `SESSION_ID` = ?");
		$sessionStmt->bind_param('i', $this->session_id);
		
		$sessionStmt->execute();
		
		$sessionStmt->close();
	}
	
	
			///////////////////CRUD BY JSON/////////////////////
			
	
	public function loadSessionFromJson($jsonObj) {
		
		foreach ($this->session as $key=>$value) {
			if (isset($jsonObj[$key])) {
				$this->session[$key] = $jsonObj[$key];
			}
		}
		
	}
	
	public function createSessionFromJson($jsonObj) {
		$this->loadSessionFromJson($jsonObj);
		return $this->create();
	}
	
	public function updateSessionFromJson($jsonObj) {
		$this->loadSessionFromJson($jsonObj);
		$this->updateSession();
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>