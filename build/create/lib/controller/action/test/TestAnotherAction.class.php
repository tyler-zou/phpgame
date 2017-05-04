<?php
class TestAnotherAction extends SzAbstractAction
{

    protected $paramTypes = array(
    );

    public function execute()
    {
        $body = array(
            'output' => 'another API!',
        );

        return $this->buildResponse($body);
    }

}