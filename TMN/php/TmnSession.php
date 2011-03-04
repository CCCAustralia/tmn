<?php

include_once('TmnUser.php');

class TmnUser extends TmnUser {
	
	
			///////////////////INSTANCE VARIABLES/////////////////////
	
	
	protected $home_assignment_session_id;
	protected $international_assignment_session_id;
	protected $date_modified;
	protected $os_assignment_start_date;
	protected $os_assignment_end_date;
	protected $os_resident_for_tax_purposes;
	protected $net_stipend;
	protected $tax;
	protected $additional_tax;
	protected $post_tax_super;
	protected $taxable_income;
	protected $pre_tax_super;
	protected $additional_life_cover;
	protected $mfb;
	protected $additional_housing_allowance;
	protected $os_overseas_housing_allowance;
	protected $financial_package;
	protected $employer_super;
	protected $mmr;
	protected $stipend;
	protected $housing_stipend;
	protected $housing_mfb;
	protected $mfb_rate;
	protected $claimable_mfb;
	protected $total_super;
	protected $resc;
	protected $super_fund;
	protected $income_protection_cover_source;
	protected $s_net_stipend;
	protected $s_tax;
	protected $s_additional_tax;
	protected $s_post_tax_super;
	protected $s_taxable_income;
	protected $s_pre_tax_super;
	protected $s_additional_life_cover;
	protected $s_mfb;
	protected $s_additional_housing_allowance;
	protected $s_os_overseas_housing_allowance;
	protected $s_financial_package;
	protected $s_employer_super;
	protected $s_mmr;
	protected $s_stipend;
	protected $s_housing_stipend;
	protected $s_housing_mfb;
	protected $s_mfb_rate;
	protected $s_claimable_mfb;
	protected $s_total_super;
	protected $s_resc;
	protected $s_super_fund;
	protected $s_income_protection_cover_source;
	protected $joint_financial_package;
	protected $total_transfers;
	protected $workers_comp;
	protected $ccca_levy;
	protected $tmn;
	protected $buffer;
	protected $additional_housing;
	protected $monthly_housing;
	protected $housing;
	protected $housing_frequency;
	
	
			///////////////////CONSTRUCTOR/////////////////////
	
	
	public function __construct($logfile, $session_id) {
		
		parent::__construct($logfile);
		
		$this->loadSessionWithID($session_id);
	}
	
	
			///////////////////CRUD BY SESSION_ID/////////////////////
	
	
	public function loadSessionWithID($session_id) {
		
		if ($session_id != null) {
			$userStmt	= $this->newStmt();
			$userStmt->prepare("SELECT `HOME_ASSIGNMENT_SESSION_ID`, `INTERNATIONAL_ASSIGNMENT_SESSION_ID`, `DATE_MODIFIED`, `OS_ASSIGNMENT_START_DATE`, `OS_ASSIGNMENT_END_DATE`, `OS_RESIDENT_FOR_TAX_PURPOSES`, `NET_STIPEND`, `TAX`, `ADDITIONAL_TAX`, `POST_TAX_SUPER`, `TAXABLE_INCOME`, `PRE_TAX_SUPER`, `ADDITIONAL_LIFE_COVER`, `MFB`, `ADDITIONAL_HOUSING_ALLOWANCE`, `OS_OVERSEAS_HOUSING_ALLOWANCE`, `FINANCIAL_PACKAGE`, `EMPLOYER_SUPER`, `MMR`, `STIPEND`, `HOUSING_STIPEND`, `HOUSING_MFB`, `MFB_RATE`, `CLAIMABLE_MFB`, `TOTAL_SUPER`, `RESC`, `SUPER_FUND`, `INCOME_PROTECTION_COVER_SOURCE`, `S_NET_STIPEND`, `S_TAX`, `S_ADDITIONAL_TAX`, `S_POST_TAX_SUPER`, `S_TAXABLE_INCOME`, `S_PRE_TAX_SUPER`, `S_ADDITIONAL_LIFE_COVER`, `S_MFB`, `S_ADDITIONAL_HOUSING_ALLOWANCE`, `S_OS_OVERSEAS_HOUSING_ALLOWANCE`, `S_FINANCIAL_PACKAGE`, `S_EMPLOYER_SUPER`, `S_MMR`, `S_STIPEND`, `S_HOUSING_STIPEND`, `S_HOUSING_MFB`, `S_MFB_RATE`, `S_CLAIMABLE_MFB`, `S_TOTAL_SUPER`, `S_RESC`, `S_SUPER_FUND`, `S_INCOME_PROTECTION_COVER_SOURCE`, `JOINT_FINANCIAL_PACKAGE`, `TOTAL_TRANSFERS`, `WORKERS_COMP`, `CCCA_LEVY`, `TMN`, `BUFFER`, `ADDITIONAL_HOUSING`, `MONTHLY_HOUSING`, `HOUSING`, `HOUSING_FREQUENCY` FROM `Tmn_Sessions` WHERE `SESSION_ID` = ?");
			$userStmt->bind_param('i', $session_id);
			$userStmt->execute();
			
			if ($userStmt->num_rows == 1) {
				
				$userStmt->bind_result(
					$this->home_assignment_session_id,
					$this->international_assignment_session_id,
					$this->date_modified,
					$this->os_assignment_start_date,
					$this->os_assignment_end_date,
					$this->os_resident_for_tax_purposes,
					$this->net_stipend,
					$this->tax,
					$this->additional_tax,
					$this->post_tax_super,
					$this->taxable_income,
					$this->pre_tax_super,
					$this->additional_life_cover,
					$this->mfb,
					$this->additional_housing_allowance,
					$this->os_overseas_housing_allowance,
					$this->financial_package,
					$this->employer_super,
					$this->mmr,
					$this->stipend,
					$this->housing_stipend,
					$this->housing_mfb,
					$this->mfb_rate,
					$this->claimable_mfb,
					$this->total_super,
					$this->resc,
					$this->super_fund,
					$this->income_protection_cover_source,
					$this->s_net_stipend,
					$this->s_tax,
					$this->s_additional_tax,
					$this->s_post_tax_super,
					$this->s_taxable_income,
					$this->s_pre_tax_super,
					$this->s_additional_life_cover,
					$this->s_mfb,
					$this->s_additional_housing_allowance,
					$this->s_os_overseas_housing_allowance,
					$this->s_financial_package,
					$this->s_employer_super,
					$this->s_mmr,
					$this->s_stipend,
					$this->s_housing_stipend,
					$this->s_housing_mfb,
					$this->s_mfb_rate,
					$this->s_claimable_mfb,
					$this->s_total_super,
					$this->s_resc,
					$this->s_super_fund,
					$this->s_income_protection_cover_source,
					$this->joint_financial_package,
					$this->total_transfers,
					$this->workers_comp,
					$this->ccca_levy,
					$this->tmn,
					$this->buffer,
					$this->additional_housing,
					$this->monthly_housing,
					$this->housing,
					$this->housing_frequency
				);
				
				$userStmt->fetch();
			
			} else {
				$this->failWithMsg("Session Conflict: session_id = " . $session_id);
			}
			
			$userStmt->close();
		}
	}
	
