<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzUtilityTest extends SzTestAbstract
{
    /**
     * @see SzUtility::getClientBrowser
     */
    public function test_GetClientBrowser()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0';
        $this->assertEquals('FireFox 31.0', SzUtility::getClientBrowser());
    }

    /**
     * @see SzUtility::getServerHost
     */
    public function test_GetServerHost()
    {
        $_SERVER["SERVER_ADDR"] = '127.0.0.1';
        $this->assertEquals($_SERVER["SERVER_ADDR"], SzUtility::getServerHost());
    }

    /**
     * @see SzUtility::getClientHost
     */
    public function test_GetClientHost()
    {
        $_SERVER["REMOTE_ADDR"] = '127.0.0.1';
        $this->assertEquals($_SERVER["REMOTE_ADDR"], SzUtility::getClientHost());
    }

    /**
     * @see SzUtility::checkArrayKey
     */
    public function test_CheckArrayKey()
    {
        $checkArray = array(
            10086 => 1,
            10087 => 2,
            10088 => 3,
        );

        $checkKey = 10086;
        $this->assertEquals(true, SzUtility::CheckArrayKey($checkKey, $checkArray));

        $checkKey = array(10086,10087);
        $this->assertEquals(true, SzUtility::CheckArrayKey($checkKey, $checkArray));
    }

    /**
     * @see SzUtility::consistentHash
     */
    public function test_ConsistentHash()
    {
        $this->assertEquals(0, SzUtility::consistentHash(10086, 2));
    }

    /**
     * @see SzUtility::explodeWithTrim
     */
    public function test_ExplodeWithTrim()
    {
        $this->assertEquals(array('Page', 'Index'), SzUtility::explodeWithTrim('.', "Page.Index"));
    }

    /**
     * @see SzUtility::escape
     */
    public function test_Escape()
    {
        $this->assertEquals('Hello, World', SzUtility::escape('<p>Hello, World</p>'));
    }

    /**
     * @see SzUtility::convertStringToBoolean
     */
    public function test_ConvertStringToBoolean()
    {
        $this->assertEquals(true, SzUtility::convertStringToBoolean('TRUE'));
        $this->assertEquals(false, SzUtility::convertStringToBoolean('FALSE'));
    }

    /**
     * @see SzUtility::buildQueryString
     */
    public function test_BuildQueryString()
    {
        $param = array(
            'dev' => 'fengjie',
            'ver' => 'latest'
        );
        $this->assertEquals("dev=fengjie&ver=latest", SzUtility::buildQueryString($param, true));
    }

    /**
     * @see SzUtility::parseUrl
     */
    public function test_ParseUrl()
    {
        $url = 'http://dev-flowershop2.shinezone.com/fs2admin#/api';
        $parse = array(
            'scheme'    => 'http',
            'host'      => 'dev-flowershop2.shinezone.com',
            'path'      => '/fs2admin',
            'fragment'  => '/api',
            'port'      => '80',
        );

        $this->assertEquals($parse, SzUtility::parseUrl($url));
    }

    /**
     * @see SzUtility::getRandomElementByProbability
     */
    public function test_GetRandomElementByProbability()
    {
        $probabilityList = array(
            '51%'    => 51,
            '49%'    => 49,
        );
        $this->assertTrue(SzUtility::checkArrayKey(SzUtility::getRandomElementByProbability($probabilityList), $probabilityList));
    }

    /**
     * @see SzUtility::getRandomIntFromRange
     */
    public function test_GetRandomIntFromRange()
    {
        $randomRange = '0,10';
        $randomRangeArray = array(0,1,2,3,4,5,6,7,8,9,10);
        $this->assertTrue(SzUtility::checkArrayKey(SzUtility::getRandomIntFromRange($randomRange), $randomRangeArray));
    }

    /**
     * @see SzUtility::calcPercentageRate
     */
    public function test_CalcPercentageRate()
    {
        $this->assertTrue(SzUtility::calcPercentageRate(100));
        $this->assertFalse(SzUtility::calcPercentageRate(0));
    }

    /**
     * @see SzUtility::wordHumanize
     */
    public function test_WordHumanize()
    {
        $this->assertEquals('Hello World', SzUtility::wordHumanize('hello_world'));
    }

    /**
     * @see SzUtility::wordCamelize
     */
    public function test_WordCamelize()
    {
        $this->assertEquals('helloWorld', SzUtility::wordCamelize('hello_world'));
        $this->assertEquals('HelloWorld', SzUtility::wordCamelize('hello_world', true));
    }

    /**
     * @see SzUtility::wordUnderscore
     */
    public function test_wordUnderscore()
    {
        $this->assertEquals('hello_world', SzUtility::wordUnderscore('helloWorld'));
    }

    /**
     * @see SzUtility::genMd5
     */
    public function test_genMd5()
    {
        $this->assertEquals(md5('hello world'), SzUtility::genMd5('hello world'));
    }

    /**
     * @see SzUtility::strUtf8CharLen
     */
    public function test_strUtf8CharLen()
    {
        $this->assertEquals(4, SzUtility::strUtf8CharLen('你好世界'));
        $this->assertEquals(11, SzUtility::strUtf8CharLen('hello world'));
    }

    /**
     * @see SzUtility::strLen
     */
    public function test_strLen()
    {
        $this->assertEquals(12, SzUtility::strLen('你好世界'));
        $this->assertEquals(11, SzUtility::strLen('hello world'));
    }

    /**
     * @see SzUtility::calcExpUpdate
     */
    public function test_calcExpUpdate_without_continuousMode()
    {
        $level  = 1;
        $exp    = 100;
        $expLadder = array(
            2 => array('level' => 2, 'exp' => 20),
            3 => array('level' => 3, 'exp' => 30),
            4 => array('level' => 4, 'exp' => 40),
            5 => array('level' => 5, 'exp' => 50),
        );
        SzUtility::calcExpUpdate($level, $exp, $expLadder);

        $this->assertEquals(4, $level);
        $this->assertEquals(10, $exp);
    }


    /**
     * @see SzUtility::calcExpUpdate
     */
    public function test_calcExpUpdate_with_continuousMode()
    {
        $level  = 1;
        $exp    = 100;
        $expLadder = array(
            2 => array('level' => 2, 'exp' => 20),
            3 => array('level' => 3, 'exp' => 50),
            4 => array('level' => 4, 'exp' => 90),
            5 => array('level' => 5, 'exp' => 150),
        );
        SzUtility::calcExpUpdate($level, $exp, $expLadder, true);

        $this->assertEquals(4, $level);
        $this->assertEquals(100, $exp);
    }

    /**
     * @see SzUtility::handleLevelUpBonus
     */
    public function test_HandleLevelUpBonus()
    {
        $originLevel  = 1;
        $finalLevel   = 3;
        $bonusConfigs = array(
            2 => array('level' => 2, 'reward' => array(10001 => 1, 10002 => 1)),
            3 => array('level' => 3, 'reward' => array(10001 => 1, 10002 => 1)),
            4 => array('level' => 4, 'reward' => array(10001 => 1, 10002 => 1)),
        );
        $this->assertEquals(array(10001 => 2, 10002 => 2), SzUtility::handleLevelUpBonus($originLevel, $finalLevel, $bonusConfigs));
    }

    /**
     * @see SzUtility::getTimeZoneOffset
     */
    public function test_GetTimeZoneOffset()
    {
        $this->assertEquals(28800, SzUtility::getTimeZoneOffset('Asia/Shanghai'));
        $this->assertEquals(-28800, SzUtility::getTimeZoneOffset('GMT', 'Asia/Shanghai'));
    }

    /**
     * @see SzUtility::refreshRegularlyGeneratedResourceCount
     */
    public function test_RefreshRegularlyGeneratedResourceCount()
    {
        list($info, $lastChargedTime) = SzUtility::refreshRegularlyGeneratedResourceCount(
            100,
            1388505600, // 2014-01-01 00:00:00
            200,
            3600,
            1,
            1388592010 // 2014-01-02 00:00:10
        );

        $this->assertEquals(124, $info);
        $this->assertEquals(1388592000, $lastChargedTime);
    }

    /**
     * @see SzUtility::getProtocol
     */
    public function test_GetProtocol()
    {
        $_SERVER['HTTPS'] = 'on';
        $this->assertEquals('https:', SzUtility::getProtocol());

        $_SERVER['HTTPS'] = 'off';
        $this->assertEquals('http:', SzUtility::getProtocol());
    }

    /**
     * @see SzUtility::getCurrentCycleCoolDownCount
     */
    public function test_GetCurrentCycleCoolDownCount()
    {
        $this->assertEquals(24, SzUtility::getCurrentCycleCoolDownCount(1388592010, 1388505600, 3600));
    }

    /**
     * @see SzUtility::getNextCycleCoolDownTimeViaGivenTime
     */
    public function test_GetNextCycleCoolDownTimeViaGivenTime()
    {
        $this->assertEquals(1388592000 + 3600, SzUtility::getNextCycleCoolDownTimeViaGivenTime(1388592010, 1388505600, 3600));
    }
}