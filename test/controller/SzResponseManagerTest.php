<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzResponseManagerTest extends SzTestAbstract
{

    /**
     * @var SzResponseManager
     */
    protected static $instance;

    protected static $body          = 'body';
    protected static $headers       = array('headers');
    protected static $file          = array();
    protected static $contentType   = 'contentType';
    protected static $ormName       = 'ormName';
    protected static $shardId       = 1;
    protected static $pkValue       = 1;
    protected static $value         = array('Key'=>'Value');
    protected static $updateOutput  = array(
        'ormName' => array(
            1 => array(
                1 => array('Key' => 'Value')
            )
        )
    );

    public function setUp()
    {
        self::$instance = new SzResponseManager();
    }

    /**
     * @see SzResponseManager::sendHeader
     */
    public function test_SendHeader()
    {
        if (!function_exists('xdebug_get_headers')) {
            return; // necessary extension does not exist
        }

        header_remove();
        header("Location: foo");

        $reflector = $this->setMethodPublic('SzResponseManager', 'sendHeader');
        $reflector->invoke(self::$instance, 'Location', 'foo');

        $this->assertEquals(array("Location: foo"), xdebug_get_headers());
    }

    /**
     * @see SzResponseManager::mergeResponses
     */
    public function test_MergeResponses()
    {
        $response = new SzResponse();
        $response->setBody(self::$body);
        $response->setContentType(self::$contentType);
        $response->setFile(self::$file);
        $response->setHeaders(self::$headers);

        self::$instance->mergeResponses(array($response));

        $this->assertEquals(array(self::$body), self::$instance->getBody());
        $this->assertEquals(self::$contentType, self::$instance->getContentType());
        $this->assertEquals(self::$file, self::$instance->getFile());
        $this->assertEquals(self::$headers, self::$instance->getHeaders());
    }

    /**
     * @see SzResponseManager::mergePersistUpdateResponse
     */
    public function test_MergePersistUpdateResponse()
    {
        SzPersister::get()->addResponse(array(
            self::$ormName, // ormName
            self::$shardId, // shardId
            self::$pkValue, // pkValue
            self::$value, // value
        ));

        $reflector = $this->setMethodPublic('SzResponseManager', 'mergePersistUpdateResponse');
        $reflector->invoke(self::$instance);

        $body = self::$instance->getBody();
        $this->assertEquals(self::$updateOutput, $body[SzResponseManager::PERSIST_UPDATE_BODY_KEY]);
    }

    /**
     * @see SzResponseManager::send
     */
    public function test_Send()
    {
        $this->expectOutputString(json_encode(array('code' => 0, 'msg'=> array(), SzResponseManager::PERSIST_UPDATE_BODY_KEY => self::$updateOutput)));
        self::$instance->send();
    }

    /**
     * @see SzResponseManager::send
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10208
     */
    public function test_Send_Error_10208()
    {
        self::$instance->setFile(array('SzAutoloadMock.class.php', dirname(dirname(__DIR__)) . '/test/autoload_Error'));
        self::$instance->send();
    }
}