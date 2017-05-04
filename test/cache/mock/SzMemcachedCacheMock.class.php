<?php
require_once dirname(dirname(dirname(__DIR__))) . '/lib/cache/SzAbstractCache.class.php';
require_once dirname(dirname(dirname(__DIR__))) . '/lib/cache/implements/SzMemcachedCache.class.php';

class SzMemcachedCacheMock extends SzMemcachedCache
{
    protected function connect($server)
    {
        return $server;
    }

    public function get($key)
    {
        return $key;
    }

    public function mGet($keys)
    {
        return $keys;
    }

    public function set($key, $value, $expire = null)
    {
        $result = null;
        if (is_null($expire)) {
            return array($key, $value);
        } else {
            return array($key, $value, $this->genExpire($expire));
        }
    }

    public function mSet($items, $expire = null)
    {
        return $items;
    }

    public function add($key, $value, $expire = null)
    {
        return array($key, $value);
    }

    public function incr($key, $value = 1, $expire = null)
    {
        return array($key, $value);
    }

    public function delete($key)
    {
        return $key;
    }

    public function flush()
    {
        return true;
    }

    public function expire($key, $expire)
    {
        return array($key, $this->genExpire($expire));
    }

    public function hSet($key, $field, $value, $expire = null)
    {
        return array($key, $field, $value);
    }

    /**
     * @see SzAbstractCache::hMSet
     */
    public function hMSet($key, $params, $expire = null)
    {
        return array($key, $params);
    }

    /**
     * @see SzAbstractCache::hGetAll
     */
    public function hGetAll($key)
    {
        return array($key);
    }

    public function hDel($key, $field1, $field2 = null, $fieldN = null)
    {
        return array($key);
    }

    public function hGet($key, $hashKey)
    {
        return array($key, $hashKey);
    }

    public function hLen($key)
    {
        return array($key);
    }
}