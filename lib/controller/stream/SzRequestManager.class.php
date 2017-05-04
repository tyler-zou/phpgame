<?php
class SzRequestManager
{

    /**
     * queue of SzRequest
     *
     * @var array
     */
    private $queue = array();

    /**
     * Register one request into requests queue from three necessary inputs.
     *
     * @param string $function
     * @param string $action
     * @param array $params default array(), means no params inputted
     * @return void
     */
    public function registerRequest($function, $action, $params = array())
    {
        $this->queue[] = new SzRequest($function, $action, $params);
    }

    /**
     * Register one request into requests queue from array inputs.
     *
     * @param array $input
     * @throws SzException 10401
     * @return void
     */
    public function registerRawRequest($input)
    {
        $wrongFormat = false;

        if (!is_array($input) || !$input) { // not array or empty
            $wrongFormat = true;
        } else if (count($input) < 1) { // wrong count number
            $wrongFormat = true;
        } else if (!isset($input[0])) { // has no key 0 ("{$function}.{$action}")
            $wrongFormat = true;
        } else {
            /**
             * "{$function}.{$action}"
             * =>
             * array(
             *     $function,
             *     $action
             * )
             */
            $callInfo = SzUtility::explodeWithTrim('.', $input[0]);
            $params = (isset($input[1]) && is_array($input[1])) ? $input[1] : array();
            $this->queue[] = new SzRequest($callInfo[0], $callInfo[1], $params);
        }

        if ($wrongFormat) {
            throw new SzException(10400);
        }
    }

    /**
     * Add one request into requests queue.
     *
     * @param SzRequest $request
     * @return void
     */
    public function addRequest($request)
    {
        $this->queue[] = $request;
    }

    /**
     * Shift the first request out of the queue. <br/>
     * <b>NULL</b> returned when requests queue is empty.
     *
     * @return SzRequest
     */
    public function shiftRequest()
    {
        return array_shift($this->queue);
    }

    /**
     * Get total requests count.
     *
     * @return int
     */
    public function getTotalRequestsCount()
    {
        return count($this->queue);
    }

}