<?php

include_once('../classes/TmnCrud.php');

//This is an example of how to subclass TmnCrud
class TmnCrudSession extends TmnCrud {
	
	public function __construct($logfile, $tablename=null, $primarykey=null, $privatetypes=null, $publictypes=null) {
		
		parent::__construct(
			$logfile,						//path of logfile
			"Tmn_Sessions",					//name of table
			"session_id",					//name of table's primary key
			array(							//an assoc array of private field names and there types
				'auth_session_id'						=>	"i",
				'fan'									=>	"i",
				'guid'									=>	"s"
			),
			array(							//an assoc array of public field names and there types
				'session_id'							=>	"i",
				'session_name'							=>	"s",
				'home_assignment_session_id'			=>	"i",
				'international_assignment_session_id'	=>	"i",
				'date_modified'							=>	"s",
				'os_assignment_start_date'				=>	"s",
				'os_assignment_end_date'				=>	"s",
				'os_resident_for_tax_purposes'			=>	"s",
				'net_stipend'							=>	"i",
				'tax'									=>	"i",
				'additional_tax'						=>	"i",
				'post_tax_super'						=>	"i",
				'taxable_income'						=>	"i",
				'pre_tax_super'							=>	"i",
				'additional_life_cover'					=>	"i",
				'mfb'									=>	"i",
				'additional_housing_allowance'			=>	"i",
				'os_overseas_housing_allowance'			=>	"i",
				'financial_package'						=>	"i",
				'employer_super'						=>	"i",
				'mmr'									=>	"i",
				'stipend'								=>	"i",
				'housing_stipend'						=>	"i",
				'housing_mfb'							=>	"i",
				'mfb_rate'								=>	"s",
				'claimable_mfb'							=>	"i",
				'total_super'							=>	"i",
				'resc'									=>	"i",
				'super_fund'							=>	"s",
				'income_protection_cover_source'		=>	"s",
				's_net_stipend'							=>	"i",
				's_tax'									=>	"i",
				's_additional_tax'						=>	"i",
				's_post_tax_super'						=>	"i",
				's_taxable_income'						=>	"i",
				's_pre_tax_super'						=>	"i",
				's_additional_life_cover'				=>	"i",
				's_mfb'									=>	"i",
				's_additional_housing_allowance'		=>	"i",
				's_os_overseas_housing_allowance'		=>	"i",
				's_financial_package'					=>	"i",
				's_employer_super'						=>	"i",
				's_mmr'									=>	"i",
				's_stipend'								=>	"i",
				's_housing_stipend'						=>	"i",
				's_housing_mfb'							=>	"i",
				's_mfb_rate'							=>	"s",
				's_claimable_mfb'						=>	"i",
				's_total_super'							=>	"i",
				's_resc'								=>	"i",
				's_super_fund'							=>	"s",
				's_income_protection_cover_source'		=>	"s",
				'joint_financial_package'				=>	"i",
				'total_transfers'						=>	"i",
				'workers_comp'							=>	"i",
				'ccca_levy'								=>	"i",
				'tmn'									=>	"i",
				'buffer'								=>	"i",
				'international_donations'				=>	"i",
				'additional_housing'					=>	"i",
				'monthly_housing'						=>	"i",
				'housing'								=>	"i",
				'housing_frequency'						=>	"s"
			)
		);
	}
	
	
	////////////////////////ACCESSOR FUNCTIONS////////////////////////////
	
	
	public function getSessionID() {
		return $this->getField('session_id');
	}
	
	public function setSessionID($session_id) {
		$this->setField('session_id', $session_id);
	}
	
	public function getHomeAssignmentID() {
		return $this->getField('home_assignment_session_id');
	}
	
	public function setHomeAssignmentID($session_id) {
		$this->setField('home_assignment_session_id', $session_id);
	}
	
	public function getInternationalAssignmentID() {
		return $this->getField('international_assignment_session_id');
	}
	
	public function setInternationalAssignmentID($session_id) {
		$this->setField('international_assignment_session_id', $session_id);
	}
}

?>