	public function updateSessionWithID($session_id) {
		
		$userStmt	= $this->newStmt();
		$userStmt->prepare("UPDATE `Tmn_Sessions` SET `HOME_ASSIGNMENT_SESSION_ID`=?, `INTERNATIONAL_ASSIGNMENT_SESSION_ID`=?, `DATE_MODIFIED`=?, `OS_ASSIGNMENT_START_DATE`=?, `OS_ASSIGNMENT_END_DATE`=?, `OS_RESIDENT_FOR_TAX_PURPOSES`=?, `NET_STIPEND`=?, `TAX`=?, `ADDITIONAL_TAX`=?, `POST_TAX_SUPER`=?, `TAXABLE_INCOME`=?, `PRE_TAX_SUPER`=?, `ADDITIONAL_LIFE_COVER`=?, `MFB`=?, `ADDITIONAL_HOUSING_ALLOWANCE`=?, `OS_OVERSEAS_HOUSING_ALLOWANCE`=?, `FINANCIAL_PACKAGE`=?, `EMPLOYER_SUPER`=?, `MMR`=?, `STIPEND`=?, `HOUSING_STIPEND`=?, `HOUSING_MFB`=?, `MFB_RATE`=?, `CLAIMABLE_MFB`=?, `TOTAL_SUPER`=?, `RESC`=?, `SUPER_FUND`=?, `INCOME_PROTECTION_COVER_SOURCE`=?, `S_NET_STIPEND`=?, `S_TAX`=?, `S_ADDITIONAL_TAX`=?, `S_POST_TAX_SUPER`=?, `S_TAXABLE_INCOME`=?, `S_PRE_TAX_SUPER`=?, `S_ADDITIONAL_LIFE_COVER`=?, `S_MFB`=?, `S_ADDITIONAL_HOUSING_ALLOWANCE`=?, `S_OS_OVERSEAS_HOUSING_ALLOWANCE`=?, `S_FINANCIAL_PACKAGE`=?, `S_EMPLOYER_SUPER`=?, `S_MMR`=?, `S_STIPEND`=?, `S_HOUSING_STIPEND`=?, `S_HOUSING_MFB`=?, `S_MFB_RATE`=?, `S_CLAIMABLE_MFB`=?, `S_TOTAL_SUPER`=?, `S_RESC`=?, `S_SUPER_FUND`=?, `S_INCOME_PROTECTION_COVER_SOURCE`=?, `JOINT_FINANCIAL_PACKAGE`=?, `TOTAL_TRANSFERS`=?, `WORKERS_COMP`=?, `CCCA_LEVY`=?, `TMN`=?, `BUFFER`=?, `ADDITIONAL_HOUSING`=?, `MONTHLY_HOUSING`=?, `HOUSING`=?, `HOUSING_FREQUENCY`=? WHERE `SESSION_ID` = ?");
		$userStmt->bind_param(
			'iissssiiiiiiiiiiiiiiiisiiissiiiiiiiiiiiiiiiisiiissiiiiiiiiisi',
			$this->home_assignment_session_id,
			$this->international_assignment_session_id,
			$this->date_modified,
			$this->os_assignment_start_date,
			$this->os_assignment_end_date,
			$this->os_resident_for_tax_purposes,
			$this->net_stipend,
			$this->tax,
			$this->additional_tax,
			$this->post_tax_super,
			$this->taxable_income,
			$this->pre_tax_super,
			$this->additional_life_cover,
			$this->mfb,
			$this->additional_housing_allowance,
			$this->os_overseas_housing_allowance,
			$this->financial_package,
			$this->employer_super,
			$this->mmr,
			$this->stipend,
			$this->housing_stipend,
			$this->housing_mfb,
			$this->mfb_rate,
			$this->claimable_mfb,
			$this->total_super,
			$this->resc,
			$this->super_fund,
			$this->income_protection_cover_source,
			$this->s_net_stipend,
			$this->s_tax,
			$this->s_additional_tax,
			$this->s_post_tax_super,
			$this->s_taxable_income,
			$this->s_pre_tax_super,
			$this->s_additional_life_cover,
			$this->s_mfb,
			$this->s_additional_housing_allowance,
			$this->s_os_overseas_housing_allowance,
			$this->s_financial_package,
			$this->s_employer_super,
			$this->s_mmr,
			$this->s_stipend,
			$this->s_housing_stipend,
			$this->s_housing_mfb,
			$this->s_mfb_rate,
			$this->s_claimable_mfb,
			$this->s_total_super,
			$this->s_resc,
			$this->s_super_fund,
			$this->s_income_protection_cover_source,
			$this->joint_financial_package,
			$this->total_transfers,
			$this->workers_comp,
			$this->ccca_levy,
			$this->tmn,
			$this->buffer,
			$this->additional_housing,
			$this->monthly_housing,
			$this->housing,
			$this->housing_frequency,
			$session_id
		);
		
		$userStmt->execute();
		
		$userStmt->close();
	}
	
	public function deleteSessionWithID($session_id) {
		
		$userStmt	= $this->newStmt();
		$userStmt->prepare("DELETE FROM `Tmn_Sessions` WHERE `SESSION_ID` = ?");
		$userStmt->bind_param('i', $session_id);
		
		$userStmt->execute();
		
		$userStmt->close();
	}
	
	
			///////////////////CRUD BY JSON/////////////////////
			
	
	public function loadSessionFromJson($packet) {
		
		//fill instance variables from $packet
	}
	
	public function updateSessionFromJson($session_id, $packet) {
		$this->loadSessionFromJson($packet);
		$this->updateSessionWithID($session_id);
	}
	
	public function createSessionFromJson($packet) {
		
		$userStmt	= $this->newStmt();
		//change to insert statement
		//$userStmt->prepare("");
		//$userStmt->bind_param();
		
		$userStmt->execute();
		
		$userStmt->close();
	}
	
	
			///////////////////DECONSTRUCTOR/////////////////////
	
	
	public function __destruct() {
		parent::__destruct();
	}
	
}

?>