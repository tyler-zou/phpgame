<?php
class SzDispatcher
{

    /**
     * Dispatch request to execute.
     *
     * @param SzRequest $request
     * @throws SzException 10401
     * @return SzResponse
     */
    public function dispatch($request)
    {
        // get action instance
        $actionName = $this->getActionClassName($request);

        // record start time
        SzController::get()->logActionStartTime($actionName);

        /* @var SzAbstractAction $actionInstance */
        $actionInstance = SzSystemCache::cache(SzSystemCache::CTRL_ACTION_INSTANCE, $actionName);
        if (!$actionInstance) {
            $actionInstance = new $actionName();
            if (!method_exists($actionInstance, 'execute')) {
                /**
                 * No "execute" method declaration in abstract class.
                 * Since the number of the params of the function "execute" is various (random),
                 * it cannot be defined in class SzAbstractAction as an abstract function or interface.
                 * Thus it would be checked here with function "method_exists".
                 */
                throw new SzException(10401, $actionName);
            }
            SzSystemCache::cache(SzSystemCache::CTRL_ACTION_INSTANCE, $actionName, $actionInstance);
        }

        // validate request params
        $reqParams = $request->getParams();
        $actionInstance->validateParams($reqParams);

        /* @var SzResponse $response */
        $response = call_user_func_array(array($actionInstance, 'execute'), $reqParams);

        // record end time
        SzController::get()->logActionEndTime($actionName);

        return $response;
    }

    /**
     * Get action class name according to request attributes. <br/>
     * <pre>
     * e.g
     *     $function = 'page'; $action = 'index'; className => 'PageIndexAction' <br/>
     *     $function = 'Game'; $action = 'Start'; className => 'GameStartAction'
     * </pre>
     *
     * @param SzRequest $request
     * @return string
     */
    private function getActionClassName($request)
    {
        return $request->getFunction() . $request->getAction() . 'Action';
    }

}