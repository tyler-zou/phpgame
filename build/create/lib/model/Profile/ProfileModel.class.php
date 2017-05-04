<?php
class ProfileModel extends SzMySqlModel
{

    /**
     * @see SzAbstractModel::$ORM_NAME
     * @var string
     */
    public static $ORM_NAME = 'Profile';

    /**
     * Initialize the ProfileModel.
     *
     * @return ProfileModel
     */
    public function __construct()
    {
        $this->table = 'profile';
        $this->columns = array('userId', 'level', 'exp', 'money', 'energy', 'energyLimit', 'lastEnergyChargedTime', 'lastLoginTime', );
        $this->autoIncrColumn = null;
        $this->diffUpColumns = array(1, 2, 3, 4, );
        $this->updateFilter = array(0, );
        $this->toArrayFilter = array();
        $this->searchColumns = array(0, );
        $this->updateColumns = array(0, );
        $this->jsonColumns = array();
        $this->cacheColumn = 0;
        $this->shardColumn = 0;
        $this->pkColumn = 0;
        $this->deleteColumns = array(0, );
        $this->cacheTime = null;

        $this->ormName = 'Profile';
        $this->columnCount = 8;
        $this->isList = false;
        $this->dbType = 'MySql';
        $this->voClassName = 'ProfileVo';
        $this->voListClassName = 'ProfileVoList';

        parent::__construct();
    }

}