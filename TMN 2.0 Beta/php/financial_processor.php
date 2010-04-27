<?php
include_once("mysqldriver.php");
include_once("logger.php");
include_once("./calc/calc_tax.php");
include_once("./calc/calc_mfbmax.php");
include_once("./calc/calc_employersuper.php");
include_once("./calc/calc_additionalhousing.php");
require_once("../lib/FirePHPCore/fb.php");

class finproc {
	//financial values
	private $STIPEND_MIN = 100;
	private $MIN_ADD_SUPER_RATE = 0.09;
	
	//personal details
	private $days_per_week = 0;
	
	private $financial_data;
	private $DEBUG;
	private $connection;
	private $logger;
	
	
	//__construct:			This is the constructor and will initalise the object when created
	//params:				$findat		- an associative array that contains the financial data
	//						$dbug		- (number 0,1) tells the object if it should use debug mode or not
	//returns				n/a
	public function __construct($findat, $dbug) {
		$this->financial_data = $findat;
		$this->DEBUG = $dbug;
		$this->connection = new MySqlDriver();
		$this->logger = new logger("logs/financial.log");
		$this->logger->setDebug($this->DEBUG);
		if($this->DEBUG) fb("DEBUGGING MODE");
	}
	
	
	//proc:				processes the financial data and returns the result as a json object
	//params:			n/a
	//returns			a string that describes a json object (will contain {"success": "true", "financial_data": ... } or {"success": "false", "err": ... }
	public function proc() {
	
		//Spouse Guid
		$this->financial_data['spouse'] = $this->getSpouseGuid();
		
		//Housing
		$this->financial_data['ADDITIONAL_HOUSING'] = $this->getAdditionalHousing();
		
		
		//Taxable Income Panel
		if (isset($this->financial_data['STIPEND'])){
			
			//Housing Stipend while debug will return 0
			$this->financial_data['HOUSING_STIPEND'] = $this->getHousingStipend();
		if ($this->DEBUG) fb($this->financial_data);
			//calc net stipend (stipend (money in your account) + housing stipend (extra stipend needed to cover housing amount)
			$this->financial_data['NET_STIPEND'] = $this->financial_data['STIPEND'] + $this->financial_data['HOUSING_STIPEND'];
			
			//check min stipend
			$err .= $this->validateStipend(0);//0 means STIPEND, 1 means S_STIPEND
			
			$annum = ($this->financial_data['NET_STIPEND'] * 12) + ($this->financial_data['POST_TAX_SUPER'] * 12) + ($this->financial_data['ADDITIONAL_TAX'] * 12);	//calculate yearly figure
			
			$this->financial_data['TAXABLE_INCOME'] = calculateTaxableIncome($annum);
			$this->financial_data['TAX'] = calculateTax($this->financial_data['TAXABLE_INCOME'], 'resident');
			$this->financial_data['EMPLOYER_SUPER'] = calculateEmployerSuper($this->financial_data['TAXABLE_INCOME']);
			
			$this->financial_data['TAXABLE_INCOME'] = round($this->financial_data['TAXABLE_INCOME'] / 12);
			$this->financial_data['TAX'] = round($this->financial_data['TAX'] / 12);
		    $this->financial_data['EMPLOYER_SUPER'] = round($this->financial_data['EMPLOYER_SUPER'] / 12);
		}
		
		//Maximum MFB & Pre-tax Super
		if (isset($this->financial_data['TAXABLE_INCOME'])) {
		
			//mfb rate
			$mfbrate = $this->getMfbRate($this->financial_data['MFB_RATE']);
		
			//Pre Tax Super (if its not set then set it to the min)
			$this->financial_data['PRE_TAX_SUPER'] = $this->getPreTaxSuper($mfbrate,0);//the 0 means return my value
			
			//Fetch Days Per Week
			$this->financial_data['DAYS_PER_WEEK'] = $this->getDaysPerWeek($this->financial_data['guid']);
			
			//calc max mfbs
			$this->financial_data['MAX_MFB'] = round(calculateMaxMFB($this->financial_data['TAXABLE_INCOME'], $mfbrate, $this->financial_data['DAYS_PER_WEEK']));
			
			//calc claimable mfbs (the mfbs that are left after your housing has been taken out)
			$this->financial_data['CLAIMABLE_MFB'] = $this->getClaimableMfb(0);//0 for my claimable mfb
		}
		
		
		//Spouse Taxable Income Panel
		if (isset($this->financial_data['S_STIPEND'])){
					//calc housing stipend (diff between housing and what your mfbs & additional housing allowance will cover)
					$this->financial_data['S_HOUSING_STIPEND'] = 0;
				
			//calc net stipend (stipend (money in your account) + housing stipend (extra stipend needed to cover housing amount)
			$this->financial_data['S_NET_STIPEND'] = $this->financial_data['S_STIPEND'] + $this->financial_data['S_HOUSING_STIPEND'];
			
			//check min stipend
			$err .= $this->validateStipend(1);//0 means STIPEND, 1 means S_STIPEND
			
			$s_annum = ($this->financial_data['S_NET_STIPEND'] * 12) + ($this->financial_data['S_POST_TAX_SUPER'] * 12) + ($this->financial_data['S_ADDITIONAL_TAX'] * 12);	//calculate yearly figure
			
			$this->financial_data['S_TAXABLE_INCOME'] = calculateTaxableIncome($s_annum);
			$this->financial_data['S_TAX'] = calculateTax($this->financial_data['S_TAXABLE_INCOME'], 'resident');
			$this->financial_data['S_EMPLOYER_SUPER'] = calculateEmployerSuper($this->financial_data['S_TAXABLE_INCOME']);
			
			$this->financial_data['S_TAXABLE_INCOME'] = round($this->financial_data['S_TAXABLE_INCOME'] / 12);
			$this->financial_data['S_TAX'] = round($this->financial_data['S_TAX'] / 12);
		    $this->financial_data['S_EMPLOYER_SUPER'] = round($this->financial_data['S_EMPLOYER_SUPER'] / 12);
		}
		
		//Spouse Maximum MFB && Pre Tax Super
		if (isset($this->financial_data['S_TAXABLE_INCOME'])) {
		
			//enumerate mfb rate
			$mfbrate = $this->getMfbRate($this->financial_data['S_MFB_RATE']);
			
			//Spouse Pre Tax Super (if its not set then set it to the min)
			$this->financial_data['S_PRE_TAX_SUPER'] = $this->getPreTaxSuper($mfbrate,1); //the 1 means return spouse value
			
			//Fetch the user's days per week
			$this->financial_data['S_DAYS_PER_WEEK'] = $this->getDaysPerWeek($this->financial_data['spouse']);
			
			$this->financial_data['S_MAX_MFB'] = round(calculateMaxMFB($this->financial_data['S_TAXABLE_INCOME'], $mfbrate, $this->financial_data['S_DAYS_PER_WEEK']));
			
			//calc claimable mfbs (the mfbs that are left after your housing has been taken out)
			$this->financial_data['S_CLAIMABLE_MFB'] = $this->getClaimableMfb(1);//1 for spouse claimable mfb
		}
		
		//if ($this->DEBUG) fb($this->financial_data);
		
		if ($err == '') {
		
			$result = array('success'=>'true');
			$result['financial_data'] = $this->financial_data;
			return json_encode($result);
		}
		else {
			return '{"success": false, "errors":{'.trim($err,", ").'} }';	//Return with errors
		}
	}
	
	
	//getAdditionalHousing:	Calculates and returns the additional housing allowance (the amount of housing above the max housing mfb, set by Memeber Care)
	//params:				n/a
	//returns				additional_housing (a number >= 0)
	public function getAdditionalHousing(){
		if (isset($this->financial_data['HOUSING'])){
		
			$maxhousingmfb = $this->getMaxHousingMfb();
			//set housing freq to the default if not set
			if (!isset($this->financial_data['HOUSING_FREQUENCY'])) $this->financial_data['HOUSING_FREQUENCY'] = 0;
			//make sure housing is monthly
			$monthly_housing = $this->getMonthlyHousing();
			//calc additional housing
			return round(max( 0, $this->financial_data['HOUSING'] - $maxhousingmfb ));
		}
		
		return 0;
	}
	
	
	//getClaimableMfb:	Calculates a persons claimable mfbs (the mfbs that are left after your housing has been taken out)
	//params:			$me_or_spouse	- (number 0 or 1) tells function wether you, 0, want your pretax super or the spouses, 1, pretax super
	//returns			claimable mfb (a number >= 0)
	public function getClaimableMfb($me_or_spouse){
		//changes wether it grabs your or spouse values (adds S_ prefix to keys)
		if ($me_or_spouse) $prefix = "S_"; else $prefix = "";
		
		if (isset($this->financial_data['MAX_MFB'])){
			if (isset($this->financial_data['HOUSING'])){
				//grab max housing mfb
				$max_housing_mfb = $this->getMaxHousingMfb();
				//calcs spouses claimable mfb if possible
				if ($me_or_spouse){
					if(isset($this->financial_data['S_MAX_MFB'])){
						//calcs spouse claimable mfb (take my mfb off housing first and if there is housing still to be coverd take it from spouse mfb)
						return max(0, $this->financial_data['S_MAX_MFB'] - max(0, min($this->financial_data['HOUSING'], $max_housing_mfb) - $this->financial_data['MAX_MFB']));
					} else {
						//if no spouse stipend so cant calc this value so return 0
						return 0;
					}
				}
				//calcs single claimable mfb otherwise
				return max(0, $this->financial_data['MAX_MFB'] - min($this->financial_data['HOUSING'], $max_housing_mfb));
			} else {
				//if no housing to take away, just return max mfb
				return $this->financial_data[$prefix.'MAX_MFB'];
			}
		}
		return 0;
	}
	
