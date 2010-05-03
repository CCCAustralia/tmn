<?php
include_once("mysqldriver.php");
include_once("logger.php");
include_once("FinancialProcessor.php");
require_once("../lib/FirePHPCore/fb.php");

class FinancialSubmitter extends FinancialProcessor {
	
	private $DEBUG;
	private $connection;
	private $logger;
	
	//TODO: put in DB
	//Member Care constants
	private $WORKERS_COMP_RATE = 0.015;
	private $CCCA_LEVY_RATE = 0.1;
	private $MIN_ADD_SUPER_RATE = 0.09;	//Minimum Additional CCCA Pre-tax Super - rate for Full MFB
	private $NET_STIPEND_MIN = 100;
	
	//Band values
	private $MULTIPLIER				=	1;
	private $BAND_FP_COUPLE			=	6000;
	private $BAND_FP_SINGLE			=	3600;
	private $BAND_TMN_COUPLE_MIN	=	3600;
	private $BAND_TMN_COUPLE_MAX	=	7200;
	private $BAND_TMN_SINGLE_MIN	=	2400;
	private $BAND_TMN_SINGLE_MAX	=	4100;
	
	//DATA ARRAY SETUP//
	private $data = array(
						firstname						=>	"__",
						s_firstname						=>	"__",
						surname							=>	"__",
						s_surname						=>	"__",
						date							=>	"__",
						s_date							=>	"__",
						fan								=>	"__",
						s_fan							=>	"__",
						
						ministry						=>	"__",
						s_ministry						=>	"__",
						
						ft_pt_os						=>	"__",
						s_ft_pt_os						=>	"__",
						days_per_wk						=>	"__",
						s_days_per_wk					=>	"__",
						
						stipend							=>	"__",
						s_stipend						=>	"__",
						housing_stipend					=>	"__",
						s_housing_stipend				=>	"__",
						net_stipend						=>	"__",
						s_net_stipend					=>	"__",
						tax								=>	"__",
						s_tax							=>	"__",
						additional_tax					=>	"__",
						s_additional_tax				=>	"__",
						post_tax_super					=>	"__",
						s_post_tax_super				=>	"__",
						
						taxable_income					=>	"__",
						s_taxable_income				=>	"__",
						pre_tax_super					=>	"__",
						s_pre_tax_super					=>	"__",
						housing							=>	"__",
						housing_frequency				=>	"__",
						additional_housing				=>	"__",
						additional_life_cover			=>	"__",
						s_additional_life_cover			=>	"__",
						income_protection_cover_source	=>	"__",
						s_income_protection_cover_source=>	"__",
						mfb								=>	"__",
						s_mfb							=>	"__",
						claimable_mfb					=>	"__",
						s_claimable_mfb					=>	"__",
						mfb_rate						=>	"__",
						s_mfb_rate						=>	"__",
						mmr								=>	"__",
						s_mmr							=>	"__",
						financial_package				=>	"__",
						s_financial_package				=>	"__",
						joint_financial_package			=>	"__",
						ministry_levy					=>	"__",
						s_ministry_levy					=>	"__",
						
						pre_tax_super					=>	"__",
						s_pre_tax_super					=>	"__",
						employer_super					=>	"__",
						s_employer_super				=>	"__",
						total_super						=>	"__",
						s_total_super					=>	"__",
						resc							=>	"__",
						s_resc							=>	"__",
						super_fund						=>	"__",
						s_super_fund					=>	"__",
						
						
						//joint data
						//-housing
						//-additional_housing
						//-joint_financial_package
						
						
						transfers						=>	"",
						total_transfers					=>	"",
						
						workers_comp					=>	"__",
						
						buffer							=>	"__",
						
						international_donations			=> 	0,
						ccca_levy						=>	"__",
						
						tmn								=>	"__",
						
						auth_lv1						=>	0,
						auth_lv2						=>	0,
						auth_lv2_reasons				=>  array(),
						auth_lv3						=>	0,
						auth_lv3_reasons				=>	array()
					);
	//END DATA ARRAY SETUP//
	
	
	//__construct:			This is the constructor and will initalise the object when created
	//params:				$findat		- an associative array that contains the financial data
	//						$dbug		- (number 0,1) tells the object if it should use debug mode or not
	//returns				n/a
	public function __construct($findat, $dbug) {
		parent::setFinancialData($findat);
		$this->DEBUG = $dbug;
		$this->connection = new MySqlDriver();
		$this->logger = new logger("logs/submit_fd.log");
		$this->logger->setDebug($this->DEBUG);
		if($this->DEBUG) fb("DEBUGGING MODE");
	}
	
	
	public function submit(){

		//Fetch names
		$sql = mysql_query("SELECT * FROM User_Profiles WHERE guid='".$this->financial_data['guid']."'");

		if (mysql_num_rows($sql) != 1) die('{"success":"false"}'); //can't be found in DB
		$row = mysql_fetch_assoc($sql);
		$s_sql = mysql_query("SELECT * FROM User_Profiles WHERE guid=(SELECT SPOUSE_GUID FROM User_Profiles WHERE guid='".$this->financial_data['guid']."')");
		if (mysql_num_rows($sql) != 1) {
			$this->financial_data['spouse'] = 0;
			$iscouple = 0;
		} else {
			$s_row = mysql_fetch_assoc($s_sql);
			if ($s_row['GUID'] == '') die('{"success":"false"}'); //not stored correctly in DB (can't be processed)
			$this->financial_data['spouse'] = $s_row['GUID'];
			$iscouple = 1;
		}

		//Main user
		//-from db(User_Profiles)
		$this->data['firstname']					=	$row['FIRSTNAME'];
		$this->data['surname']						=	$row['SURNAME'];
		
		//Spouse
		//-from db(User_Profiles)
		$this->data['s_firstname']					=	$s_row['FIRSTNAME'];
		$this->data['s_surname']					=	$s_row['SURNAME'];
		
		//Date
		//(date of last change i.e. now)
		$this->data['date']							=	date("d M Y");
		$this->data['s_date']						=	date("d M Y");
		
		//Financial Account Number
		//-from db(User_Profiles)
		$this->data['fan']							=	$row['FIN_ACC_NUM'];
		$this->data['s_fan']						=	$s_row['FIN_ACC_NUM'];
		
		//Ministry
		//-from db(User_Profiles)
		$this->data['ministry']						=	$row['MINISTRY'];
		$this->data['s_ministry']					=	$s_row['MINISTRY'];
		
		//Full-time/Part-time/Overseas
		//-from db(User_Profiles)
		$ftptos_sql = mysql_query("SELECT * FROM FT_PT_OS");
		for ($i = 0; $i < mysql_num_rows($ftptos_sql); $i++) {
			$ftptos_row = mysql_fetch_assoc($ftptos_sql);
			$ftptos_map[$ftptos_row['key']] = $ftptos_row['value'];
		}
		if($this->DEBUG) fb($ftptos_map);
		$this->data['ft_pt_os']						=	$ftptos_map[$row['FT_PT_OS']];
		$this->data['s_ft_pt_os']					=	$ftptos_map[$s_row['FT_PT_OS']];
		
		//Days per Week
		//-from db(User_Profiles)
		if (is_null($row['DAYS_PER_WEEK']) || $ftptos_map[$row['FT_PT_OS']] == "Full Time")
			$row['DAYS_PER_WEEK']					=	4;
		$this->data['days_per_wk']					=	$row['DAYS_PER_WEEK'] + 1;
		if (is_null($s_row['DAYS_PER_WEEK']) || $ftptos_map[$s_row['FT_PT_OS']] == "Full Time")
			$s_row['DAYS_PER_WEEK']					=	4;
		$this->data['s_days_per_wk']				=	$s_row['DAYS_PER_WEEK'] + 1;
		
		//Stipend
		//-from Form on Page
		$this->data['stipend']						=	$this->financial_data['STIPEND'];
		$this->data['s_stipend']					=	$this->financial_data['S_STIPEND'];
		
		//Housing
		$this->data['housing']						=	$this->financial_data['HOUSING'];
				
		//Additional Housing Allowance
		$this->financial_data['ADDITIONAL_HOUSING']	=	$this->getAdditionalHousing();
		$this->data['additional_housing']			=	$this->financial_data['ADDITIONAL_HOUSING'];
		
		//Stipend
		//-from Form on Page
		$this->data['housing_stipend']				=	$this->getHousingStipend();
		$this->data['s_housing_stipend']			=	0;
		
		//Net Stipend
		//-from Form on Page
		//NOTE: The old TMN refers to and displays this as Gross Stipend.
		//Either way, this value is as such: <Net/Gross Stipend> + <Additional Tax> + <Post-Tax Super> = Taxable Income
		$this->financial_data['NET_STIPEND']		=	$this->data['stipend'] + $this->data['housing_stipend'];
		$this->data['net_stipend']					=	$this->financial_data['NET_STIPEND'];
		$this->financial_data['S_NET_STIPEND']		=	$this->data['s_stipend'] + $this->data['s_housing_stipend'];
		$this->data['s_net_stipend']				=	$this->financial_data['S_NET_STIPEND'];
		
		//Additional Tax
		//-from Form on Page
		$this->data['additional_tax']				=	$this->financial_data['ADDITIONAL_TAX'];
		$this->data['s_additional_tax']				=	$this->financial_data['S_ADDITIONAL_TAX'];
		
		//Post-Tax Super (voluntary super contributions)
		//-from Form on Page
		$this->data['post_tax_super']				=	$this->financial_data['POST_TAX_SUPER'];
		$this->data['s_post_tax_super']				=	$this->financial_data['S_POST_TAX_SUPER'];
		
		//Taxable Income
		//-calculated using sum of (Net Stipend, Add. Tax, and Post-Tax Super)
		$this->financial_data['TAXABLE_INCOME']		=	$this->calculateTaxableIncome($this->data['net_stipend'] + $this->data['additional_tax'] + $this->data['post_tax_super']);
		$this->data['taxable_income']				=	$this->financial_data['TAXABLE_INCOME'];
		$this->financial_data['S_TAXABLE_INCOME']	=	$this->calculateTaxableIncome($this->data['s_net_stipend'] + $this->data['s_additional_tax'] + $this->data['s_post_tax_super']);
		$this->data['s_taxable_income']				=	$this->financial_data['S_TAXABLE_INCOME'];
		
		//Tax
		//-from Form on Page
		$this->financial_data['TAX']				=	$this->calculateTax($this->data['taxable_income'], 'resident');
		$this->data['tax']							=	$this->financial_data['TAX'];
		$this->financial_data['S_TAX']				=	$this->calculateTax($this->data['s_taxable_income'], 'resident');
		$this->data['s_tax']						=	$this->financial_data['S_TAX'];
		
		//Housing Frequency
		$this->data['housing_frequency']			=	($this->financial_data['HOUSING_FREQUENCY'] ? "Fortnightly" : "Monthly");
		
		//Additional Life Cover
		$this->data['additional_life_cover']		=	$this->financial_data['LIFE_COVER'];
		$this->data['s_additional_life_cover']		=	$this->financial_data['S_LIFE_COVER'];
		
		//INCOME_PROTECTION Cover Source
		//(index: 0=Support Account, 1=Super Fund)
		$this->data['income_protection_cover_source']	=	($this->financial_data['INCOME_PROTECTION_COVER_SOURCE'] ? "Super Fund" : "Support Account");
		$this->data['s_income_protection_cover_source']	=	($this->financial_data['S_INCOME_PROTECTION_COVER_SOURCE'] ? "Super Fund" : "Support Account");

		//MFB Rate
		switch ($this->financial_data['MFB_RATE']) {
			case 0:
				$this->data['mfb_rate'] = "Zero";
				break;
			case 1:
				$this->data['mfb_rate'] = "Half";
				break;
			case 2:
				$this->data['mfb_rate'] = "Full";
				break;
		}
		$mfb_rate = $this->getMfbRate($this->financial_data['MFB_RATE']);
		
		switch ($this->financial_data['S_MFB_RATE']) {
			case 0:
				$this->data['s_mfb_rate'] = "Zero";
				break;
			case 1:
				$this->data['s_mfb_rate'] = "Half";
				break;
			case 2:
				$this->data['s_mfb_rate'] = "Full";
				break;
		}
		$s_mfb_rate = $this->getMfbRate($this->financial_data['S_MFB_RATE']);
		
		//Ministry Fringe Benefits
		$this->financial_data['MAX_MFB']				=	$this->calculateMaxMFB($this->data['taxable_income'], $mfb_rate, $this->data['days_per_wk']);
		$this->data['mfb']								=	$this->financial_data['MAX_MFB'];
		$this->financial_data['S_MAX_MFB']				=	$this->calculateMaxMFB($this->data['s_taxable_income'], $mfb_rate, $this->data['s_days_per_wk']);
		$this->data['s_mfb']							=	$this->financial_data['S_MAX_MFB'];
		
		//Claimable Ministry Fringe Benefits
		$this->financial_data['CLAIMABLE_MFB']		=	$this->getClaimableMfb(0);//$this->financial_data['CLAIMABLE_MFB'];
		$this->data['claimable_mfb']				=	$this->financial_data['CLAIMABLE_MFB'];
		$this->financial_data['S_CLAIMABLE_MFB']	=	$this->getClaimableMfb(1);
		$this->data['s_claimable_mfb']				=	$this->financial_data['S_CLAIMABLE_MFB'];
		
		//Pre-Tax Super
		$min_pretax_super 							= 	round($mfb_rate * $this->MIN_ADD_SUPER_RATE * $this->data['taxable_income']);
		$this->financial_data['PRE_TAX_SUPER']		=	$this->getPreTaxSuper($mfbrate, 0);
		$this->data['pre_tax_super']				=	$this->financial_data['PRE_TAX_SUPER'];
		//Reportable Employer Super Contribution
		$this->financial_data['RESC']				=	round($this->data['pre_tax_super'] - $min_pretax_super);
		$this->data['resc']							=	$this->financial_data['RESC'];
		
		//Spouse Pre-Tax Super
		$s_min_pretax_super 						=	round($s_mfb_rate * $this->MIN_ADD_SUPER_RATE * $this->data['s_taxable_income']);
		$this->financial_data['S_PRE_TAX_SUPER']	=	$this->getPreTaxSuper($s_mfbrate, 1);
		$this->data['s_pre_tax_super']				=	$this->financial_data['S_PRE_TAX_SUPER'];
		//Reportable Employer Super Contribution
		$this->financial_data['S_RESC']				=	round($this->data['s_pre_tax_super'] - $s_min_pretax_super);
		$this->data['s_resc']						=	$this->financial_data['S_RESC'];

		//Financial Packages
		if (!$iscouple) {
			$this->financial_data['FINANCIAL_PACKAGE']			=	round($this->data['taxable_income'] + $this->data['mfb'] + $this->data['pre_tax_super'] + $this->data['additional_life_cover'] + $this->data['additional_housing'], ROUND_HALF_UP);
			$this->financial_data['S_FINANCIAL_PACKAGE']		=	0;
			$this->financial_data['JOINT_FINANCIAL_PACKAGE']	=	$this->financial_data['FINANCIAL_PACKAGE'];
		} else {
			$this->financial_data['FINANCIAL_PACKAGE']			=	round($this->data['taxable_income'] + $this->data['mfb'] + $this->data['pre_tax_super'] + $this->data['additional_life_cover'] + ($this->data['additional_housing'] * ($this->data['net_stipend'] / ($this->data['net_stipend'] + $this->data['s_net_stipend']))), ROUND_HALF_UP);
			$this->financial_data['S_FINANCIAL_PACKAGE']		=	round($this->data['s_taxable_income'] + $this->data['s_mfb'] + $this->data['s_pre_tax_super'] + $this->data['s_additional_life_cover'] + ($this->data['additional_housing'] * ($this->data['s_net_stipend'] / ($this->data['net_stipend'] + $this->data['s_net_stipend']))), ROUND_HALF_UP);
			$this->financial_data['JOINT_FINANCIAL_PACKAGE']	=	$this->financial_data['S_FINANCIAL_PACKAGE'] + $this->financial_data['S_FINANCIAL_PACKAGE'];
		}
		$this->data['financial_package']			=	$this->financial_data['FINANCIAL_PACKAGE'];
		$this->data['s_financial_package']			=	$this->financial_data['S_FINANCIAL_PACKAGE'];
		$this->data['joint_financial_package']		=	$this->financial_data['JOINT_FINANCIAL_PACKAGE'];
		
		//Employer Super
		$this->financial_data['EMPLOYER_SUPER']		=	$this->calculateEmployerSuper($this->data['taxable_income']);
		$this->data['employer_super']				=	$this->financial_data['EMPLOYER_SUPER'];
		$this->financial_data['S_EMPLOYER_SUPER']	=	$this->calculateEmployerSuper($this->data['s_taxable_income']);
		$this->data['s_employer_super']				=	$this->financial_data['S_EMPLOYER_SUPER'];
		
		//Total Super
		$this->financial_data['TOTAL_SUPER']		=	$this->data['post_tax_super'] + $this->data['pre_tax_super'] + $this->data['employer_super'];
		$this->data['total_super']					=	$this->financial_data['TOTAL_SUPER'];
		$this->financial_data['S_TOTAL_SUPER']		=	$this->data['s_post_tax_super'] + $this->data['s_pre_tax_super'] + $this->data['s_employer_super'];
		$this->data['s_total_super']				=	$this->financial_data['S_TOTAL_SUPER'];
		
		//Super fund choice
		//(index: 0=Other, 1=IOOF)
		$this->data['super_fund']					=	($this->financial_data['IOOF'] ? 'IOOF' : 'Other');
		$this->data['s_super_fund']					=	($this->financial_data['S_IOOF'] ? 'IOOF' : 'Other');
		
		//Monthly Ministry Reimbursements
		$this->data['mmr']							=	$this->financial_data['MMR'];
		$this->data['s_mmr']						=	$this->financial_data['S_MMR'];
		
		//Worker's Compensation
		$this->financial_data['WORKERS_COMP']		=	round($this->data['joint_financial_package'] * $this->WORKERS_COMP_RATE);
		$this->data['workers_comp']					=	$this->financial_data['WORKERS_COMP'];
		
		
		//Ministry Levy
		if ($iscouple) {
			//calc the amount that the levy should be applied to
			$subtotal = $this->data['employer_super'] + $this->data['s_employer_super'] + $this->data['joint_financial_package'] + $this->data['workers_comp'] + $this->data['mmr'] + $this->data['s_mmr'];
			//grab the levy percentage
			$ministry_row = mysql_fetch_assoc(mysql_query("SELECT * FROM Ministry WHERE MINISTRY_ID='".$this->data['ministry']."'"));
			$ministry_levy_rate = $ministry_row['MINISTRY_LEVY'];
			$s_ministry_row = mysql_fetch_assoc(mysql_query("SELECT * FROM Ministry WHERE MINISTRY_ID='".$this->data['s_ministry']."'"));
			$s_ministry_levy_rate = $s_ministry_row['MINISTRY_LEVY'];
			//calc levy (levy is in proportion to the days per week each works)
			$this->data['ministry_levy']				=	round((($this->data['days_per_wk']/($this->data['days_per_wk']+$this->data['s_days_per_wk'])) * ($ministry_levy_rate / 100) * $subtotal));
			$this->data['s_ministry_levy']				=	round((($this->data['days_per_wk']/($this->data['days_per_wk']+$this->data['s_days_per_wk'])) * ($s_ministry_levy_rate / 100) * $subtotal));
		
			//check if both in same ministry - combine the ministry levy rather than duplicate
			if ($this->data['ministry'] == $this->data['s_ministry']) {
				$this->data['ministry_levy'] += $this->data['s_ministry_levy'];
				$this->data['s_ministry_levy'] = 0;
			}
		} else {
			$subtotal = $this->data['employer_super'] + $this->data['joint_financial_package'] + $this->data['workers_comp'] + $this->data['mmr'];
			$ministry_row = mysql_fetch_assoc(mysql_query("SELECT * FROM Ministry WHERE MINISTRY_ID='".$this->data['ministry']."'"));
			$ministry_levy_rate = $ministry_row['MINISTRY_LEVY'];
			$this->data['ministry_levy']				=	round(($ministry_levy_rate / 100) * $subtotal);
		}

		if($this->DEBUG) fb($ministry_row);
		
		///////////////////TODO change this code for when multiple session is possible//////////////////////////////////////////////////////////
		//atm session is set to be guid but for internal transfers it needs to be FAN so $this->data['fan'] is used instead of $this->financial_data['session'] //
		
		//Internal Contribution Transfers
		$sql = mysql_query("SELECT TRANSFER_NAME,TRANSFER_AMOUNT FROM Internal_Transfers WHERE SESSION_ID='".$this->data['fan']."'"); //should refer to $this->financial_data['session'] but atm fan is needed so that old transfers can be viewed
		for ($i = 0; $i < mysql_num_rows($sql); $i++) {
			$transfers_row = mysql_fetch_assoc($sql);
			$transfer['name'] = $transfers_row['TRANSFER_NAME'];
			$transfer['amount'] = $transfers_row['TRANSFER_AMOUNT'];
			$transfers[$i] = $transfer;
		}
		
		$i = count($transfers);
		if ($this->data['ministry_levy'] == '__')
			$this->data['ministry_levy'] = 0;
		if ($this->data['s_ministry_levy'] == '__')
			$this->data['s_ministry_levy'] == 0;
		//adding the levy to the list of internal transfers
		if ($this->data['ministry_levy'] != 0 && $this->data['s_ministry_levy'] == 0) {	//if just user (no spouse) with levy
			$transfer['name'] = $this->data['ministry'];
			$transfer['amount'] = $this->data['ministry_levy'];
			$transfers[$i] = $transfer;
		}
		if ($iscouple) {
			if ($this->data['ministry_levy'] != 0 && $this->data['s_ministry_levy'] != 0 && $this->data['ministry'] == $this->data['ministry']) {	//if both spouses are in the same levy-ing ministry
				$transfer['name'] = $this->data['ministry'];
				$transfer['amount'] = $this->data['ministry_levy'] + $this->data['s_ministry_levy'];
				$transfers[$i] = $transfer;
			}
			if ($this->data['s_ministry_levy'] != 0 && $this->data['ministry_levy'] == 0) {	//if just spouse with levy
				$transfer['name'] = $this->data['s_ministry'];
				$transfer['amount'] = $this->data['s_ministry_levy'];
				$transfers[$i] = $transfer;
			}
		}
		
		//total transfers with minsitry levy
		if (count($transfers) > 0)
			foreach($transfers as $r)
			{
				$total_transfers += $r['amount'];
			}
		$this->data['transfers']					=	$transfers;
		$this->data['total_transfers']				=	(is_null($total_transfers) ? 0 : $total_transfers);
		
		//International Donations
		if($this->financial_data['INTERNATIONAL_DONATIONS'] < ($subtotal + $transfers_total))
			$this->data['international_donations']=	$this->financial_data['INTERNATIONAL_DONATIONS'];
		else
			$err .= "INTERNATIONAL_DONATIONS:\"This figure must be smaller than your TMN.\", ";
		
		//CCCA Levy								//this has been changed so its not just a percentage of the subtotal but a percentage of the whole TMN
		$this->data['ccca_levy']					=	round(($subtotal + $total_transfers - $this->data['international_donations']) * ($this->CCCA_LEVY_RATE/(1-$this->CCCA_LEVY_RATE)));
		
		//Total Monthly Needs
		$this->data['tmn']							=	round($subtotal + $total_transfers + $this->data['ccca_levy']);
		
		//Buffer Required
		$this->data['buffer']						=	$this->data['tmn'] * ($row['MPD'] ? 2 : 1.5);
		

		//Calculate days per week multiplier
		$this->MULTIPLIER = ($iscouple ?(($this->data['days_per_wk'] + $this->data['s_days_per_wk']) / 10) : ($this->data['days_per_wk'] / 5));
		//Apply multiplier to limits and bands
		$this->BAND_FP_COUPLE			=	$this->BAND_FP_COUPLE			*	$this->MULTIPLIER;
		$this->BAND_FP_SINGLE			=	$this->BAND_FP_SINGLE			*	$this->MULTIPLIER;
		$this->BAND_TMN_COUPLE_MIN		=	$this->BAND_TMN_COUPLE_MIN 		* 	$this->MULTIPLIER;
		$this->BAND_TMN_COUPLE_MAX		=	$this->BAND_TMN_COUPLE_MAX		*	$this->MULTIPLIER;
		$this->BAND_TMN_SINGLE_MIN		=	$this->BAND_TMN_SINGLE_MIN		*	$this->MULTIPLIER;
		$this->BAND_TMN_SINGLE_MAX		=	$this->BAND_TMN_SINGLE_MAX		*	$this->MULTIPLIER;
		
		
		//Financial Package Limits
		if ($iscouple) {
			if ($this->data['joint_financial_package'] > $this->BAND_FP_COUPLE) {
				$this->data['auth_lv1'] = 1;
				if ($this->data['joint_financial_package'] < ($this->BAND_FP_COUPLE * 1.1)) {
					$this->data['auth_lv2'] = 1;
					$this->data['auth_lv2_reasons'][count($this->data['auth_lv2_reasons'])] = array('reason' => 'Joint Financial Package is $'.(round($this->data['joint_financial_package'] - $this->BAND_FP_COUPLE)).' above the limit ($'.$this->BAND_FP_COUPLE.').');
				} else {
					$this->data['auth_lv3'] = 1;
					$this->data['auth_lv3_reasons'][count($this->data['auth_lv3_reasons'])] = array('reason' => 'Financial Package is more than 10% over the limit ($'.$this->BAND_FP_COUPLE.').<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;">Warning</span>, it is $'.(round($this->data['joint_financial_package'] - ($this->BAND_FP_COUPLE*1.1))).' above the 10% buffer ($'.($this->BAND_FP_COUPLE*1.1).'). <span style="color:red;">This is a Very High Joint Financial Package!</span>');
				}
			}
		}
		if (!$iscouple) {
			if ($this->data['joint_financial_package'] > $this->BAND_FP_SINGLE) {
				$this->data['auth_lv1'] = 1;
				if ($this->data['joint_financial_package'] < ($this->BAND_FP_SINGLE * 1.1)) {
					$this->data['auth_lv2'] = 1;
					$this->data['auth_lv2_reasons'][count($this->data['auth_lv2_reasons'])] = array('reason' => 'Financial Package is $'.(round($this->data['joint_financial_package'] - $this->BAND_FP_SINGLE)).' above the limit ($'.$this->BAND_FP_SINGLE.').');
				} else {
					$this->data['auth_lv3'] = 1;
					$this->data['auth_lv3_reasons'][count($this->data['auth_lv3_reasons'])] = array('reason' => 'Financial Package is more than 10% over the limit ($'.$this->BAND_FP_SINGLE.'). <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;">Warning</span>, it is $'.(round($this->data['joint_financial_package'] - ($this->BAND_FP_SINGLE*1.1))).' above the 10% buffer ($'.($this->BAND_FP_SINGLE*1.1).'). <span style="color:red;">This is a Very High Joint Financial Package!</span>');
				}
			}
		}
		if($this->DEBUG) fb($this->data);
		
		//TMN BANDS
		$this->data['auth_lv1'] = 1;
		if ($iscouple) {
			//check min bound
			if($this->data['tmn'] < $this->BAND_TMN_COUPLE_MIN) {
				//check min bound with 10% buffer
				if($this->data['tmn'] > ($this->BAND_TMN_COUPLE_MIN * 0.9)) {
					$this->data['auth_lv2'] = 1;
					$this->data['auth_lv2_reasons'][count($this->data['auth_lv2_reasons'])] = array('reason' => 'TMN is $'.(round($this->BAND_TMN_COUPLE_MIN - $this->data['tmn'])).' Under the Minimum ($'.$this->BAND_TMN_COUPLE_MIN.').');
				} else {
					$this->data['auth_lv3'] = 1;
					$this->data['auth_lv3_reasons'][count($this->data['auth_lv3_reasons'])] = array('reason' => 'TMN is more than 10% Under the Minimum ($'.$this->BAND_TMN_COUPLE_MIN.'). <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;">Warning</span>, it is $'.(round(($this->BAND_TMN_COUPLE_MIN*0.9) - $this->data['tmn'])).' Under the 10% buffer ($'.($this->BAND_TMN_COUPLE_MIN*0.9).'). <span style="color:red;">This is a Very Low TMN!</span>');
				}
			}
			//check max bound
			if($this->data['tmn'] > $this->BAND_TMN_COUPLE_MAX) {
				//check max bound with 10% buffer
				if ($this->data['tmn'] < ($this->BAND_TMN_COUPLE_MAX * 1.1)) {
					$this->data['auth_lv2'] = 1;
					$this->data['auth_lv2_reasons'][count($this->data['auth_lv2_reasons'])] = array('reason' => 'TMN is $'.(round($this->data['tmn'] - $this->BAND_TMN_COUPLE_MAX)).' Over the Maximum ($'.$this->BAND_TMN_COUPLE_MAX.').');
				} else {
					$this->data['auth_lv3'] = 1;
					$this->data['auth_lv3_reasons'][count($this->data['auth_lv3_reasons'])] = array('reason' => 'TMN is more than 10% Over the Maximum ($'.$this->BAND_TMN_COUPLE_MAX.'). <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;">Warning</span>, it is $'.(round($this->data['tmn'] - ($this->BAND_TMN_COUPLE_MAX*1.1))).' Over the 10% buffer ($'.($this->BAND_TMN_COUPLE_MAX*1.1).'). <span style="color:red;">This is a Very High TMN!</span>');
				}
			}
		}
		if (!$iscouple) {
			//check min bound
			if ($this->data['tmn'] < $this->BAND_TMN_SINGLE_MIN) {
				//check min bound with 10% buffer
				if($this->data['tmn'] > ($this->BAND_TMN_SINGLE_MIN * 0.9)) {
					$this->data['auth_lv2'] = 1;
					$this->data['auth_lv2_reasons'][count($this->data['auth_lv2_reasons'])] = array('reason' => 'TMN is $'.(round($this->BAND_TMN_SINGLE_MIN - $this->data['tmn'])).' Under the Minimum ($'.$this->BAND_TMN_SINGLE_MIN.').');
				} else {
					$this->data['auth_lv3'] = 1;
					$this->data['auth_lv3_reasons'][count($this->data['auth_lv3_reasons'])] = array('reason' => 'TMN is more than 10% Under the Minimum ($'.$this->BAND_TMN_SINGLE_MIN.'). <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;">Warning</span>, it is $'.(round(($this->BAND_TMN_SINGLE_MIN*0.9) - $this->data['tmn'])).' Under the 10% buffer ($'.($this->BAND_TMN_SINGLE_MIN*0.9).'). <span style="color:red;">This is a Very Low TMN!</span>');
				}
			}
			//check max bound
			if($this->data['tmn'] > $this->BAND_TMN_SINGLE_MAX) {
			//check max bound with 10% buffer
				if ($this->data['tmn'] < ($this->BAND_TMN_SINGLE_MAX * 1.1)) {
					$this->data['auth_lv2'] = 1;
					$this->data['auth_lv2_reasons'][count($this->data['auth_lv2_reasons'])] = array('reason' => 'TMN is $'.(round($this->data['tmn'] - $this->BAND_TMN_SINGLE_MAX)).' Over the Maximum ($'.$this->BAND_TMN_SINGLE_MAX.').');
				} else {
					$this->data['auth_lv3'] = 1;
					$this->data['auth_lv3_reasons'][count($this->data['auth_lv3_reasons'])] = array('reason' => 'TMN is more than 10% Over the Maximum ($'.$this->BAND_TMN_SINGLE_MAX.'). <br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:red;">Warning</span>, it is $'.(round($this->data['tmn'] - ($this->BAND_TMN_SINGLE_MAX*1.1))).' Over the 10% buffer ($'.($this->BAND_TMN_SINGLE_MAX*1.1).'). <span style="color:red;">This is a Very High TMN!</span>');
				}
			}
		}
		
		//ADDITIONAL HOUSING AUTH CHECK
		if ($this->data['additional_housing'] != 0) {
			$this->data['auth_lv1'] = 1;
			$this->data['auth_lv2'] = 1;
			$this->data['auth_lv2_reasons'][count($this->data['auth_lv2_reasons'])] = array('reason' => 'You have an Additional Housing Allowance.');
		}
		
		//MPD AUTH CHECK
		if ($row['MPD'] == 1) {
			$this->data['auth_lv1'] = 1;
		}

		//trim and wrap
		//$this->data['auth_lv2_reasons'] = '['.trim($this->data['auth_lv2_reasons'], ', ').']';
		//$this->data['auth_lv3_reasons'] = '['.trim($this->data['auth_lv3_reasons'], ', ').']';
		
		
		//remove spouse entries if s_firstname is null
		foreach ($this->data as $k=>$v) {
			if (is_null($this->data['s_firstname']) && substr($k, 0, 2) == 's_')
				unset($this->data[$k]);
		}
		
		//check that net stipend for both spouses is over $100
		if ($this->data['net_stipend'] < $this->NET_STIPEND_MIN)
			$err .= "NET_STIPEND:\"You cannot have a stipend less than $".$this->NET_STIPEND_MIN.".\", ";
		if ($this->data['s_net_stipend'] < $this->NET_STIPEND_MIN && $iscouple)
			$err .= "S_NET_STIPEND:\"You cannot have a stipend less than $".$this->NET_STIPEND_MIN.".\", ";
			
		//check that housing is less than total mfbs
		//if ($this->data['housing'] > ($this->data['mfb']+$this->data['s_mfb']))
		//	$err .= "HOUSING:\"You cannot have a housing amount greater than your total MFB\'s ($".($this->data['mfb']+$this->data['s_mfb']).").\", ";
		
		if ($err == '') {
			$result = array('success'=>'true');
			$result['tmn_data'] = $this->data;
			return json_encode($result);
			
			if($this->DEBUG) fb($this->financial_data);
			if($this->DEBUG) fb($this->data);
			
			//return '{success: true}';
		} else {
			return '{success: false, errors:{'.trim($err,", ").'} }'; //Return with errors
		}
	}
}

?>