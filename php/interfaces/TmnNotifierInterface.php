<?php

interface TmnNotifierInterface {

    public static function create($action);

    /**
     * Will take a financial unit and generate emails for them and their autherisers. The specifics of the email
     * are determined by each implementation of this interface
     *
     * @param TmnFinancialUnit $financial_unit
     */
    public function sendEmailsFor(TmnFinancialUnit $financial_unit);

    /**
     * Sends a summary of what was sent by this notifier to the MemberCare staff.
     */
    public function sendReportToMemberCare();

    /**
     * Returns as string with all the email addresses of MemberCare staff in the system.
     */
    public function memberCareEmails();

    /**
     * Returns the number of notifications that have been sent by this notifier.
     */
    public function sendCount();

}