	//////////This function is now being used quite regularly, we may want to change it so it only pulls from DB if necissary/////////////
	
	//getDaysPerWeek:	Takes a guid and grabs that persons days per week from the database
	//params:			$guid		- (a 32 char string that identifies a user in the database)
	//returns			days_per_week (a number 0,1,2,3,4,5) (0 means not found in DB)
	public function getDaysPerWeek($guid){
		
		//if the value has not been grabed from the DB grab it (zero means not from DB)
		if ($this->days_per_week == 0){
			//Fetch the user's days per week
			$sql = mysql_query("SELECT DAYS_PER_WEEK, FT_PT_OS FROM User_Profiles WHERE guid='".$guid."'");
			if (mysql_num_rows($sql) == 1) {
				$row = mysql_fetch_assoc($sql);
				if ($row['FT_PT_OS'] == 0){
					$this->days_per_week = 5;
				} else {
					$this->days_per_week = $row['DAYS_PER_WEEK'] + 1; //+1 because days per week is stored as an index not a number
				}
			}
			$this->days_per_week = 0;//zero means not found in DB
		}
		
		return $this->days_per_week;
	}
	
	
	//getHousingStipend:	Calculates and returns the housing stipend (the amount of housing that is not covered by mfbs or
	//						additional housing allowance and needs to be covered by stipend)
	//params:				n/a
	//returns				housing_stipend (a number >= 0)
	public function getHousingStipend(){
		//calc housing stipend (diff between housing and what your mfbs & additional housing allowance will cover)
		if (isset($this->financial_data['HOUSING']) && isset($this->financial_data['STIPEND']) && isset($this->financial_data['ADDITIONAL_HOUSING']))
		{
			$monthly_housing = $this->getMonthlyHousing();
		
			if (isset($this->financial_data['S_STIPEND'])){
			if ($this->DEBUG) fb($this->financial_data);
			
				//calc lastest taxable income for me
				$annum = ($this->financial_data['STIPEND'] * 12) + ($this->financial_data['POST_TAX_SUPER'] * 12) + ($this->financial_data['ADDITIONAL_TAX'] * 12);	//calculate yearly figure
				$taxableincome = calculateTaxableIncome($annum) / 12;//adjust back to monthly from annual
				//calc lastest taxable income for spouse
				$s_annum = ($this->financial_data['S_STIPEND'] * 12) + ($this->financial_data['S_POST_TAX_SUPER'] * 12) + ($this->financial_data['S_ADDITIONAL_TAX'] * 12);	//calculate yearly figure
				$s_taxableincome = calculateTaxableIncome($s_annum) / 12;//adjust back to monthly from annual
				
				//mfb rate for me
				$mfbrate = $this->getMfbRate($this->financial_data['MFB_RATE']);
				//mfb rate for spouse
				$s_mfbrate = $this->getMfbRate($this->financial_data['S_MFB_RATE']);
				
				//calc lastest joint max mfbs
				$maxmfb = calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek($this->financial_data['guid']));
				$s_maxmfb = calculateMaxMFB($s_taxableincome, $s_mfbrate, $this->getDaysPerWeek($this->financial_data['spouse']));
				$joint_maxmfb = $maxmfb + $s_maxmfb;
			
				//calc housing stipend from lastest values
				$housing_stipend = max(0, $monthly_housing - $joint_maxmfb - $this->financial_data['ADDITIONAL_HOUSING']);
				//if there is a housing stipend it needs to be split between stipend and mfbs
				//we do this because as you increase stipend you increase mfbs and you decrease the housing stipend
				if ($housing_stipend > 0){
					//recalc taxable income and max mfb with the calc housing stipend
					$annum += ($housing_stipend * 12);//adjust back to annual from monthly
					$taxableincome = calculateTaxableIncome($annum) / 12;//adjust back to monthly from annual
					$maxmfb = calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek($this->financial_data['guid']));
					$joint_maxmfb = $maxmfb + $s_maxmfb;

					//max housing mfb
					$max_housing_mfb = $this->getMaxHousingMfb();

					//calculate the difference between the current mfbs and where the mfbs should be (if there is a housing stipend all the mfbs should be used on housing)
					$diff = $max_housing_mfb - $joint_maxmfb - $housing_stipend;

					//split diff between taxable income and mfbs and take taxable incomes portion of diff away. This will give the ideal taxable income.
					$ideal_taxable_income = ((1 - ($mfbrate*0.5)) * $diff) + $taxableincome;

					//calc the ideal tax from the ideal taxable income
					$ideal_tax = calculateTax($ideal_taxable_income * 12, 'resident') / 12;

					//calc what housing stipend should be by taking everything other than housing stipend from the ideal taxable income
					$housing_stipend = $ideal_taxable_income - $ideal_tax - $this->financial_data['ADDITIONAL_TAX'] - $this->financial_data['POST_TAX_SUPER'] - $this->financial_data['STIPEND'];
				}
				
				return round($housing_stipend);
			} else {
				//calc lastest taxable income
				$annum = ($this->financial_data['STIPEND'] * 12) + ($this->financial_data['POST_TAX_SUPER'] * 12) + ($this->financial_data['ADDITIONAL_TAX'] * 12);	//calculate yearly figure
				$taxableincome = calculateTaxableIncome($annum) / 12;//adjust back to monthly from annual
				
				//mfb rate
				$mfbrate = $this->getMfbRate($this->financial_data['MFB_RATE']);
				
				//calc lastest max mfbs
				$maxmfb = calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek($this->financial_data['guid']));
			
