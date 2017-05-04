<?php
require_once dirname(dirname(dirname(__DIR__))) . '/lib/cache/SzAbstractCache.class.php';

 class SzAbstractCacheMock extends SzAbstractCache
{
    public function connect($server) {}

    public function get($key) {}

    public function mGet($keys) {}

    public function set($key, $value, $expire = null) {}

    public function mSet($items, $expire = null) {}

    public function add($key, $value, $expire = null) {}

    public function incr($key, $value = 1, $expire = null) {}

    public function delete($key) {}

    public function flush() {}

    public function expire($key, $expire) {}

    public function hSet($key, $field, $value, $expire = null) {}

    public function hMSet($key, $params, $expire = null) {}

    public function hGet($key, $hashKey) {}

    public function hLen($key) {}

    public function hGetAll($key) {}

    public function hDel($key, $field1, $field2 = null, $fieldN = null) {}

}