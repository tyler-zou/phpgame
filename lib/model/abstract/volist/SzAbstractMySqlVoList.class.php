<?php
class SzAbstractMySqlVoList extends SzAbstractVoList
{

    /**
     * @see SzAbstractVoList::__construct
     */
    public function __construct($list)
    {
        parent::__construct($list);
    }

    /**
     * @see SzAbstractVoList::persistInsertList
     */
    protected function persistInsertList($model, &$responses)
    {
        if (!$this->insertList) { // nothing to do with insert action
            return;
        }

        /* @var SzMySqlModel $model */
        if ($model->hasAutoIncrementId()) {
            // model has auto increment id
            if (count($this->insertList) > $this->batchInsertThreshold) {
                // use batch insert
                $dbResult = $model->insert($this->insertList);
                if ($dbResult[0] > 0) {
                    $shardKey = $this->getShardKeyFromList($model, $this->insertList);
                    if (!$shardKey) {
                        throw new SzException(10509, $model->getOrmName());
                    }
                    $selectedList = $model->select($shardKey)->getList(); // select all to filter new inserted data
                    if (count($this->list) == 0) {
                        // empty list, set to cache & $this->list directly
                        foreach ($selectedList as $vo) {
                            /* @var SzAbstractVo $vo */
                            $responses[] = $vo->buildResponse($model, true);
                        }
                        $model->setListOfVoCache($selectedList, $selectedList);
                        $this->list = $this->formatList($selectedList);
                    } else {
                        $newInserted = array();
                        foreach ($selectedList as $pkValue => $vo) {
                            /* @var SzAbstractVo $vo */
                            if (!$this->searchElementExists($vo, $model, $pkValue)) {
                                $responses[] = $vo->buildResponse($model, true);
                                $newInserted[] = $vo;
                            }
                        }
                        if ($newInserted) {
                            $model->setListOfVoCache($newInserted, $selectedList);
                        }
                        $this->list = $this->formatList($selectedList);
                    }
                }
            } else {
                // loop single insert
                foreach ($this->insertList as $vo) {
                    /* @var SzAbstractVo $vo */
                    $dbResult = $model->insert($vo);
                    if ($dbResult[0] > 0 && $dbResult[1]) {
                        $model->setColumnValue($vo, $model->getAutoIncrColumn(), $dbResult[1]);
                        $responses[] = $vo->buildResponse($model, true);
                        $vo->clearStatusChangeInfo();
                        $this->setVoInList($vo, $model);
                    }
                }
                $model->setListOfVoCache($this->insertList, $this->list);
            }
        } else {
            // model does not have auto increment id
            if (count($this->insertList) > $this->batchInsertThreshold) {
                // use batch insert
                $dbResult = $model->insert($this->insertList);
                if ($dbResult[0] > 0) {
                    foreach ($this->insertList as $vo) {
                        /* @var SzAbstractVo $vo */
                        $responses[] = $vo->buildResponse($model, true);
                        $vo->clearStatusChangeInfo();
                        $this->setVoInList($vo, $model);
                    }
                }
            } else {
                // loop single insert
                foreach ($this->insertList as $vo) {
                    /* @var SzAbstractVo $vo */
                    $dbResult = $model->insert($vo);
                    if ($dbResult[0] > 0) {
                        $responses[] = $vo->buildResponse($model, true);
                        $vo->clearStatusChangeInfo();
                        $this->setVoInList($vo, $model);
                    }
                }
            }
            $model->setListOfVoCache($this->insertList, $this->list);
        }

        $this->insertList = array();
    }

    /**
     * @see SzAbstractVoList::persistUpdateList
     */
    protected function persistUpdateList($model, &$responses)
    {
        if (!$this->updateList) {
            return;
        }

        /* @var SzMySqlModel $model */
        foreach ($this->updateList as $updateId => $vo) {
            /* @var SzAbstractVo $vo */
            $dbResult = $model->update($vo);
            if ($dbResult[0] > 0) {
                $responses[] = $vo->buildResponse($model);
                $vo->clearStatusChangeInfo();
                $this->setVoInList($vo, $model, $updateId);
                $this->updateList[$updateId] = $vo;
            } else {
                unset($this->updateList[$updateId]);
            }
        }
        $model->setListOfVoCache($this->updateList, $this->list);
        $this->updateList = array();
    }

    /**
     * @see SzAbstractVoList::persistDeleteList
     */
    protected function persistDeleteList($model, &$responses)
    {
        if (!$this->deleteList) {
            return;
        }

        foreach ($this->deleteList as $deleteId => $vo) {
            /* @var SzAbstractVo $vo */
            $responses[] = $vo->buildResponse($model, false, true);
        }

        /* @var SzMySqlModel $model */
        $dbResult = $model->delete($this->deleteList);
        if ($dbResult[0] > 0) {
            $model->deleteListOfVoCache($this->deleteList);
        }

        $this->deleteList = array();
    }

}