<?php
require_once dirname(dirname(dirname(__DIR__))) . '/lib/controller/action/SzAbstractAction.class.php';

class DispatcherMockAction extends SzAbstractAction
{
    protected $paramTypes = array(
        self::TYPE_INT,
        self::TYPE_INT,
    );

    public function execute($param1, $param2)
    {
        return $param1 + $param2;
    }
}