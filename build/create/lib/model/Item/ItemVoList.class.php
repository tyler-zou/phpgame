<?php
class ItemVoList extends SzAbstractMySqlVoList
{

    /**
     * Initialize ItemVoList.
     *
     * @param array $list array of ItemVo
     * @return ItemVoList
     */
    public function __construct($list)
    {
        parent::__construct($list);

        $this->listClassName = 'ItemVoList';
        $this->ormName = 'Item';
    }

}