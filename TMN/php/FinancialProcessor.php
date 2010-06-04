<?php
include_once("mysqldriver.php");
include_once("logger.php");
include_once("../lib/FirePHPCore/fb.php");

//Authenticate the user in GCX with phpCAS
include_once('../lib/cas/cas.php');		//include the CAS module
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('{success: false}');

class FinancialProcessor {
	//TODO: grab these values from DB
	//financial values
	private $STIPEND_MIN		=	100;
	private $MIN_SUPER_RATE		= 	0.09; 	//for employer super
	private $MIN_ADD_SUPER_RATE	=	0.09; 	//for pre-tax super
	private $OS_STIPEND_MAX		=	850;	//overseas maximum stipend (surplus is LAFHA)
	
	//tax values
	//formula and values grabbed from:
	//Statement of formulas for calculating amounts to be withheld
	
	//Scale 7 (Where payee not eligible to receive leave loading and has claimed tax-free threshold)
	private $x_resident = array(
					198,
					342,
					402,
					576,
					673,
					1225,
					1538,
					3461,
					PHP_INT_MAX //this is the highest number possible
				);
				
	private $a_resident = array(
					0.000,
					0.150,
					0.250,
					0.165,
					0.185,
					0.335,
					0.315,
					0.395,
					0.465
				);
				
	private $b_resident = array(
					0.0000,
					29.7115,
					63.9308,
					29.7117,
					41.2502,
					142.2117,
					117.6925,
					240.7694,
					483.0771
				);
				
	//Scale 3 (Foreign Residents)
	private $x_non_resident = array(
					673,
					1538,
					3461,
					PHP_INT_MAX	//this is the highest number possible
				);
				
	private $a_non_resident = array(
					0.2900,
					0.3000,
					0.3800,
					0.4500
				);
	
	private $b_non_resident = array(
					0.2900,
					6.7308,
					129.8077,
					372.1154
				);
	
	//personal details
	private $guid;
	private $spouse;
				
	private $days_per_week = 0;
	private $s_days_per_week = 0;
	
