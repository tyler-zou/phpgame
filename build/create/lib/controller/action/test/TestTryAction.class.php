<?php
class TestTryAction extends SzAbstractAction
{

    protected $paramTypes = array(
        self::TYPE_STRING,
        self::TYPE_STRING,
    );

    public function execute($firstName, $lastName)
    {
        $userId = 56898832;

        /**
         * @var ProfileVo $profile
         * @var ItemVoList $itemList
         * @var ItemVo $item
         */
//        $profile = new ProfileVo($userId, 1, 0, 12, 100, 100, SzTime::getTime(), SzTime::getTime(), true);
//        SzPersister::get()->setVo($userId, $profile);
//
//        $itemList = new ItemVoList(array());
//        $itemList->addElement(new ItemVo(null, $userId, 1, 1, 10, SzTime::getTime() + SzTime::DAY7, SzTime::getTime()));
//        SzPersister::get()->setVoList($userId, $itemList);

//        $profile = SzPersister::get()->getVo($userId, ProfileModel::$ORM_NAME);
//        $profile->setExp($profile->getExp() + 100);
//        SzPersister::get()->setVo($userId, $profile);
//
//        $itemList = SzPersister::get()->getVoList($userId, ItemModel::$ORM_NAME);
//        $item = $itemList->getElement(1);
//        $item->setCount($item->getCount() + 11);
//        $itemList->updateElement(1, $item);
//        SzPersister::get()->setVoList($userId, $itemList);

        $body = array(
            'firstName' => $firstName,
            'lastName' => $lastName,
            'identify' => SzUuid::genUuid(),
        );

        return $this->buildResponse($body);
    }

}