				//calc housing stipend from lastest values
				$housing_stipend = max(0, $monthly_housing - $maxmfb - $this->financial_data['ADDITIONAL_HOUSING']);
				//if there is a housing stipend it needs to be split between stipend and mfbs
				//we do this because as you increase stipend you increase mfbs and you decrease the housing stipend
				if ($housing_stipend > 0){
					//recalc taxable income and max mfb with the calc housing stipend
					$annum += ($housing_stipend * 12);//adjust back to annual from monthly
					$taxableincome = calculateTaxableIncome($annum) / 12;//adjust back to monthly from annual
					$maxmfb = calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek($this->financial_data['guid']));

					//TODO: put into DB
					$max_housing_mfb = $this->getMaxHousingMfb();

					//calculate the difference between the current mfbs and where the mfbs should be (if there is a housing stipend all the mfbs should be used on housing)
					$diff = $max_housing_mfb - $maxmfb - $housing_stipend;

					//split diff between taxable income and mfbs and take taxable incomes portion of diff away. This will give the ideal taxable income.
					$ideal_taxable_income = ((1 - ($mfbrate*0.5)) * $diff) + $taxableincome;

					//calc the ideal tax from the ideal taxable income
					$ideal_tax = calculateTax($ideal_taxable_income * 12, 'resident') / 12;

