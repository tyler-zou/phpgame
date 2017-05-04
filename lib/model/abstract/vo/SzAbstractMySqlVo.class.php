<?php
abstract class SzAbstractMySqlVo extends SzAbstractVo
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
        /* @var SzMySqlModel $model */
        $model = SzPersister::get()->getModel($this->getOrmName());

        if ($model->hasAutoIncrementId()
            && is_null($model->getColumnValue($this, $model->getAutoIncrColumn()))) {
            // INSERT
            $dbResult = $model->insert($this);
            if ($dbResult[0] > 0) {
                $model->setColumnValue($this, $model->getAutoIncrColumn(), $dbResult[1]);
            }
        } else if ($this->isInsert()) {
            // INSERT
            $dbResult = $model->insert($this);
        } else {
            // UPDATE
            $dbResult = $model->update($this);
        }

        if ($dbResult[0] > 0) { // db operation succeeded
            // build response
            if ($this->isInsert()) {
                $response = $this->buildResponse($model, true);
            } else {
                $response = $this->buildResponse($model);
            }
            // update cache
            $model->setVoCache($this);
        }

        return $response;
    }

}