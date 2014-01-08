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


    protected   $logfile                    = null;
    protected 	$db		                    = null;
    protected   $people                     = Array();
    public      $financial_account_number   = 0;
    public      $last_tmn_effective_date    = null;
    private     $authoriser_guid_array      = array();
    private     $authoriser_array           = array();

	public function __construct($logfile, $data) {

        $this->logfile  = $logfile;

		if (is_array($data)) {

            if(isset($data['TMN_EFFECTIVE_DATE'])) {
                $this->tmn_effective_date   = new DateTime($data['TMN_EFFECTIVE_DATE']);
            }

            if(isset($data['FIN_ACC_NUM'])) {
                $this->financial_account_number = $data['FIN_ACC_NUM'];
            }

            if(isset($data['AUTH_LEVEL_1'])) {
                $this->authoriser_guid_array[1] = $data['AUTH_LEVEL_1'];
            }

            if(isset($data['AUTH_LEVEL_2'])) {
                $this->authoriser_guid_array[2] = $data['AUTH_LEVEL_2'];
            }

            if(isset($data['AUTH_LEVEL_3'])) {
                $this->authoriser_guid_array[3] = $data['AUTH_LEVEL_3'];
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

        $db             = null;
        try {
            $db         = TmnDatabase::getInstance($logfile);
        } catch (LightException $e) {
            //if there is a problem with the Database kill the object
            throw new FatalException(__CLASS__ . " Exception: Couldn't Connect to Database due to error; " . $e->getMessage());
        }

		$fanSql			= "SELECT low.*, users.* FROM (SELECT * FROM User_Profiles WHERE User_Profiles.INACTIVE = 0 AND User_Profiles.EXEMPT_FROM_TMN = 0 AND User_Profiles.IS_TEST_USER = 0) AS users LEFT OUTER JOIN Low_Account AS low ON users.FIN_ACC_NUM=low.FIN_ACC_NUM GROUP BY users.ID";
		$stmt 			= $db->prepare($fanSql);
        $stmt->execute();
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

            if (!isset($ministryString) || $person->getField('ministry') == "Student Life") {
                $ministryString = $person->getField('ministry');
            }

        }

        return $ministryString;

    }

    public function getTmnsAwaitingApprovalSince($date) {

        $date           = ( isset($date) && is_a($date, "DateTime") ? $date : new DateTime() );
        $tmnSql			= "SELECT * FROM (SELECT * FROM Tmn_Sessions WHERE FAN = :financial_account_number AND AUTH_SESSION_ID IS NOT NULL) as sessions LEFT JOIN Auth_Table as auth ON sessions.AUTH_SESSION_ID = auth.AUTH_SESSION_ID WHERE auth.USER_TIMESTAMP > STR_TO_DATE(:date, '%Y-%m-%d %H:%i:%s') AND (auth.FINANCE_RESPONSE = 'Pending') AND (auth.USER_RESPONSE = 'Yes' OR auth.USER_RESPONSE = 'Pending') AND (auth.LEVEL_1_RESPONSE = 'Yes' OR auth.LEVEL_1_RESPONSE = 'Pending') AND (auth.LEVEL_2_RESPONSE = 'Yes' OR auth.LEVEL_1_RESPONSE = 'Pending') AND (auth.LEVEL_3_RESPONSE = 'Yes' OR auth.LEVEL_1_RESPONSE = 'Pending')";
        $values         = array( ":financial_account_number" => $this->financial_account_number, ":date" => $date->format("Y-m-d H:i:s") );
        $stmt 			= $this->db->prepare($tmnSql);
        $stmt->execute($values);
        $tmnResult		= $stmt->fetchAll(PDO::FETCH_ASSOC);
        $returnArray	= array();

        foreach ($tmnResult as $row) {

            $tmn = new TmnCrudSession($this->getLogfile());
            $tmn->loadDataFromAssocArray($row);
            array_push($returnArray, $tmn);

        }

        return $returnArray;
    }

    public function getAuthoriserEmailsForLevel($level = 0) {

        $level          = min($level, count($this->authoriser_guid_array));
        $emailString    = "";

        for ($levelCount = 1; $levelCount <= $level; $levelCount++) {

            if (!isset($this->authoriser_array[$levelCount])) {
                $this->authoriser_array[$levelCount] = new TmnCrudUser($this->getLogfile(), $this->authoriser_guid_array[$levelCount]);
            }

            $authoriser = $this->authoriser_array[$levelCount];

            $emailString .= $authoriser->getField('email') . ", ";

        }

        if (count($this->people) > 0) {
            $emailString    = substr($emailString, 0, -2);
        }

        return $emailString;

    }

    public function getAuthoriserNamesForLevel($level = 0, $fullName = false) {

        $level          = min($level, count($this->authoriser_guid_array));
        $nameString     = "";

        for ($levelCount = 1; $levelCount <= $level; $levelCount++) {

            if (!isset($this->authoriser_array[$levelCount])) {
                $this->authoriser_array[$levelCount] = new TmnCrudUser($this->getLogfile(), $this->authoriser_guid_array[$levelCount]);
            }

            $authoriser = $this->authoriser_array[$levelCount];

            $nameString .= $authoriser->getField('firstname');
            if ($fullName) {
                $nameString .= " " . $authoriser->getField('surname');
            }
            $nameString .= ", ";

        }

        if (count($this->people) > 0) {
            $nameString    = substr($nameString, 0, -2);
        }

        return $nameString;

    }

    public function getEmails() {

        $emailString    = "";

        foreach ($this->people as $person) {

            $emailString .= $person->getField('email') . ", ";

        }

        if (count($this->people) > 0) {
            $emailString    = substr($emailString, 0, -2);
        }

        return $emailString;

    }

    public function getNames($fullName = false) {

        $nameString    = "";

        foreach ($this->people as $person) {

            $nameString .= $person->getField('firstname') . " & ";

        }

        if (count($this->people) > 0) {
            if($fullName) {
                $nameString    = substr($nameString, 0, -2) . $this->people[0]->getField('surname');
            } else {
                $nameString    = substr($nameString, 0, -3);
            }
        }

        return $nameString;

    }

    protected function getLogfile() {
        return $this->logfile;
    }


}

?>