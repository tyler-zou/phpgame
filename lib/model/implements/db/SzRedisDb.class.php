<?php
class SzRedisDb extends SzAbstractDb
{

    /**
     * flag whether current redis instance has started multi transaction or not
     *
     * @var boolean
     */
    protected $multiEnabled = false;

    /**
     * @see SzAbstractDb::connect
     */
    protected function connect($host, $port, $userName = null, $password = null, $dbName = null)
    {
        $handle = new Redis();

        $isConnected = $handle->connect($host, $port);
        if (!$isConnected) {
            throw new SzException(10503, array($host, $port));
        }
        $handle->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE); // no serialize

        return $handle;
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* API FUNCTIONS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* BASIC FUNCTIONS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * @see Redis::set
     * @see Redis::setex
     */
    public function set($key, $value, $timeout = null)
    {
        $result = null;
        if (is_null($timeout)) {
            $result = $this->writeHandle->set($key, $value);
        } else {
            $result = $this->writeHandle->setex($key, $timeout, $value);
        }
        return $result;
    }

    /**
     * @see Redis::get
     */
    public function get($key)
    {
        return $this->readHandle->get($key);
    }

    /**
     * @see Redis::delete
     */
    public function delete($keys)
    {
        return $this->writeHandle->delete($keys);
    }

    /**
     * @see Redis::incr
     * @see Redis::incrBy
     */
    public function incr($key, $value = 1)
    {
        $result = null;
        if ($value == 1) {
            $result = $this->writeHandle->incr($key);
        } else {
            $result = $this->writeHandle->incrBy($key, $value);
        }
        return $result;
    }

    /**
     * @see Redis::incrByFloat
     */
    public function incrByFloat($key, $value)
    {
        return $this->writeHandle->incrByFloat($key, $value);
    }

    /**
     * @see Redis::decr
     * @see Redis::decrBy
     */
    public function decr($key, $value = 1)
    {
        $result = null;
        if ($value == 1) {
            $result = $this->writeHandle->decr($key);
        } else {
            $result = $this->writeHandle->decrBy($key, $value);
        }
        return $result;
    }

    /**
     * $secondsFormat, default true <br/>
     * true: Redis::ttl <br/>
     * false: Redis::pttl <br/>
     *
     * @see Redis::ttl
     * @see Redis::pttl
     */
    public function ttl($key, $secondsFormat = true)
    {
        $result = null;
        if ($secondsFormat) {
            $result = $this->readHandle->ttl($key);
        } else {
            $result = $this->readHandle->pttl($key);
        }
        return $result;
    }

    /**
     * $secondsFormat, default true <br/>
     * true: Redis::expire <br/>
     * false: Redis::pExpire <br/>
     *
     * @see Redis::expire
     * @see Redis::pExpire
     */
    public function expire($key, $timeout, $secondsFormat = true)
    {
        $result = null;
        if ($secondsFormat) {
            $result = $this->writeHandle->expire($key, $timeout);
        } else {
            $result = $this->writeHandle->pExpire($key, $timeout);
        }
        return $result;
    }

    /**
     * @see Redis::persist
     */
    public function persist($key)
    {
        return $this->writeHandle->persist($key);
    }

    /**
     * @see Redis::keys
     */
    public function keys($pattern = null)
    {
        return $this->readHandle->keys($pattern);
    }

    /**
     * @see Redis::info
     */
    public function info()
    {
        return $this->writeHandle->info();
    }

