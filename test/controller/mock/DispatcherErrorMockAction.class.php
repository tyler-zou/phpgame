<?php
require_once dirname(dirname(dirname(__DIR__))) . '/lib/controller/action/SzAbstractAction.class.php';

class DispatcherErrorMockAction extends SzAbstractAction
{
    protected $paramTypes = array(
        self::TYPE_INT,
        self::TYPE_INT,
    );
}