<?php

include_once('../interfaces/TmnCrudSessionInterface.php');

include_once('../classes/TmnCrud.php');
include_once('../classes/TmnCrudUser.php');
include_once('../classes/TmnAuthorisationProcessor.php');

//This is an example of how to subclass TmnCrud
class TmnCrudSession extends TmnCrud implements TmnCrudSessionInterface {
	
	private $owner					=	null;
	private $authorisatoionProcessor	=	null;
	
	public function __construct($logfile, $tablename=null, $primarykey=null, $privatetypes=null, $publictypes=null) {
		
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
	
	
	public function getOwner() {
		//if a guid is set
		if ($this->getField('guid') != null) {
			
			//if the user object hasn't been made from the guid then create it
			if ($this->owner == null) {
				$this->owner = TmnCrudUser::make($this->logfile, $this->getField('guid'));
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
	
	public function getOwnerGuid() {
		return $this->getField('guid');
	}
	
	public function setOwnerGuid($guid) {
		
			//if an owner already exists then load the user from the Database
		if ($this->owner != null) {
			$this->owner->setGuid($guid);
		} else {
			//if the owner object doesn't exist then make it
			$this->owner = TmnCrudUser::make($this->logfile, $guid);
		}
		
		//if the owner creation/switching worked without throwing an exception then update the guid field
		$this->setField('guid', $guid);
		$this->setField('fan', $this->owner->getFan());
	}
	
	
			///////////////////////AUTHORISATION METHODS////////////////////////
			
	
	
	public function submit( TmnCrudUser $user, TmnCrudUser $level1Authoriser, TmnCrudUser $level2Authoriser, TmnCrudUser $level3Authoriser,  $data ) {
		
		//load data into the session object
		$this->loadDataFromAssocArray($data);
		$this->setOwner($user);
		
		//initiate the authorisation process and if it works store the id of the session authorisation process
		$this->authorisatoionProcessor	= new TmnAuthorisationProcessor($this->logfile);
		$ap_id = $this->authorisatoionProcessor->submit($user, $level1Authoriser, $level2Authoriser, $level3Authoriser);
		$this->setField('auth_session_id', $ap_id);
		
		//if initiating the authorisation process worked with out throwing an exception put the data from the object into the database
		if ($this->getField('session_id') == null) {
			$this->create();
		} else {
			$this->update();
		}
		
		return true;
	}
	
	public function userIsAuthoriser(TmnCrudUser $user) {
		//make sure that the session has been authorised first
		if ($this->getField('auth_session_id') != null) {
			
			//if the 
			if ($this->authorisationProcessor == null) {
				$this->authorisationProcessor = TmnAuthorisationProcessor::make($this->logfile, $this->getField('auth_session_id'));
			}
			
			$this->authorisationProcessor->userIsAuthoriser($user);
			
		} else {
			throw new LightException(__CLASS__ . " Exception: Can't check if user is an Authoriser because the session has not been submitted.");;
		}
	}
	
	public function authorise($level, $response) {
		$this->authorisationProcessor->authorise($level, $response);
	}
	
}

?>