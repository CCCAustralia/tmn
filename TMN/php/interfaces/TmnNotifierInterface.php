<?php

interface TmnNotifierInterface {

    /**
     * Will take a financial unit and generate emails for them and their autherisers. The specifics of the email
     * are determined by each implementation of this interface
     *
     * @param TmnFinancialUnit $financial_unit
     */
    public function sendEmailsFor(TmnFinancialUnit $financial_unit);

}