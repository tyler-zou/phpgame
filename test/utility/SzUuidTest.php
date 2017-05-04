<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzUuidTest extends SzTestAbstract
{
    /**
     * @see SzUuid::genUuid
     */
    public function test_GenUuid()
    {
        $this->assertTrue(SzUuid::isUuid(SzUuid::genUuid()));
    }
}