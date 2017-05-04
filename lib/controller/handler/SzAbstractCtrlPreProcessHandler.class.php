<?php
abstract class SzAbstractCtrlPreProcessHandler
{

    /**
     * Abstract definition of the controller pre process handler. <br/>
     * To be implemented by the application. <br/>
     *
     * This function will be called after the router parsing process finished, <br/>
     * and before the requests dispatching process. <br/>
     *
     * The implementation class <b>HAVE</b> to be named & placed at
     *     "<b>APP_ROOT</b>/lib/controller/handler".
     *
     * @param array $inputs passed by reference, params inputted from $_REQUEST['*']
     * @return boolean
     */
    public abstract function handle(&$inputs);

}