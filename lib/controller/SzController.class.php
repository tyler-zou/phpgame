<?php
class SzController
{

    /**
     * @var SzController
     */
    private static $instance;

    /**
     * Initialize SzController instance.
     *
     * @return void
     */
    public static function init()
    {
        self::$instance = new SzController();
    }

    /**
     * Get the instance of SzController.
     *
     * @return SzController
     */
    public static function get()
    {
        return self::$instance;
    }

    /**
     * @var SzRouter
     */
    private $router;
    /**
     * @var SzDispatcher
     */
    private $dispatcher;
    /**
     * @var SzRequestManager
     */
    private $reqManager;
    /**
     * @var SzResponseManager
     */
    private $resManager;
    /**
     * Total requests count.
     *
     * @var int
     */
    private $requestsCount = 0;
    /**
     * Finished requests count.
     *
     * @var int
     */
    private $finishedCount = 0;
    /**
     * History time consumption of all action requests.
     *
     * <pre>
     * array(
     *     array($actionName, $startTime, $endTime),
     *     ...
     * )
     * </pre>
     *
     * @var array
     */
    private $actionTimeConsumption = array();

    /**
     * Initialize controller components.
     *
     * @return SzController
     */
    public function __construct()
    {
        $this->router     = new SzRouter();
        $this->dispatcher = new SzDispatcher();
        $this->reqManager = new SzRequestManager();
        $this->resManager = new SzResponseManager();
    }

    /**
     * Process the requests & send out the reponses.
     *
     * @throws SzException 10406
     */
    public function process()
    {
        // record start time
        $startMicroTime = SzTime::getMicroTime();

        // parse the raw PHP request inputs
        $inputs = $this->router->parseRawInputs();

        /** @var SzResponseManager $resManager */
        if (SzConfig::get()->loadAppConfig('app', 'API_REPEAT_CHECK')
            && ($resManager = unserialize(SzContextFactory::get()->getAppCache()->get(SzParam::getReqParam('sign'))))
        ) {
            if ($resManager->responseCount >= SzConfig::get()->loadAppConfig('app', 'API_REPEAT_LIMIT')) {
                throw new SzException(10406);
            }
            $resManager->responseCount++;
            //cache SzResponseManager with expire time
            SzContextFactory::get()->getAppCache()->set(
                SzParam::getReqParam('sign'),
                serialize($resManager),
                SzConfig::get()->loadAppConfig('app', 'API_REPEAT_EXPIRE')
            );
            $resManager->send();
        } else {
            // work through all pre process handlers
            SzProcessHandler::preProcess($inputs);

            // format requests
            $this->router->formatRequests($inputs, $this->reqManager);
            $this->requestsCount = $this->reqManager->getTotalRequestsCount();

            // loop & dispatch requests & collect responses
            while ($request = $this->reqManager->shiftRequest()) {
                $this->resManager->mergeResponse(
                    $this->dispatcher->dispatch($request)
                );
                ++$this->finishedCount;
            }

            // work through all post process handlers
            SzProcessHandler::postProcess($inputs);

            // record persist start time
            SzPersister::get()->persist();

            // record end time
            $this->logExecutionTimeConsumption($startMicroTime);

            //cache SzResponseManager with expire time
            if (SzConfig::get()->loadAppConfig('app', 'API_REPEAT_CHECK')) {
                SzContextFactory::get()->getAppCache()->set(
                    SzParam::getReqParam('sign'),
                    serialize($this->resManager),
                    SzConfig::get()->loadAppConfig('app', 'API_REPEAT_EXPIRE')
                );
            }

            // send out the response
            $this->resManager->send();
        }
    }

    /**
     * Get total requests count.
     *
     * @return int
     */
    public function getTotalRequestsCount()
    {
        return $this->requestsCount;
    }

    /**
     * Get finished requests count.
     *
     * @return int
     */
    public function getFinishedRequestsCount()
    {
        return $this->finishedCount;
    }

    /**
     * Log the start time of single action request.
     *
     * @see SzController::$actionTimeConsumption
     *
     * @param string $actionName
     * @return void
     */
    public function logActionStartTime($actionName)
    {
        $time = SzTime::getMicroTime();
        array_push($this->actionTimeConsumption, array($actionName, $time, $time)); // default end time is same as start time
    }

    /**
     * Log the end time of single action request.
     *
     * @see SzController::$actionTimeConsumption
     *
     * @param string $actionName
     * @return void
     */
    public function logActionEndTime($actionName)
    {
        if (!$this->actionTimeConsumption) {
            return; // invalid function call of logActionEndTime, no start data in $this->actionTimeConsumption
        }

        $lastRecordIndex = count($this->actionTimeConsumption) - 1;
        $lastActionRecord = $this->actionTimeConsumption[$lastRecordIndex];

        if ($actionName != $lastActionRecord[0]) {
            return; // invalid, name incorrect
        }

        $lastActionRecord[2] = SzTime::getMicroTime(); // set end time
        $this->actionTimeConsumption[$lastRecordIndex] = $lastActionRecord;
    }

    /**
     * Log the whole execution time consumption info into log system. <br/>
     * Including all the actions consumption & total consumption.
     *
     * @param int $executionStartTime
     * @return void
     */
    private function logExecutionTimeConsumption($executionStartTime)
    {
        $now = SzTime::getMicroTime();
        $consumptionData = array();

        // total requests consumption
        $totalConsumption = floor($now - $executionStartTime);
        if ($totalConsumption <= 0) {
            $totalConsumption = 1;
        }
        $consumptionData['totalConsumed'] = $totalConsumption;

        // consumption per request
        $consumptionData['actions'] = array();
        foreach ($this->actionTimeConsumption as $actionConsumption) {
            $consumption = floor($actionConsumption[2] - $actionConsumption[1]);
            if ($consumption <= 0) {
                $consumption = 1;
            }
            array_push($consumptionData['actions'], array(
                'name' => $actionConsumption[0],
                'consumed' => $consumption
            ));
        }

        SzLogger::get()->info('SzController: Request executed', $consumptionData);
    }

    /**
     * Log request status(how many request, how many done) when exception exits.
     *
     * @return void
     */
    public function logExceptionExitReqStatus()
    {
        SzLogger::get()->warn('SzController: Status when process exits with exception', array(
            'reqCountGot'    => $this->getTotalRequestsCount(),
            'reqCountSolved' => $this->getFinishedRequestsCount(),
        ));
    }

}