	public $financial_data;
	private $DEBUG;
	private $connection;
	private $logger;
	
	
	//__construct:			This is the constructor and will initalise the object when created
	//params:				$findat		- an associative array that contains the financial data
	//						$dbug		- (number 0,1) tells the object if it should use debug mode or not
	//returns				n/a
	public function __construct($findat, $dbug) {
		$this->financial_data = $findat;
			//grab guid
		if (isset($_SESSION['phpCAS'])) {
			$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
			$xmlobject = new SimpleXmlElement($xmlstr);
			$this->guid = $xmlobject->authenticationSuccess->attributes->ssoGuid;
		}
		$this->spouse = $this->financial_data['spouse'];
		$this->DEBUG = $dbug;
		$this->connection = new MySqlDriver();
		$this->logger = new logger("logs/financial.log");
		$this->logger->setDebug($this->DEBUG);
		if($this->DEBUG) fb("DEBUGGING MODE");		
		//choose the appropriate set of tax figures
		if ($this->financial_data['OS_RESIDENT_FOR_TAX_PURPOSES']) {
			$this->x = $this->x_resident;
			$this->a = $this->a_resident;
			$this->b = $this->b_resident;
		} else {
			$this->x = $this->x_non_resident;
			$this->a = $this->a_non_resident;
			$this->b = $this->b_non_resident;
		}
		if($this->DEBUG) fb($this->x);
	}
	
	
	//proc:				processes the financial data and returns the result as a json object
	//params:			n/a
	//returns			a string that describes a json object (will contain {"success": "true", "financial_data": ... } or {"success": "false", "err": ... }
	public function process() {
	
		//Spouse Guid
		$this->spouse = $this->getSpouseGuid();
	
		//OVERSEAS PROCESSING
		if ($this->financial_data['overseas']) {
			//Stipend
			//calculate the extra stipend
			$overflow = $this->financial_data['STIPEND'] - $this->OS_STIPEND_MAX;
			//check if it is over the limit
			if ($overflow > 0) {
				//truncate the stipend
				$this->financial_data['STIPEND'] = $this->financial_data['STIPEND'] - $overflow;
				
				//add the overflow to LAFHA
				$this->financial_data['OS_LAFHA'] += $overflow;
				
				//return warnings explaining the changes
				$warnings['STIPEND'] = "\"Your stipend was over the maximum of $".$this->OS_STIPEND_MAX.".<br />The extra amount ($".$overflow.") was added to your LAFHA to compensate.<br />Please review these figures before submitting.\"";
				$warnings['OS_LAFHA']= "\"Your stipend was over the maximum of $".$this->OS_STIPEND_MAX.".<br />The extra amount ($".$overflow.") was added to your LAFHA to compensate.<br />Please review these figures before submitting.\"";
			} else {
				if (!isset($this->financial_data['OS_LAFHA']))
					$this->financial_data['OS_LAFHA'] = 0;
			}
			
			//spouse
			//calculate the extra stipend
			$s_overflow = $this->financial_data['S_STIPEND'] - $this->OS_STIPEND_MAX;
			//check if it is over the limit
			if ($s_overflow > 0) {
				//truncate the stipend
				$this->financial_data['S_STIPEND'] = $this->financial_data['S_STIPEND'] - $s_overflow;
				
				//add the overflow to LAFHA
				$this->financial_data['S_OS_LAFHA'] += $s_overflow;
				
				//return warnings explaining the changes
				$warnings['S_STIPEND']	= "\"Your spouse's stipend was over the maximum of $".$this->OS_STIPEND_MAX.".<br />The extra amount ($".$s_overflow.") was added to their LAFHA to compensate.<br />Please review these figures before submitting.\"";
				$warnings['S_OS_LAFHA']	= "\"Your spouse's stipend was over the maximum of $".$this->OS_STIPEND_MAX.".<br />The extra amount ($".$s_overflow.") was added to their LAFHA to compensate.<br />Please review these figures before submitting.\"";
			} else {
				if (!isset($this->financial_data['S_OS_LAFHA']))
					$this->financial_data['S_OS_LAFHA'] = 0;
			}

			if($this->DEBUG) fb($this->financial_data['OS_LAFHA']);
			
			//LAFHA
			////The LAFHA may not be more than zero if the stipend is less than the maximum
			if ($overflow <= 0 && $this->financial_data['OS_LAFHA'] != 0) {
				$difference = $this->OS_STIPEND_MAX - $this->financial_data['STIPEND'];
				if($this->DEBUG) fb($difference);
				if ($this->financial_data['OS_LAFHA'] > $difference) {
					$this->financial_data['STIPEND'] += $difference;
					$this->financial_data['OS_LAFHA'] = $this->financial_data['OS_LAFHA'] - $difference;
				} else {
					$this->financial_data['STIPEND'] += $this->financial_data['OS_LAFHA'];
					$this->financial_data['OS_LAFHA'] = 0;
				}	
			}
			//spouse
			if ($s_overflow <= 0 && $this->financial_data['S_OS_LAFHA'] != 0) {
				$s_difference = $this->OS_STIPEND_MAX - $this->financial_data['S_STIPEND'];
				if ($this->financial_data['S_OS_LAFHA'] > $s_difference) {
					$this->financial_data['S_STIPEND'] += $s_difference;
					$this->financial_data['S_OS_LAFHA'] = $this->financial_data['S_OS_LAFHA'] - $s_difference;
				} else {
					$this->financial_data['S_STIPEND'] += $this->financial_data['S_OS_LAFHA'];
					$this->financial_data['S_OS_LAFHA'] = 0;
				}	
			}
		}
		
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
			
			$annum = $this->financial_data['NET_STIPEND'] + $this->financial_data['POST_TAX_SUPER'] + $this->financial_data['ADDITIONAL_TAX'];	//calculate yearly figure
			
			$this->financial_data['TAXABLE_INCOME'] = $this->calculateTaxableIncome($annum);
			$this->financial_data['TAX'] = $this->calculateTax($this->financial_data['TAXABLE_INCOME']);
			$this->financial_data['EMPLOYER_SUPER'] = $this->calculateEmployerSuper($this->financial_data['TAXABLE_INCOME']);
		}
		
