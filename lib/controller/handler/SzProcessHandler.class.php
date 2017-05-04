<?php
class SzProcessHandler
{

    /**
     * Handling all the pre process logic before requests handling.
     *
     * @param array $inputs passed by reference
     * @return void
     */
    public static function preProcess(&$inputs)
    {
        $preProcessHandlerClasses = SzConfig::get()->loadAppConfig('controller', 'PRE_HANDLERS');
        if ($preProcessHandlerClasses) {
            foreach ($preProcessHandlerClasses as $className) {
                $postHandler = new $className();
                if ($postHandler instanceof SzAbstractCtrlPreProcessHandler) {
                    $postHandler->handle($inputs);
                }
            }
            $postHandler = null;
        }
    }

    /**
     * Handling all the post process logic after all the requests handling done and before persist starts.
     *
     * @param array $inputs
     * @return void
     */
    public static function postProcess($inputs)
    {
        $postProcessHandlerClasses = SzConfig::get()->loadAppConfig('controller', 'POST_HANDLERS');
        if ($postProcessHandlerClasses) {
            foreach ($postProcessHandlerClasses as $className) {
                $postHandler = new $className();
                if ($postHandler instanceof SzAbstractCtrlPostProcessHandler) {
                    $postHandler->handle($inputs);
                }
            }
            $postHandler = null;
        }
    }

}