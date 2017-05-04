<?php
class SzRouter
{

    /**
     * Initialize SzRouter.
     *
     * @return SzRouter
     */
    public function __construct()
    {
    }

    /**
     * Parse the PHP request inputs.
     *
     * <pre>
     * The returned $inputs are all the params comes from "$_REQUEST['*']".
     * </pre>
     *
     * @return array $inputs
     */
    public function parseRawInputs()
    {
        return SzParam::parseRawInputs();
    }

    /**
     * Format the input requests, separate them into single SzRequest, and register into SzRequests.
     *
     * @param array $inputs
     * @param SzRequestManager $reqManager passed as a reference
     * @return void
     */
    public function formatRequests($inputs, &$reqManager)
    {
        if (!$inputs) {
            $reqManager->registerRequest('Page', 'Index');
        } else {
            foreach ($inputs as $input) {
                $reqManager->registerRawRequest($input);
            }
        }
    }

}