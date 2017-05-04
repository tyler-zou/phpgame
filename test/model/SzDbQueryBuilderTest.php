<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';
require_once dirname(dirname(__DIR__)) . '/test/model/mock/SzAbstractDbFactoryMock.class.php';

class SzDbQueryBuilderTest extends SzTestAbstract
{
    /**
     * @var SzDbQueryBuilder
     */
    protected static $instance;

    public function setUp()
    {
        $class = new ReflectionClass('SzDbQueryBuilder');
        self::$instance = $class->newInstanceWithoutConstructor();

        $dbFactory = new SzAbstractDbFactoryMock();
        self::setPropertyValue('SzDbQueryBuilder', self::$instance, 'connection', $dbFactory->getDb());
    }

    /**
     * @see SzDbQueryBuilder::insertQuery
     */
    public function test_InsertQuery()
    {
        $columns    = array('userId', 'itemDefId', 'type', 'count', 'expireTime', 'updateTime');
        $values     = array('1', '10001', '1', '1', '0', '0');

        $this->assertEquals(
            "INSERT INTO `frame0`.`item` (`userId`, `itemDefId`, `type`, `count`, `expireTime`, `updateTime`) VALUES (1, 10001, 1, 1, 0, 0);",
            self::$instance->insertQuery('frame0', 'item', $columns, $values)
        );
    }

    /**
     * @see SzDbQueryBuilder::insertBatchQuery
     */
    public function test_InsertBatchQuery()
    {
        $columns    = array('userId', 'itemDefId', 'type', 'count', 'expireTime', 'updateTime');
        $values     = array(array('1', '10001', '1', '1', '0', '0'), array('1', '10001', '1', '1', '0', '0'));

        $this->assertEquals(
            "INSERT INTO `frame0`.`item` (`userId`, `itemDefId`, `type`, `count`, `expireTime`, `updateTime`) VALUES (1, 10001, 1, 1, 0, 0), (1, 10001, 1, 1, 0, 0);",
            self::$instance->insertBatchQuery('frame0', 'item', $columns, $values)
        );
    }

    /**
     * @see SzDbQueryBuilder::deleteQuery
     */
    public function test_DeleteQuery()
    {
        $wheres    = array('userId' => 1);

        $this->assertEquals(
            "DELETE FROM `frame0`.`item` WHERE `userId` = 1;",
            self::$instance->deleteQuery('frame0', 'item', $wheres)
        );
    }

    /**
     * @see SzDbQueryBuilder::selectQuery
     */
    public function test_SelectQuery()
    {
        $wheres    = array('userId' => 1);
        $columns   = array('userId', 'itemDefId', 'type', 'count', 'expireTime', 'updateTime');

        $this->assertEquals(
            "SELECT `userId`, `itemDefId`, `type`, `count`, `expireTime`, `updateTime` FROM `frame0`.`item` WHERE `userId` = 1;",
            self::$instance->selectQuery('frame0', 'item', $columns, $wheres)
        );
    }

    /**
     * @see SzDbQueryBuilder::updateQuery
     */
    public function test_UpdateQuery()
    {
        $wheres    = array('userId' => 1);
        $columns   = array('count', 'expireTime', 'updateTime');
        $values    = array('1', '0', '0');

        $this->assertEquals(
            "UPDATE `frame0`.`item` SET `count` = 1, `expireTime` = 0, `updateTime` = 0 WHERE `userId` = 1;",
            self::$instance->updateQuery('frame0', 'item', $columns, $values, null, $wheres)
        );
    }

    /**
     * @see SzDbQueryBuilder::updateQuery
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10602
     */
    public function test_UpdateQuery_Error_10602()
    {
        $wheres    = array('userId' => 1);
        $columns   = array('count', 'expireTime', 'updateTime');
        $values    = array('1', '0');

        $this->assertEquals(
            "UPDATE `frame0`.`item` SET `count` = 1, `expireTime` = 0, `updateTime` = 0 WHERE `userId` = 1;",
            self::$instance->updateQuery('frame0', 'item', $columns, $values, null, $wheres)
        );
    }

    /**
     * @see SzDbQueryBuilder::handleWhere
     */
    public function test_HandleWhere()
    {
        $reflector = $this->setMethodPublic('SzDbQueryBuilder', 'handleWhere');

        $wheres    = array('userId' => 1, 'itemDefId' => array(1, 2, 3));
        $this->assertEquals('WHERE `userId` = 1 AND `itemDefId` IN (1, 2, 3)', $reflector->invoke(self::$instance, $wheres));
    }

    /**
     * @see SzDbQueryBuilder::handleLimit
     */
    public function test_HandleLimit()
    {
        $reflector = $this->setMethodPublic('SzDbQueryBuilder', 'handleLimit');

        $argus = 10;
        $this->assertEquals('LIMIT 10', $reflector->invoke(self::$instance, $argus));
    }

    /**
     * @see SzDbQueryBuilder::handleGroup
     */
    public function test_HandleGroup()
    {
        $reflector = $this->setMethodPublic('SzDbQueryBuilder', 'handleGroup');

        $argus = 'userId';
        $this->assertEquals('GROUP BY `userId`', $reflector->invoke(self::$instance, $argus));

        $argus = array('userId', 'type');
        $this->assertEquals('GROUP BY `userId`, `type`', $reflector->invoke(self::$instance, $argus));
    }

    /**
     * @see SzDbQueryBuilder::handleOrder
     */
    public function test_HandleOrder()
    {
        $reflector = $this->setMethodPublic('SzDbQueryBuilder', 'handleOrder');

        $argus = 'userId';
        $this->assertEquals('ORDER BY `userId` ASC', $reflector->invoke(self::$instance, $argus, true));

        $argus = array('userId', 'type');
        $this->assertEquals('ORDER BY `userId`, `type` DESC', $reflector->invoke(self::$instance, $argus, false));
    }
}