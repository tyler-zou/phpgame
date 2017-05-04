<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzParamTest extends SzTestAbstract
{
    /**
     * @see SzParam::getWholeRequest
     */
    public function test_GetWholeRequest()
    {
        $_REQUEST['request'] = 'request';
        $this->assertEquals($_REQUEST, SzParam::getWholeRequest());
    }

    /**
     * @see SzParam::getReqParam
     */
    public function test_GetReqParam()
    {
        $_REQUEST['request'] = 'request';
        $this->assertEquals($_REQUEST['request'], SzParam::getReqParam('request'));
    }

    /**
     * @see SzParam::getWholePost
     */
    public function test_GetWholePost()
    {
        $_POST['post'] = 'post';
        $this->assertEquals($_POST, SzParam::getWholePost());
    }

    /**
     * @see SzParam::getPostParam
     */
    public function test_GetPostParam()
    {
        $_POST['post'] = 'post';
        $this->assertEquals($_POST['post'], SzParam::getPostParam('post'));
    }

    /**
     * @see SzParam::getWholeGet
     */
    public function test_GetWholeGet()
    {
        $this->assertEquals($_GET, SzParam::getWholeGet());
    }

    /**
     * @see SzParam::getGetParam
     */
    public function test_GetGetParam()
    {
        $_GET['get'] = 'get';
        $this->assertEquals($_GET['get'], SzParam::getGetParam('get'));
    }

    /**
     * @see SzParam::getWholeCookie
     */
    public function test_GetWholeCookie()
    {
        $_COOKIE['cookie'] = 'cookie';
        $this->assertEquals($_COOKIE, SzParam::getWholeCookie());
    }

    /**
     * @see SzParam::getCookieParam
     */
    public function test_GetCookieParam()
    {
        $_COOKIE['cookie'] = 'cookie';
        $this->assertEquals($_COOKIE['cookie'], SzParam::getCookieParam('cookie'));
    }

    /**
     * @see SzParam::setCookieParam
     */
    public function test_SetCookieParam()
    {
        $this->assertEquals('test', SzParam::setCookieParam('test', 'test', 3600 * 24));
    }

}