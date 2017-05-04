<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzResponseTest extends SzTestAbstract
{
    /**
     * @var SzResponse
     */
    protected static $instance;

    protected static $body          = 'body';
    protected static $headers       = array('headers');
    protected static $file          = 'file';
    protected static $contentType   = 'contentType';

    public function setUp()
    {
        self::$instance = new SzResponse();
    }

    /**
     * @see SzRequest::getFunction
     */
    public function test_GetBody()
    {
        self::$instance->setBody(self::$body);
        $this->assertEquals(self::$body, self::$instance->getBody());
    }

    /**
     * @see SzRequest::getContentType
     */
    public function test_GetContentType()
    {
        self::$instance->setContentType(self::$contentType);
        $this->assertEquals(self::$contentType, self::$instance->getContentType());
    }

    /**
     * @see SzRequest::getFile
     */
    public function test_GetFile()
    {
        self::$instance->setFile(self::$file);
        $this->assertEquals(self::$file, self::$instance->getFile());
    }

    /**
     * @see SzRequest::getHeader
     */
    public function test_GetHeader()
    {
        self::$instance->setHeaders(self::$headers);
        $this->assertEquals(self::$headers, self::$instance->getHeaders());
    }
}