		//Maximum MFB & Pre-tax Super
		if (isset($this->financial_data['TAXABLE_INCOME'])) {
		
			//mfb rate
			$mfbrate = $this->getMfbRate($this->financial_data['MFB_RATE']);
		
			//Pre Tax Super (if its not set then set it to the min)
			$this->financial_data['PRE_TAX_SUPER'] = $this->getPreTaxSuper($mfbrate,0);//the 0 means return my value
			
			//Fetch Days Per Week
			$this->financial_data['DAYS_PER_WEEK'] = $this->getDaysPerWeek(0);
			
			//calc max mfbs
			$this->financial_data['MAX_MFB'] = round($this->calculateMaxMFB($this->financial_data['TAXABLE_INCOME'], $mfbrate, $this->financial_data['DAYS_PER_WEEK']));
			
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
			
			$s_annum = $this->financial_data['S_NET_STIPEND'] + $this->financial_data['S_POST_TAX_SUPER'] + $this->financial_data['S_ADDITIONAL_TAX'];	//calculate yearly figure
			
			$this->financial_data['S_TAXABLE_INCOME'] = $this->calculateTaxableIncome($s_annum);
			$this->financial_data['S_TAX'] = $this->calculateTax($this->financial_data['S_TAXABLE_INCOME']);
			$this->financial_data['S_EMPLOYER_SUPER'] = $this->calculateEmployerSuper($this->financial_data['S_TAXABLE_INCOME']);
		}
		
		//Spouse Maximum MFB && Pre Tax Super
		if (isset($this->financial_data['S_TAXABLE_INCOME'])) {
		
			//enumerate mfb rate
			$mfbrate = $this->getMfbRate($this->financial_data['S_MFB_RATE']);
			
			//Spouse Pre Tax Super (if its not set then set it to the min)
			$this->financial_data['S_PRE_TAX_SUPER'] = $this->getPreTaxSuper($mfbrate,1); //the 1 means return spouse value
			
			//Fetch the user's days per week
			$this->financial_data['S_DAYS_PER_WEEK'] = $this->getDaysPerWeek(1);
			
			$this->financial_data['S_MAX_MFB'] = round($this->calculateMaxMFB($this->financial_data['S_TAXABLE_INCOME'], $mfbrate, $this->financial_data['S_DAYS_PER_WEEK']));
			
			//calc claimable mfbs (the mfbs that are left after your housing has been taken out)
			$this->financial_data['S_CLAIMABLE_MFB'] = $this->getClaimableMfb(1);//1 for spouse claimable mfb
		}
		
		
	
		if ($this->DEBUG) fb($this->financial_data);
		
