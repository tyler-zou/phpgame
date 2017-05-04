<?php
abstract class SzAbstractRedisVo extends SzAbstractVo
{

    /**
     * @see SzAbstractVo::persist
     */
    public function persist()
    {
        if (!$this->isChanged() && !$this->isInsert()) {
            // nothing to persist
            return false;
        }

        $response = null;
        /* @var SzRedisModel $model */
        $model = SzPersister::get()->getModel($this->getOrmName());

        if ($model->hasAutoIncrementId()
            && is_null($model->getColumnValue($this, $model->getAutoIncrColumn()))) {
            // INSERT
            $newAutoIncrId = $model->genAutoIncrId();
            $model->setColumnValue($this, $model->getAutoIncrColumn(), $newAutoIncrId);
        }

        // build response
        $response = $this->buildResponse($model);

        // persist in redis
        $model->save($this);

        return $response;
    }

}