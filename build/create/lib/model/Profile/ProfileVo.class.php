<?php
class ProfileVo extends SzAbstractMySqlVo
{

    private $userId;
    private $level;
    private $exp;
    private $money;
    private $energy;
    private $energyLimit;
    private $lastEnergyChargedTime;
    private $lastLoginTime;

    /**
     * Initialize ProfileVo.
     *
     * @param $userId
     * @param $level
     * @param $exp
     * @param $money
     * @param $energy
     * @param $energyLimit
     * @param $lastEnergyChargedTime
     * @param $lastLoginTime
     * @param boolean $isInsert default false, means for now this vo is initialized not for insert process
     * @return ProfileVo
     */
    public function __construct($userId, $level, $exp, $money, $energy, $energyLimit, $lastEnergyChargedTime, $lastLoginTime, $isInsert = false) {
        $this->userId = $userId;
        $this->level = $level;
        $this->exp = $exp;
        $this->money = $money;
        $this->energy = $energy;
        $this->energyLimit = $energyLimit;
        $this->lastEnergyChargedTime = $lastEnergyChargedTime;
        $this->lastLoginTime = $lastLoginTime;

        $this->isInsert = $isInsert;
        $this->voClassName = 'ProfileVo';
        $this->ormName = 'Profile';
    }

    /**
     * @see SzAbstractVo::toArray
     */
    public function toArray($needEncode = false)
    {
        return array(
            'userId' => $this->getUserId(),
            'level' => $this->getLevel(),
            'exp' => $this->getExp(),
            'money' => $this->getMoney(),
            'energy' => $this->getEnergy(),
            'energyLimit' => $this->getEnergyLimit(),
            'lastEnergyChargedTime' => $this->getLastEnergyChargedTime(),
            'lastLoginTime' => $this->getLastLoginTime(),
        );
    }

    /**
     * @see SzAbstractVo::toEntireArray
     */
    public function toEntireArray($needEncode = false)
    {
        return array(
            'userId' => $this->getUserId(),
            'level' => $this->getLevel(),
            'exp' => $this->getExp(),
            'money' => $this->getMoney(),
            'energy' => $this->getEnergy(),
            'energyLimit' => $this->getEnergyLimit(),
            'lastEnergyChargedTime' => $this->getLastEnergyChargedTime(),
            'lastLoginTime' => $this->getLastLoginTime(),
        );
    }

    /**
     * @see SzAbstractVo::toPureArray
     */
    public function toPureArray()
    {
        return array(
            $this->getUserId(),
            $this->getLevel(),
            $this->getExp(),
            $this->getMoney(),
            $this->getEnergy(),
            $this->getEnergyLimit(),
            $this->getLastEnergyChargedTime(),
            $this->getLastLoginTime(),
        );
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($val)
    {
        $this->saveColumnStatus(0, $this->userId);
        $this->userId = $val;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($val)
    {
        $this->saveColumnStatus(1, $this->level);
        $this->level = $val;
    }

    public function getExp()
    {
        return $this->exp;
    }

    public function setExp($val)
    {
        $this->saveColumnStatus(2, $this->exp);
        $this->exp = $val;
    }

    public function getMoney()
    {
        return $this->money;
    }

    public function setMoney($val)
    {
        $this->saveColumnStatus(3, $this->money);
        $this->money = $val;
    }

    public function getEnergy()
    {
        return $this->energy;
    }

    public function setEnergy($val)
    {
        $this->saveColumnStatus(4, $this->energy);
        $this->energy = $val;
    }

    public function getEnergyLimit()
    {
        return $this->energyLimit;
    }

    public function setEnergyLimit($val)
    {
        $this->saveColumnStatus(5, $this->energyLimit);
        $this->energyLimit = $val;
    }

    public function getLastEnergyChargedTime()
    {
        return $this->lastEnergyChargedTime;
    }

    public function setLastEnergyChargedTime($val)
    {
        $this->saveColumnStatus(6, $this->lastEnergyChargedTime);
        $this->lastEnergyChargedTime = $val;
    }

    public function getLastLoginTime()
    {
        return $this->lastLoginTime;
    }

    public function setLastLoginTime($val)
    {
        $this->saveColumnStatus(7, $this->lastLoginTime);
        $this->lastLoginTime = $val;
    }

}