		if ($err == '') {
		
			$result = array('success'=>'true');
			$result['financial_data'] = $this->financial_data;
			$result['warnings'] = $warnings;
			if($this->DEBUG) fb($result);
			return json_encode($result);
		}
		else {
			return '{"success": false, "errors":{'.trim($err,", ").'} }';	//Return with errors
		}
		
	}
	
	
	//calculateEmployerSuper:	Calculates and returns the additional housing allowance (the amount of housing above the max housing mfb, set by Memeber Care)
	//params:					$taxableincome - (a whole number > 0) taxable income (freq doen't matter can take weekly, monthly, annually, etc)
	//returns					employer super (a whole number >= 0)
	public function calculateEmployerSuper($taxableincome){
		return	round($taxableincome * $this->MIN_SUPER_RATE);
	}
	
	
	//calculateMaxMFB:			Calculates and returns the max mfbs a person can take
	//params:					$taxableincome - (a whole number > 0) taxable income (freq doen't matter can take weekly, monthly, annually, etc)
	//							$mfbrate - (a decimal number 0 <= $mfbrate <= 1) the rate of mfbs they are entitled to (atm they are 0% 25% 50% of your taxable income)
	//							$daysperweek - (a whole number 1,2,3,4,5)
	//returns					maxmfb (a whole number >= 0)
	public function calculateMaxMFB($taxableincome, $mfbrate, $daysperweek) {
		
		$maxmfb = $taxableincome;
		
		$maxmfb = $maxmfb * ($mfbrate);
		
		//$maxmfb = $maxmfb * ($daysperweek / 5);
		
		return $maxmfb;
	}
	
	
	//calculateMaxWage:					Takes an index of the weekly tax ranges and returns the range for a wage
	//params:							$index - (an int >= 0) the index of a range in the $this->x variable
	//returns							weekly max wage (a whole number >= 0)
	public function calculateMaxWage($index) {
		//formula and values grabed from:
		//Statement of formulas for calculating amounts to be withheld
		
		//the max taxable income - the tax of max taxable income gives us the max wage for that tax bracket
		return $this->x[$index] - round($this->a[$index] * (floor($this->x[$index]) + 0.99) - $this->b[$index]);
	}
	
	
	//calculateTaxableIncome:			Takes a wage and returns a taxable income
	//params:							$wage - (a whole number > 0) the monthly wage
	//returns							monthly taxable income (a number >= 0)
	public function calculateTaxableIncome($wage){
		return $wage + $this->calculateTaxFromWage($wage);
	}
	
	
	//calculateTaxFromWage:				Takes a wage and returns a taxable income
	//params:							$wage - (a whole number > 0) the monthly wage
	//returns							monthly tax (a whole number >= 0)
	public function calculateTaxFromWage($wage) {
		//formula and values derived from:
		//Statement of formulas for calculating amounts to be withheld
		if($this->DEBUG) fb("wage: ".$wage);
		//convert from months to weeks
		$wage = floor(floor($wage) * 12 / 52);
		
		for( $rangeCount = 0; $rangeCount < count($this->x); $rangeCount++ ){
			if ($wage < $this->calculateMaxWage($rangeCount))
				break;
		}
		if($this->DEBUG) fb("rangecount: ".$rangeCount);
		return round(ceil(($this->a[$rangeCount] * ($wage) - $this->b[$rangeCount]) / (1 - $this->a[$rangeCount])) * 52 / 12);
	}
	
	
	//calculateTax:						Takes a monthly taxable income and returns the tax (formula and values grabed from: Statement of formulas for calculating amounts to be withheld. On the ATO website)
	//params:							$taxableincome - (a whole number > 0) the weekly taxable income
	//returns							monthly tax (a whole number >= 0)
	public function calculateTax($taxableincome) {
		//formula and values grabbed from:
		//Statement of formulas for calculating amounts to be withheld
		
		//ATO rounding for monthly to weekly convertion (if $taxableincome ends with 33 cents then add one cent)
		if (($taxableincome-floor($taxableincome)) == 0.33) $taxableincome += 0.01;
		
		//convert from monthly to weekly
		$taxableincome = $taxableincome * 3 / 13; //same as $taxableincome = $taxableincome * 12 / 52
		
		//ATO rounding for weekly Tax calculation (ignore cents and add 0.99)
		$taxableincome = floor($taxableincome) + 0.99;
		
		//find which weekly tax bracket $taxableincome falls in
		for( $rangeCount = 0; $rangeCount < count($this->x); $rangeCount++ ){
			if ($taxableincome < $this->x[$rangeCount])
				break;
		}
		//calculate tax
		$tax = round($this->a[$rangeCount] * $taxableincome - $this->b[$rangeCount]);
		//convert back to monthly before returning
		return round($tax * 13 / 3); //same as $tax * 52 / 12
	}
	
	
	//getAdditionalHousing:	Calculates and returns the additional housing allowance (the amount of housing above the max housing mfb, set by Memeber Care)
	//params:				n/a
	//returns				additional_housing (a number >= 0)
	public function getAdditionalHousing(){
		if (isset($this->financial_data['HOUSING'])){
		
			//get max cut off
			$maxhousingmfb = $this->getMaxHousingMfb();
			
			//set housing freq to the default if not set
			if (!isset($this->financial_data['HOUSING_FREQUENCY'])) $this->financial_data['HOUSING_FREQUENCY'] = 0;
			
			//make sure housing is monthly
			$monthly_housing = $this->getMonthlyHousing();
			
			//get days per week ratio
			//TODO: apply it to the calculation
			if ($this->spouse)
				$days_per_week_ratio = ($this->getDaysPerWeek(0) + $this->getDaysPerWeek(1)) / 10; //couple ratio
			else
				$days_per_week_ratio = $this->getDaysPerWeek(0) / 5; //singles ratio
				
			//calc additional housing
			return round(max( 0, ($monthly_housing - $maxhousingmfb) ));
		}
		
		return 0;
	}
	
	
	//getClaimableMfb:	Calculates a persons claimable mfbs (the mfbs that are left after your housing has been taken out)
	//params:			$me_or_spouse	- (number 0 or 1) tells function wether you, 0, want your pretax super or the spouses, 1, pretax super
	//returns			claimable mfb (a number >= 0)
	public function getClaimableMfb($me_or_spouse){
		//changes wether it grabs your or spouse values (adds S_ prefix to keys)
		if ($me_or_spouse) $prefix = "S_"; else $prefix = "";
		
		if (isset($this->financial_data[$prefix.'MAX_MFB'])){
			if (isset($this->financial_data['MAX_MFB']) && isset($this->financial_data['HOUSING'])){
				//grab max housing mfb
				$max_housing_mfb = $this->getMaxHousingMfb();
				
				//make sure housing is monthly
				$monthly_housing = $this->getMonthlyHousing();
				
				//calcs spouses claimable mfb if possible
				if ($me_or_spouse){
					if(isset($this->financial_data['S_MAX_MFB'])){
						//calcs spouse claimable mfb (take my mfb off housing first and if there is housing still to be coverd take it from spouse mfb)
						return max(0, $this->financial_data['S_MAX_MFB'] - max(0, min($monthly_housing, $max_housing_mfb) - $this->financial_data['MAX_MFB']));
					} else {
						//if no spouse stipend so cant calc this value so return 0
						return 0;
					}
				}
				//calcs single claimable mfb otherwise
				return max(0, $this->financial_data['MAX_MFB'] - min($monthly_housing, $max_housing_mfb));
			} else {
				//if no housing to take away, just return max mfb
				return $this->financial_data[$prefix.'MAX_MFB'];
			}
		}
		return 0;
	}
	
	//getDaysPerWeek:	Takes a guid and grabs that persons days per week from the database
	//params:			$me_or_spouse	- (number 0 or 1) tells function wether you, 0, want your values or the spouses, 1, values
	//returns			days_per_week (a number 0,1,2,3,4,5) (0 means not found in DB)
	public function getDaysPerWeek($me_or_spouse){
		//grabs data from correct class variables based on if we are dealing with me or spouse
		if ($me_or_spouse) {$guid = $this->spouse; $days_per_week = $this->s_days_per_week;} else {$guid = $this->guid; $days_per_week = $this->days_per_week;}
		//grabs it from DB only if it has not already been grabed (ie when its not its default value of 0)
		if ($days_per_week == 0){
			//Fetch the user's days per week
			$sql = mysql_query("SELECT DAYS_PER_WEEK, FT_PT_OS FROM User_Profiles WHERE guid='".$guid."'");
			if (mysql_num_rows($sql) == 1) {
				$row = mysql_fetch_assoc($sql);
				if ($row['FT_PT_OS'] == 0){
					$days_per_week = 5;
				} else {
					$days_per_week = $row['DAYS_PER_WEEK'] + 1; //+1 because days per week is stored as an index not a number
				}
			}
		}
		//saves data to correct class variables based on if we are dealing with me or spouse
		if ($me_or_spouse) $this->s_days_per_week = $days_per_week; else $this->days_per_week = $days_per_week;
		return $days_per_week;//zero means not found in DB
	}
	
	
	//getHousingStipend:	Calculates and returns the housing stipend (the amount of housing that is not covered by mfbs or
	//						additional housing allowance and needs to be covered by stipend)
	//params:				n/a
	//returns				housing_stipend (a number >= 0)
	public function getHousingStipend(){
		//calc housing stipend (diff between housing and what your mfbs & additional housing allowance will cover)
		if (isset($this->financial_data['HOUSING']) && isset($this->financial_data['STIPEND']) && isset($this->financial_data['ADDITIONAL_HOUSING']) && !$this->financial_data['overseas'])
		{
			$monthly_housing = $this->getMonthlyHousing();
		
			if (isset($this->financial_data['S_STIPEND'])){
				if ($this->DEBUG) fb($this->financial_data);
			
				//calc lastest taxable income for me
				$annum = $this->financial_data['STIPEND'] + $this->financial_data['POST_TAX_SUPER'] + $this->financial_data['ADDITIONAL_TAX'];
				$taxableincome = $this->calculateTaxableIncome($annum);
				//calc lastest taxable income for spouse
				$s_annum = $this->financial_data['S_STIPEND'] + $this->financial_data['S_POST_TAX_SUPER'] + $this->financial_data['S_ADDITIONAL_TAX'];
				$s_taxableincome = $this->calculateTaxableIncome($s_annum);
				
				//mfb rate for me
				$mfbrate = $this->getMfbRate($this->financial_data['MFB_RATE']);
				//mfb rate for spouse
				$s_mfbrate = $this->getMfbRate($this->financial_data['S_MFB_RATE']);
				
				//calc lastest joint max mfbs
				$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(0));
				$s_maxmfb = $this->calculateMaxMFB($s_taxableincome, $s_mfbrate, $this->getDaysPerWeek(1));
				$joint_maxmfb = $maxmfb + $s_maxmfb;
			
				//calc housing stipend from lastest values
				$housing_stipend = max(0, $monthly_housing - $joint_maxmfb - $this->financial_data['ADDITIONAL_HOUSING']);
				//if there is a housing stipend it needs to be split between stipend and mfbs
				//we do this because as you increase stipend you increase mfbs and you decrease the housing stipend
				if ($housing_stipend > 0){
					//recalc taxable income and max mfb with the calc housing stipend
					$annum += $housing_stipend;
					$taxableincome = $this->calculateTaxableIncome($annum);
					$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(0));
					$joint_maxmfb = $maxmfb + $s_maxmfb;

					//max housing mfb
					$max_housing_mfb = $this->getMaxHousingMfb();

					$diff = min($max_housing_mfb, $monthly_housing) - $joint_maxmfb - $housing_stipend;

					//split diff between taxable income and mfbs and take taxable incomes portion of diff away. This will give the ideal taxable income.
					$ideal_taxable_income = ((1 - ($mfbrate*0.5)) * $diff) + $taxableincome;

					//calc the ideal tax from the ideal taxable income
					$ideal_tax = $this->calculateTax($ideal_taxable_income);

					//calc what housing stipend should be by taking everything other than housing stipend from the ideal taxable income
					$housing_stipend = $ideal_taxable_income - $ideal_tax - $this->financial_data['ADDITIONAL_TAX'] - $this->financial_data['POST_TAX_SUPER'] - $this->financial_data['STIPEND'];
				}
				
				return round($housing_stipend);
			} else {
				//makes sure that a person without a stipend for there spouse is actually single
				if (!$this->spouse){
					//calc lastest taxable income
					$annum = $this->financial_data['STIPEND'] + $this->financial_data['POST_TAX_SUPER'] + $this->financial_data['ADDITIONAL_TAX'];
					$taxableincome = $this->calculateTaxableIncome($annum);
					
					//mfb rate
					$mfbrate = $this->getMfbRate($this->financial_data['MFB_RATE']);
					
					//calc lastest max mfbs
					$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(0));
				
					//calc housing stipend from lastest values
					$housing_stipend = max(0, $monthly_housing - $maxmfb - $this->financial_data['ADDITIONAL_HOUSING']);
					//if there is a housing stipend it needs to be split between stipend and mfbs
					//we do this because as you increase stipend you increase mfbs and you decrease the housing stipend
					if ($housing_stipend > 0){
						//recalc taxable income and max mfb with the calc housing stipend
						$annum += $housing_stipend;
						$taxableincome = $this->calculateTaxableIncome($annum);
						$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(0));

						$max_housing_mfb = $this->getMaxHousingMfb();
	
						$diff = min($max_housing_mfb, $monthly_housing) - $maxmfb - $housing_stipend;
	
						//split diff between taxable income and mfbs and take taxable incomes portion of diff away. This will give the ideal taxable income.
						$ideal_taxable_income = ((1 - ($mfbrate*0.5)) * $diff) + $taxableincome;
	
						//calc the ideal tax from the ideal taxable income
						$ideal_tax = $this->calculateTax($ideal_taxable_income);
	
						//calc what housing stipend should be by taking everything other than housing stipend from the ideal taxable income
						$housing_stipend = $ideal_taxable_income - $ideal_tax - $this->financial_data['ADDITIONAL_TAX'] - $this->financial_data['POST_TAX_SUPER'] - $this->financial_data['STIPEND'];
					}
					
					return round($housing_stipend);
				}
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
		return $this->spouse ? 1600 : 960;
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
		if ($this->spouse){
			//get the spouse's guid from the DB
			$sql = mysql_query("SELECT SPOUSE_GUID FROM User_Profiles WHERE guid='".$this->guid."'");
			if (mysql_num_rows($sql) == 1) {
				$row = mysql_fetch_assoc($sql);
				return $row['SPOUSE_GUID'];
			}
		}
		//if the spouse guid was not found return 0
		return 0;
	}
	
	
	
	public function setGuid($guid) {
		$this->guid = $guid;
	}
	
	
	
	public function setSpouseGuid($guid) {
		$this->spouse = $guid;
	}
	
	
	
	public function setFinancialData($findat){
		$this->financial_data = $findat;
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