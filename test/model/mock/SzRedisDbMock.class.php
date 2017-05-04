<?php
require_once dirname(dirname(dirname(__DIR__))) . '/lib/model/abstract/db/SzAbstractDb.class.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lib/model/implements/db/SzRedisDb.class.php';

class SzRedisDbMock extends SzRedisDb
{
    protected function connect($host, $port, $userName = null, $password = null, $dbName = null)
    {
        return true;
    }

    public function set($key, $value, $timeout = null)
    {
        return array($key, $value, $timeout);
    }

    public function get($key)
    {
        return $key;
    }

    public function delete($keys)
    {
        return $keys;
    }

    public function incr($key, $value = 1)
    {
        return array($key, $value);
    }

    public function incrByFloat($key, $value)
    {
        return array($key, $value);
    }

    public function decr($key, $value = 1)
    {
        return array($key, $value);
    }

    public function ttl($key, $secondsFormat = true)
    {
        return array($key, $secondsFormat);
    }

    public function expire($key, $timeout, $secondsFormat = true)
    {
        return array($key, $timeout, $secondsFormat);
    }

    public function persist($key)
    {
        return $key;
    }

    public function keys($pattern = null)
    {
        return $pattern;
    }

    public function info()
    {
        return true;
    }

    public function flush()
    {
        return true;
    }

    public function multi()
    {
        return true;
    }

    public function exec()
    {
        return true;
    }

    public function discard()
    {
        return true;
    }

    public function watch($key)
    {
        return $key;
    }

    public function unwatch()
    {
        return true;
    }

    public function hSet($key, $field, $value)
    {
        return array($key, $field, $value);
    }

    public function hSetNx($key, $field, $value)
    {
        return array($key, $field, $value);
    }

    public function hGet($key, $field)
    {
        return array($key, $field);
    }

    public function hGetAll($key)
    {
        return array('[1, 60, 10001, 1, 1, 0, 0]');
    }

    public function hDel($key, $field)
    {
        return array($key, $field);
    }

    public function hExists($key, $field)
    {
        return array($key, $field);
    }

    public function hIncrBy($key, $field, $value)
    {
        return array($key, $field, $value);
    }

    public function hIncrByFloat($key, $field, $value)
    {
        return array($key, $field, $value);
    }

    public function hMset($key, $members)
    {
        return array($key, $members);
    }

    public function hMget($key, $memberKeys)
    {
        return array($key, $memberKeys);
    }

    public function hKeys($key)
    {
        return $key;
    }

    public function hVals($key)
    {
        return $key;
    }

    public function hLen($key)
    {
        return $key;
    }

    public function blPop($keys)
    {
        return $keys;
    }

    public function brpoplpush($srcKey, $dstKey, $timeout)
    {
        return array($srcKey, $dstKey, $timeout);
    }

    public function lIndex($key, $index)
    {
        return array($key, $index);
    }

    public function lInsert($key, $position, $pivot, $value)
    {
        return array($key, $position, $pivot, $value);
    }

    public function lLen($key)
    {
        return $key;
    }

    public function lPop($key)
    {
        return $key;
    }

    public function lPush($key, $value1, $value2 = null, $valueN = null)
    {
        return array($key, $value1, $value2, $valueN);
    }

    public function lPushx($key, $value)
    {
        return array($key, $value);
    }

    public function lRange($key, $start, $end)
    {
        return array($key, $start, $end);
    }

    public function lRem($key, $value, $count)
    {
        return array($key, $value, $count);
    }

    public function lSet($key, $index, $value)
    {
        return array($key, $index, $value);
    }

    public function lTrim($key, $start, $stop)
    {
        return array($key, $start, $stop);
    }

    public function rPop($key)
    {
        return $key;
    }

    public function rpoplpush($srcKey, $dstKey)
    {
        return array($srcKey, $dstKey);
    }

    public function rPush($key, $value1, $value2 = null, $valueN = null)
    {
        return array($key, $value1, $value2, $valueN);
    }

    public function rPushx($key, $value)
    {
        return array($key, $value);
    }

    public function sAdd($key, $value1, $value2 = null, $valueN = null)
    {
        return array($key, $value1, $value2, $valueN);
    }

    public function sCard($key)
    {
        return $key;
    }

    public function sDiff($key1, $key2, $keyN = null)
    {
        return array($key1, $key2, $keyN);
    }

    public function sDiffStore($dstKey, $key1, $key2, $keyN = null)
    {
        return array($dstKey, $key1, $key2, $keyN);
    }

    public function sInter($key1, $key2, $keyN = null)
    {
        return array($key1, $key2, $keyN);
    }

    public function sInterStore($dstKey, $key1, $key2, $keyN = null)
    {
        return array($dstKey, $key1, $key2, $keyN);
    }

    public function sIsMember($key, $value)
    {
        return array($key, $value);
    }

    public function sMembers($key)
    {
        return $key;
    }

    public function sMove($srcKey, $dstKey, $member)
    {
        return array($srcKey, $dstKey, $member);
    }

    public function sPop($key)
    {
        return $key;
    }

    public function sRandMember($key)
    {
        return $key;
    }

    public function sRem($key, $member1, $member2 = null, $memberN = null)
    {
        return array($key, $member1, $member2, $memberN);
    }

    public function sUnion($key1, $key2, $keyN = null)
    {
        return array($key1, $key2, $keyN = null);
    }

    public function sUnionStore($dstKey, $key1, $key2, $keyN = null)
    {
        return array($dstKey, $key1, $key2, $keyN = null);
    }

    public function zAdd($key, $score, $member)
    {
        return array($key, $score, $member);
    }

    public function zIncrBy($key, $member, $value)
    {
        return array($key, $member, $value);
    }

    public function zDelete($key, $member)
    {
        return array($key, $member);
    }

    public function zRemRangeByScore($key, $start, $end)
    {
        return array($key, $start, $end);
    }

    public function zRemRangeByRank($key, $start, $end)
    {
        return array($key, $start, $end);
    }

    public function zScore($key, $member)
    {
        return array($key, $member);
    }

    public function zRank($key, $member)
    {
        return array($key, $member);
    }

    public function zRange($key, $start, $end, $withScore = false)
    {
        return array($key, $start, $end, $withScore);
    }

    public function zSize($key)
    {
        return $key;
    }

    public function zCount($key, $start, $end)
    {
        return array($key, $start, $end);
    }
}