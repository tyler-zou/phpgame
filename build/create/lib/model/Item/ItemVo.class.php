<?php
class ItemVo extends SzAbstractMySqlVo
{

    private $itemId;
    private $userId;
    private $itemDefId;
    private $type;
    private $count;
    private $expireTime;
    private $updateTime;

    /**
     * Initialize ItemVo.
     *
     * @param $itemId
     * @param $userId
     * @param $itemDefId
     * @param $type
     * @param $count
     * @param $expireTime
     * @param $updateTime
     * @param boolean $isInsert default false, means for now this vo is initialized not for insert process
     * @return ItemVo
     */
    public function __construct($itemId, $userId, $itemDefId, $type, $count, $expireTime, $updateTime, $isInsert = false) {
        $this->itemId = $itemId;
        $this->userId = $userId;
        $this->itemDefId = $itemDefId;
        $this->type = $type;
        $this->count = $count;
        $this->expireTime = $expireTime;
        $this->updateTime = $updateTime;

        $this->isInsert = $isInsert;
        $this->voClassName = 'ItemVo';
        $this->ormName = 'Item';
    }

    /**
     * @see SzAbstractVo::toArray
     */
    public function toArray($needEncode = false)
    {
        return array(
            'itemId' => $this->getItemId(),
            'userId' => $this->getUserId(),
            'itemDefId' => $this->getItemDefId(),
            'type' => $this->getType(),
            'count' => $this->getCount(),
            'expireTime' => $this->getExpireTime(),
            'updateTime' => $this->getUpdateTime(),
        );
    }

    /**
     * @see SzAbstractVo::toEntireArray
     */
    public function toEntireArray($needEncode = false)
    {
        return array(
            'itemId' => $this->getItemId(),
            'userId' => $this->getUserId(),
            'itemDefId' => $this->getItemDefId(),
            'type' => $this->getType(),
            'count' => $this->getCount(),
            'expireTime' => $this->getExpireTime(),
            'updateTime' => $this->getUpdateTime(),
        );
    }

    /**
     * @see SzAbstractVo::toPureArray
     */
    public function toPureArray()
    {
        return array(
            $this->getItemId(),
            $this->getUserId(),
            $this->getItemDefId(),
            $this->getType(),
            $this->getCount(),
            $this->getExpireTime(),
            $this->getUpdateTime(),
        );
    }

    public function getItemId()
    {
        return $this->itemId;
    }

    public function setItemId($val)
    {
        $this->saveColumnStatus(0, $this->itemId);
        $this->itemId = $val;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($val)
    {
        $this->saveColumnStatus(1, $this->userId);
        $this->userId = $val;
    }

    public function getItemDefId()
    {
        return $this->itemDefId;
    }

    public function setItemDefId($val)
    {
        $this->saveColumnStatus(2, $this->itemDefId);
        $this->itemDefId = $val;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($val)
    {
        $this->saveColumnStatus(3, $this->type);
        $this->type = $val;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setCount($val)
    {
        $this->saveColumnStatus(4, $this->count);
        $this->count = $val;
    }

    public function getExpireTime()
    {
        return $this->expireTime;
    }

    public function setExpireTime($val)
    {
        $this->saveColumnStatus(5, $this->expireTime);
        $this->expireTime = $val;
    }

    public function getUpdateTime()
    {
        return $this->updateTime;
    }

    public function setUpdateTime($val)
    {
        $this->saveColumnStatus(6, $this->updateTime);
        $this->updateTime = $val;
    }

}