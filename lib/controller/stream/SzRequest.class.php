<?php
class SzRequest
{

    /**
     * request function name:
     * e.g user, friend, game, gift
     *
     * @var string
     */
    private $function;
    /**
     * request action name:
     * e.g get, set, update, start, send
     *
     * @var string
     */
    private $action;
    /**
     * request params
     *
     * @var array
     */
    private $params;

    /**
     * Initialize request.
     *
     * @param string $function
     * @param string $action
     * @param array $params default array()
     * @return SzRequest
     */
    public function __construct($function, $action, $params = array())
    {
        $this->function = ucfirst($function);
        $this->action   = ucfirst($action);

        if ($params && is_array($params)) {
            foreach ($params as $key => $param) {
                $params[$key] = SzUtility::escape($param);
            }
        }
        $this->params   = $params;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $function
     */
    public function setFunction($function)
    {
        $this->function = $function;
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

}