					//calc what housing stipend should be by taking everything other than housing stipend from the ideal taxable income
					$housing_stipend = $ideal_taxable_income - $ideal_tax - $this->financial_data['ADDITIONAL_TAX'] - $this->financial_data['POST_TAX_SUPER'] - $this->financial_data['STIPEND'];
				}
				
				return round($housing_stipend);
			}
		}
		
		return 0;
	}
	
	//////////////////////Should grab these numbers from the DB////////////////////////
	
	//getMaxHousingMfb:		It will return the max housing mfb based on if there is a spouse or not
	//						(this is the limit of how much of your mfbs will go toward housing)
	//params:				n/a
	//returns				max housing mfb (a number > 0)
	public function getMaxHousingMfb(){
		return $this->financial_data['spouse'] ? 1600 : 960;
	}
	
	
	//getMfbRate:		Takes an MFB_RATE index and returns the actual rate
	//params:			$MFB_RATE		- (number 0,1,2) give it the rate index
	//returns			mfbrate (a number 0-1)
	public function getMfbRate($MFB_RATE){
		//enumerate mfb rate
		switch ($MFB_RATE) {
			case 0:
			//Zero MFBs
				$mfbrate = 0;
				break;
			case 1:
			//Half MFBs
				$mfbrate = 0.5;
				break;
			case 2:
			//Full MFBs
				$mfbrate = 1;
				break;
			default:
			//Full MFBs
				$mfbrate = 1;
				break;
		}
		return $mfbrate;
	}
	
	
	//getMonthlyHousing:	Uses the housing frequency to return the housing amount as a monthly figure
	//params:				n/a
	//returns				monthly_housing (a number >= 0)
	public function getMonthlyHousing() {
		//convert fornightly housing to monthly housing
		if ($this->financial_data['HOUSING_FREQUENCY'] == 1)
			return round($this->financial_data['HOUSING'] * 26 / 12);
		else
			return $this->financial_data['HOUSING'];
	}
	
	
	//getPreTaxSuper:	Calculates a persons pretax super and returns it
	//params:			$mfbrate		- (a number 0-1) give it the rate to multiply it by
	//					$me_or_spouse	- (number 0 or 1) tells function wether you want your pretax super or the spouses pretax super
	//returns			pretax super (a number > 0)
	public function getPreTaxSuper($mfbrate, $me_or_spouse){
		//changes wether it grabs your or spouse values (adds S_ prefix to keys)
		if ($me_or_spouse){$prefix = "S_"; $little_prefix = "s_"; } else {$prefix = ""; $little_prefix = "";}
	
		//Pre Tax Super (if its not set then set it to the min)
		if ($this->financial_data[$little_prefix.'pre_tax_super_mode'] == 'auto'){
			return round($this->financial_data[$prefix.'TAXABLE_INCOME'] * $mfbrate * $this->MIN_ADD_SUPER_RATE);
		} else {
			$min_pre_tax_super = round($this->financial_data[$prefix.'TAXABLE_INCOME'] * $mfbrate * $this->MIN_ADD_SUPER_RATE);
			if (!isset($this->financial_data[$prefix.'PRE_TAX_SUPER']) || $this->financial_data[$prefix.'PRE_TAX_SUPER'] < $min_pre_tax_super){
				return $min_pre_tax_super;
			}
		}
		//if manual mode then return manually entered value
		return $this->financial_data[$prefix.'PRE_TAX_SUPER'];
	}
	
	
	//getSpouseGuid:		If there is a spouse it will return their guid (a 32 char string that identifies a user in the database)
	//params:				n/a
	//returns				guid (a 32 char string that identifies a user in the database) (0 means not found in DB)
	public function getSpouseGuid(){
		//if there is a spouse get the guid else return 0
		if ($this->financial_data['spouse']){
			//get the spouse's guid from the DB
			$sql = mysql_query("SELECT SPOUSE_GUID FROM User_Profiles WHERE guid='".$this->financial_data['guid']."'");
			if (mysql_num_rows($sql) == 1) {
				$row = mysql_fetch_assoc($sql);
				return $row['SPOUSE_GUID'];
			}
		}
		//if the spouse guid was not found return 0
		return 0;
	}
	
	
	//validateStipend:		Checks if stipend is valid, if not it returns an error message
	//params:				$me_or_spouse	- (number 0 or 1) tells function wether you want your values or your spouses values
	//returns				error message (a string, empty if valid)
	public function validateStipend($me_or_spouse){
		//changes wether it grabs your or spouse values (adds S_ prefix to keys)
		if ($me_or_spouse) $prefix = "S_"; else $prefix = "";
		
		//check min stipend
		if ($this->financial_data[$prefix.'NET_STIPEND'] < $this->STIPEND_MIN){
			if($this->financial_data[$prefix.'HOUSING_STIPEND'] > 0)
				return "\"".$prefix."NET_STIPEND\":\"Net Stipend is too low: must be at least $".$this->STIPEND_MIN.".\", ";
			else
				return "\"".$prefix."STIPEND\":\"Stipend is too low: must be at least $".$this->STIPEND_MIN.".\", ";
		}
		
		return "";
	}
}
?>