    /**
     * @see Redis::flushAll
     */
    public function flush()
    {
        return $this->writeHandle->flushAll();
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* TRANSACTION
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * @see Redis::multi
     */
    public function multi()
    {
        if (!$this->multiEnabled) {
            $this->multiEnabled = true;
            return $this->writeHandle->multi();
        } else {
            return $this->writeHandle;
        }
    }

    /**
     * @see Redis::exec
     */
    public function exec()
    {
        if ($this->multiEnabled) {
            $this->multiEnabled = false;
            return $this->writeHandle->exec();
        } else {
            return $this->writeHandle;
        }
    }

    /**
     * @see Redis::discard
     */
    public function discard()
    {
        if ($this->multiEnabled) {
            $this->multiEnabled = false;
            return $this->writeHandle->discard();
        } else {
            return $this->writeHandle;
        }
    }

    /**
     * @see Redis::watch
     */
    public function watch($key)
    {
        $this->writeHandle->watch($key);
    }

    /**
     * @see Redis::unwatch
     */
    public function unwatch()
    {
        $this->writeHandle->unwatch();
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* HASHES
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * @see Redis::hSet
     */
    public function hSet($key, $field, $value)
    {
        return $this->writeHandle->hSet($key, $field, $value);
    }

    /**
     * @see Redis::hSetNx
     */
    public function hSetNx($key, $field, $value)
    {
        return $this->writeHandle->hSetNx($key, $field, $value);
    }

    /**
     * @see Redis::hGet
     */
    public function hGet($key, $field)
    {
        return $this->readHandle->hGet($key, $field);
    }

    /**
     * @see Redis::hGetAll
     */
    public function hGetAll($key)
    {
        return $this->readHandle->hGetAll($key);
    }

    /**
     * @see Redis::hDel
     */
    public function hDel($key, $field)
    {
        return $this->writeHandle->hDel($key, $field);
    }

    /**
     * @see Redis::hExists
     */
    public function hExists($key, $field)
    {
        return $this->readHandle->hExists($key, $field);
    }

    /**
     * @see Redis::hIncrBy
     */
    public function hIncrBy($key, $field, $value)
    {
        return $this->writeHandle->hIncrBy($key, $field, $value);
    }

    /**
     * @see Redis::hIncrByFloat
     */
    public function hIncrByFloat($key, $field, $value)
    {
        return $this->writeHandle->hIncrByFloat($key, $field, $value);
    }

    /**
     * @see Redis::hMset
     */
    public function hMset($key, $members)
    {
        return $this->writeHandle->hMset($key, $members);
    }

    /**
     * @see Redis::hMget
     */
    public function hMget($key, $memberKeys)
    {
        return $this->readHandle->hMget($key, $memberKeys);
    }

    /**
     * @see Redis::hKeys
     */
    public function hKeys($key)
    {
        return $this->readHandle->hKeys($key);
    }

    /**
     * @see Redis::hVals
     */
    public function hVals($key)
    {
        return $this->readHandle->hVals($key);
    }

    /**
     * @see Redis::hLen
     */
    public function hLen($key)
    {
        return $this->readHandle->hLen($key);
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* LIST
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * @see Redis::blPop
     */
    public function blPop($keys)
    {
        return $this->writeHandle->blPop($keys);
    }

    /**
     * @see Redis::brpoplpush
     */
    public function brpoplpush($srcKey, $dstKey, $timeout)
    {
        return $this->writeHandle->brpoplpush($srcKey, $dstKey, $timeout);
    }

    /**
     * @see Redis::lIndex
     */
    public function lIndex($key, $index)
    {
        return $this->readHandle->lIndex($key, $index);
    }

    /**
     * @see Redis::lInsert
     */
    public function lInsert($key, $position, $pivot, $value)
    {
        return $this->writeHandle->lInsert($key, $position, $pivot, $value);
    }

    /**
     * @see Redis::lLen
     */
    public function lLen($key)
    {
        return $this->readHandle->lLen($key);
    }

    /**
     * @see Redis::lPop
     */
    public function lPop($key)
    {
        return $this->writeHandle->lPop($key);
    }

    /**
     * @see Redis::lPush
     */
    public function lPush($key, $value)
    {
        return call_user_func_array(array($this->writeHandle, 'lPush'), func_get_args());
    }

    /**
     * @see Redis::lPushx
     */
    public function lPushx($key, $value)
    {
        return $this->writeHandle->lPushx($key, $value);
    }

    /**
     * @see Redis::lRange
     */
    public function lRange($key, $start, $end)
    {
        return $this->readHandle->lRange($key, $start, $end);
    }

    /**
     * @see Redis::lRem
     */
    public function lRem($key, $value, $count)
    {
        return $this->writeHandle->lRem($key, $value, $count);
    }

    /**
     * @see Redis::lSet
     */
    public function lSet($key, $index, $value)
    {
        return $this->writeHandle->lSet($key, $index, $value);
    }

    /**
     * @see Redis::lTrim
     */
    public function lTrim($key, $start, $stop)
    {
        return $this->writeHandle->lTrim($key, $start, $stop);
    }

    /**
     * @see Redis::rPop
     */
    public function rPop($key)
    {
        return $this->writeHandle->rPop($key);
    }

    /**
     * @see Redis::rpoplpush
     */
    public function rpoplpush($srcKey, $dstKey)
    {
        return $this->writeHandle->rpoplpush($srcKey, $dstKey);
    }

    /**
     * @see Redis::rPush
     */
    public function rPush($key, $value)
    {
        return call_user_func_array(array($this->writeHandle, 'rPush'), func_get_args());
    }

    /**
     * @see Redis::rPushx
     */
    public function rPushx($key, $value)
    {
        return $this->writeHandle->rPushx($key, $value);
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* SET
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * @see Redis::sAdd
     */
    public function sAdd($key, $value)
    {
        return call_user_func_array(array($this->writeHandle, 'sAdd'), func_get_args());
    }

    /**
     * @see Redis::sCard
     */
    public function sCard($key)
    {
        return $this->readHandle->sCard($key);
    }

    /**
     * @see Redis::sDiff
     */
    public function sDiff($key)
    {
        return call_user_func_array(array($this->writeHandle, 'sDiff'), func_get_args());
    }

    /**
     * @see Redis::sDiffStore
     */
    public function sDiffStore($dstKey, $key)
    {
        return call_user_func_array(array($this->writeHandle, 'sDiffStore'), func_get_args());
    }

    /**
     * @see Redis::sInter
     */
    public function sInter($key)
    {
        return call_user_func_array(array($this->writeHandle, 'sInter'), func_get_args());
    }

    /**
     * @see Redis::sInterStore
     */
    public function sInterStore($dstKey, $key)
    {
        return call_user_func_array(array($this->writeHandle, 'sInterStore'), func_get_args());
    }

    /**
     * @see Redis::sIsMember
     */
    public function sIsMember($key, $value)
    {
        return $this->readHandle->sIsMember($key, $value);
    }

    /**
     * @see Redis::sMembers
     */
    public function sMembers($key)
    {
        return $this->readHandle->sMembers($key);
    }

    /**
     * @see Redis::sMove
     */
    public function sMove($srcKey, $dstKey, $member)
    {
        return $this->writeHandle->sMove($srcKey, $dstKey, $member);
    }

    /**
     * @see Redis::sPop
     */
    public function sPop($key)
    {
        return $this->writeHandle->sPop($key);
    }

    /**
     * @see Redis::sRandMember
     */
    public function sRandMember($key)
    {
        return $this->readHandle->sRandMember($key);
    }

    /**
     * @see Redis::sRem
     */
    public function sRem($key, $member)
    {
        return call_user_func_array(array($this->writeHandle, 'sRem'), func_get_args());
    }

    /**
     * @see Redis::sUnion
     */
    public function sUnion($key)
    {
        return call_user_func_array(array($this->writeHandle, 'sUnion'), func_get_args());
    }

    /**
     * @see Redis::sUnionStore
     */
    public function sUnionStore($dstKey, $key)
    {
        return call_user_func_array(array($this->writeHandle, 'sUnionStore'), func_get_args());
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* SORTED SET
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * @see Redis::zAdd
     */
    public function zAdd($key, $score, $member)
    {
        return $this->writeHandle->zAdd($key, $score, $member);
    }

    /**
     * @see Redis::zCard
     */
    public function zCard($key)
    {
        return $this->writeHandle->zCard($key);
    }

    /**
     * @see Redis::zIncrBy
     */
    public function zIncrBy($key, $member, $value)
    {
        return $this->writeHandle->zIncrBy($key, $value, $member);
    }

    /**
     * @see Redis::zDelete
     */
    public function zDelete($key, $member)
    {
        return $this->writeHandle->zDelete($key, $member);
    }

    /**
     * @see Redis::zRemRangeByScore
     */
    public function zRemRangeByScore($key, $start, $end)
    {
        return $this->writeHandle->zRemRangeByScore($key, $start, $end);
    }

    /**
     * @see Redis::zRemRangeByRank
     */
    public function zRemRangeByRank($key, $start, $end)
    {
        return $this->writeHandle->zRemRangeByRank($key, $start, $end);
    }

    /**
     * @see Redis::zScore
     */
    public function zScore($key, $member)
    {
        return $this->readHandle->zScore($key, $member);
    }

    /**
     * @see Redis::zRank
     */
    public function zRank($key, $member)
    {
        return $this->readHandle->zRank($key, $member);
    }

    /**
     * @see Redis::zRevRank
     */
    public function zRevRank($key, $member)
    {
        return $this->readHandle->zRevRank($key, $member);
    }

    /**
     * @see Redis::zRange
     */
    public function zRange($key, $start, $end, $withScore = false)
    {
        return $this->readHandle->zRange($key, $start, $end, $withScore);
    }

    /**
     * @see Redis::zRevRange
     */
    public function zRevRange($key, $start, $end, $withScore = false)
    {
        return $this->readHandle->zRevRange($key, $start, $end, $withScore);
    }

    /**
     * @see Redis::zSize
     */
    public function zSize($key)
    {
        return $this->readHandle->zSize($key);
    }

    /**
     * @see Redis::zCount
     */
    public function zCount($key, $start, $end)
    {
        return $this->readHandle->zCount($key, $start, $end);
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* MAGIC
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Any Redis function exists but not implemented in this class.
     *
     * @param string $functionName
     * @param array $functionParams
     * @return mixed
     */
    public function __call($functionName, $functionParams)
    {
        return call_user_func_array(array($this->writeHandle, $functionName), $functionParams);
    }

}