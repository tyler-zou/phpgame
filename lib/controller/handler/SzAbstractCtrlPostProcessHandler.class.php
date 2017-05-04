<?php
abstract class SzAbstractCtrlPostProcessHandler
{

    /**
     * Abstract definition of the controller post process handler. <br/>
     * To be implemented by the application. <br/>
     *
     * This function will be called after all the inputted requests finished, <br/>
     * and before the SzPersister trigger the persist logic. <br/>
     *
     * The implementation class <b>HAVE</b> to be named & placed at
     *     "<b>APP_ROOT</b>/lib/controller/handler".
     *
     * @param array $inputs params inputted from $_REQUEST['*']
     * @return boolean
     */
    public abstract function handle($inputs);

}