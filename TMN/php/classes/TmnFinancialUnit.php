<?php
if(file_exists('../classes/TmnDatabase.php')) {
    include_once('../classes/TmnDatabase.php');
}
if(file_exists('classes/TmnDatabase.php')) {
    include_once('classes/TmnDatabase.php');
}
if(file_exists('php/classes/TmnDatabase.php')) {
    include_once('php/classes/TmnDatabase.php');
}

class TmnFinancialUnit {


    ///////////////////INSTANCE VARIABLES/////////////////////


    protected 	$db		                    = null;
    protected   $people                     = Array();
    public      $financial_account_number   = 0;
    public      $last_tmn_effective_date    = null;

	public function __construct($logfile, $data) {
		
		if (is_array($data)) {

            if(isset($data['TMN_EFFECTIVE_DATE'])) {
                $this->tmn_effective_date   = new DateTime($data['TMN_EFFECTIVE_DATE']);
            }

            if(isset($data['FIN_ACC_NUM'])) {
                $this->financial_account_number = $data['FIN_ACC_NUM'];
            }

		}

        try {
            //grab an instance of the TmnDatabase
            $this->db	= TmnDatabase::getInstance($logfile);

        } catch (LightException $e) {
            //if there is a problem with the Database kill the object
            throw new FatalException(__CLASS__ . " Exception: Couldn't Connect to Database due to error; " . $e->getMessage());
        }
		
	}

    public static function getActiveFinancialUnits($logfile) {

        try {
            $db             = TmnDatabase::getInstance($logfile);
        } catch (LightException $e) {
            //if there is a problem with the Database kill the object
            throw new FatalException(__CLASS__ . " Exception: Couldn't Connect to Database due to error; " . $e->getMessage());
        }
		$fanSql			= "SELECT low.* FROM User_Profiles AS users LEFT JOIN Low_Account AS low ON users.FIN_ACC_NUM=low.FIN_ACC_NUM WHERE users.INACTIVE = 0 AND users.EXEMPT_FROM_TMN = 0 AND users.IS_TEST_USER = 0";
		$stmt 			= $db->prepare($fanSql);
		$fanResult		= $stmt->fetchAll(PDO::FETCH_ASSOC);
		$returnArray	= array();

		foreach ($fanResult as $row) {

            $financialUnit      = NULL;

            if (isset( $returnArray[$row["FIN_ACC_NUM"]] )) {
                $financialUnit  = $returnArray[$row["FIN_ACC_NUM"]];
            } else {
                $financialUnit  = new TmnFinancialUnit($logfile, $row);
            }

            $person = new TmnCrudUser($logfile);
            $person->loadDataFromAssocArray($row);
            $financialUnit->addPerson($person);

            $returnArray[$row["FIN_ACC_NUM"]] = $financialUnit;

        }

		return $returnArray;

    }

    public function addPerson($person) {

        if (is_a($person, "TmnCrudUser")) {

            array_push($this->people, $person);

        }

    }

    public function getMinistry() {

        $ministryString    = null;

        foreach ($this->people as $person) {

            if (!isset($ministryString) || $person->getField('MINISTRY') == "Student Life") {
                $ministryString = $person->getField('MINISTRY');
            }

        }

        return $ministryString;

    }

    public function getTmnsAwaitingApproval() {
        //TODO: write sql
    }

    public function getEmails() {

        $emailString    = "";

        foreach ($this->people as $person) {

            $emailString .= $person->getField('EMAIL') . ", ";

        }

        if (count($this->people) > 0) {
            $emailString    = substr($emailString, 0, -2);
        }

        return $emailString;

    }

    public function getNames() {

        $nameString    = "";

        foreach ($this->people as $person) {

            $nameString .= $person->getField('FIRSTNAME') . " & ";

        }

        if (count($this->people) > 0) {
            $nameString    = substr($nameString, 0, -2) . $this->people[0]->getField('SURNAME');
        }

        return $nameString;

    }


}

?>