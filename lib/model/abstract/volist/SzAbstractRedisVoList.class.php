<?php
class SzAbstractRedisVoList extends SzAbstractVoList
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
        if (!$this->insertList) {
            return;
        }

        /* @var SzRedisModel $model */
        if ($model->hasAutoIncrementId()) {
            $endOfNewIds = $model->genAutoIncrId(count($this->insertList));
            if (!is_numeric($endOfNewIds)) {
                throw new SzException(10510, $endOfNewIds);
            }
            foreach ($this->insertList as $vo) {
                /* @var SzAbstractVo $vo */
                $model->setColumnValue($vo, $model->getShardColumn(), $endOfNewIds);
                $responses[] = $vo->buildResponse($model, true);
                $vo->clearStatusChangeInfo();
                --$endOfNewIds;
                $this->setVoInList($vo, $model);
            }
            $model->save($this->insertList);
        } else {
            foreach ($this->insertList as $vo) {
                /* @var SzAbstractVo $vo */
                $responses[] = $vo->buildResponse($model, true);
                $vo->clearStatusChangeInfo();
                $this->setVoInList($vo, $model);
            }
            $model->save($this->insertList);
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

        /* @var SzRedisModel $model */
        foreach ($this->updateList as $updateId => $vo) {
            /* @var SzAbstractVo $vo */
            $responses[] = $vo->buildResponse($model, true);
            $vo->clearStatusChangeInfo();
            $this->setVoInList($vo, $model, $updateId);
        }
        $model->save($this->updateList);
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

        /* @var SzRedisModel $model */
        foreach ($this->deleteList as $deleteId => $vo) {
            /* @var SzAbstractVo $vo */
            $vo->clearStatusChangeInfo();
            $responses[] = $vo->buildResponse($model, false, true);
        }
        $model->delete($this->deleteList);
        $this->deleteList = array();
    }

}