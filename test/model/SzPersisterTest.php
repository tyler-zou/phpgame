<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzPersisterTest extends SzTestAbstract
{
    /**
     * @var SzPersister
     */
    protected static $instance;

    protected static $userId = 60;
    protected static $itemDefId = 10001;

    public function setUp()
    {
        self::$instance = SzPersister::get();
    }

    /**
     * @see SzPersister::get
     */
    public function test_Get()
    {
        $this->assertTrue(self::$instance instanceof SzPersister);
    }

    /**
     * @see SzPersister::setVoList
     */
    public function test_SetVoList()
    {
        /** @var ItemVoList $itemVoList */
        $itemVoList = self::$instance->getVoList(self::$userId, 'Item');
        $itemVo = new ItemVo(999999, self::$userId, self::$itemDefId, 1, 1, 0, 0, true);
        $itemVoList->addElement($itemVo);
        self::$instance->setVoList($itemVoList);

        $this->assertTrue(array_pop($this->getPropertyValue('SzPersister', self::$instance, 'persistenceList')) instanceof ItemVoList);
    }

    /**
     * @see SzPersister::getVoList
     */
    public function test_GetVoList()
    {
        /** @var ItemVoList $itemVoList */
        $itemVoList = self::$instance->getVoList(self::$userId, 'Item');
        $this->assertTrue($itemVoList instanceof ItemVoList);
    }

    /**
     * @see SzPersister::getVoList
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10514
     */
    public function test_GetVoList_Error_10514()
    {
        self::$instance->getVoList(self::$userId, 'Profile');
    }

    /**
     * @see SzPersister::setVo
     */
    public function test_SetVo()
    {
        /** @var ProfileVo $profileVo */
        $profileVo = new ProfileVo(
            self::$userId, // user id
            1, // level
            0, // exp
            0,  // money
            0, // energy
            0, // energy limit
            0,  // last energy charged time
            0,  // last login
            true // insert action
        );
        self::$instance->setVo($profileVo);

        $this->assertTrue(array_pop($this->getPropertyValue('SzPersister', self::$instance, 'persistenceList')) instanceof ProfileVo);
    }

    /**
     * @see SzPersister::setVo
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10519
     */
    public function test_SetVo_Error_10519()
    {
        /** @var ItemVo $itemVoList */
        $itemVoList = self::$instance->getVoList(self::$userId, 'Item');
        self::$instance->setVo($itemVoList);
    }

    /**
     * @see SzPersister::getVo
     */
    public function test_GetVo()
    {
        /** @var ProfileVo $profileVo */
        $profileVo = self::$instance->getVo(self::$userId, 'Profile');
        $this->assertTrue($profileVo instanceof ProfileVo);
    }

    /**
     * @see SzPersister::getVo
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10521
     */
    public function test_GetVo_Error_10521()
    {
        self::$instance->getVo(self::$userId, 'Item');
    }

    /**
     * @see SzPersister::persist
     */
    public function test_Persist()
    {
        self::$instance->persist();
        $this->assertTrue(true);
    }

    /**
     * @see SzPersister::addManualPersistData
     */
    public function test_AddManualPersistData()
    {
        $itemId = 999990;
        $itemVo = new ItemVo($itemId, self::$userId, self::$itemDefId, 1, 1, 0, 0, true);

        $reflector = $this->setMethodPublic('SzPersister', 'addManualPersistData');
        $reflector->invoke(self::$instance, $itemVo);

        $manualPersistList = $this->getPropertyValue('SzPersister', self::$instance, 'manualPersistList');
        $itemVo = array_pop($manualPersistList);
        $this->assertEquals($itemId, $itemVo->getItemId());
    }

    /**
     * @see SzPersister::getManualPersistData
     */
    public function test_GetManualPersistData()
    {
        $itemId = 999990;
        /**
         * @var ItemVo $itemVo
         */
        $manualPersistList = self::$instance->getManualPersistData();
        $itemVo = array_pop($manualPersistList);
        $this->assertEquals($itemId, $itemVo->getItemId());
    }
    /**
     * @see SzPersister::addResponse
     */
    public function test_AddResponse()
    {
        $itemId = 999991;
        $itemVo = new ItemVo($itemId, self::$userId, self::$itemDefId, 2, 1, 0, 0, true);
        self::$instance->addManuallyInsertedResponse($itemVo, self::$userId, $itemId);

        /**
         * @var ItemVo $itemVo
         */
        $responseList   = $this->getPropertyValue('SzPersister', self::$instance, 'responseList');
        $itemListVo     = array_pop($responseList);
        $itemVo         = array_pop($itemListVo);

        $this->assertEquals($itemId, $itemVo[$itemId]['itemId']);
    }

    /**
     * @see SzPersister::getResponseList
     */
    public function test_GetResponseList()
    {
        $itemId = 999991;

        /**
         * @var ItemVo $itemVo
         */
        $responseList   = self::$instance->getResponseList();
        $itemListVo     = array_pop($responseList);
        $itemVo         = array_pop($itemListVo);

        $this->assertEquals($itemId, $itemVo[$itemId]['itemId']);
    }

    /**
     * @see SzPersister::getModel
     */
    public function test_GetModel()
    {
        $itemMode = self::$instance->getModel('Item');
        $this->assertTrue($itemMode instanceof ItemModel);
    }

    /**
     * @see SzPersister::getModel
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10513
     */
    public function test_GetModel_Error_10002()
    {
        self::$instance->getModel('ItemError');
    }
}