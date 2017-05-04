<?php
class SzMemcachedCache extends SzAbstractCache
{

    /**
     * @see SzAbstractCache::connect
     *
     * @param array $servers
     * <pre>
     * array(
     *     array(host, port),
     *     ...
     * )
     * </pre>
     * @throws SzException
     * @return Memcached
     */
    protected function connect($servers)
    {
        $handle = new Memcached();

        $connected = $handle->addServers($servers);
        if (false === $connected) {
            throw new SzException(10702, json_encode($servers));
        }
        $handle->setOptions(array(
            Memcached::OPT_COMPRESSION => true,
            Memcached::OPT_HASH => Memcached::HASH_MD5,
            Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
            Memcached::OPT_LIBKETAMA_COMPATIBLE => true
        ));

        return $handle;
    }

    /**
     * @see SzAbstractCache::get
     */
    public function get($key)
    {
        return $this->decodeValue($this->handle->get($key));
    }

    /**
     * @see SzAbstractCache::mGet
     */
    public function mGet($keys)
    {
        $results = $this->handle->getMulti($keys);

        if ($results) {
            foreach ($keys as $key) {
                if (!SzUtility::checkArrayKey($key, $results)) {
                    $results[$key] = false;
                }
            }
            foreach ($results as $key => $value) {
                $results[$key] = $this->decodeValue($value);
            }
        }

        return $results;
    }

    /**
     * @see SzAbstractCache::set
     */
    public function set($key, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = 0;
        } else {
            $expire = $this->genExpire($expire);
        }
        return $this->handle->set($key, $this->encodeValue($value), $expire);
    }

    /**
     * @see SzAbstractCache::mSet
     */
    public function mSet($items, $expire = null)
    {
        foreach ($items as $key => $value) {
            $items[$key] = $this->encodeValue($value);
        }
        if (is_null($expire)) {
            $expire = 0;
        } else {
            $expire = $this->genExpire($expire);
        }
        $this->handle->setMulti($items, $expire);
    }

    /**
     * @see SzAbstractCache::add
     */
    public function add($key, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = 0;
        } else {
            $expire = $this->genExpire($expire);
        }
        return $this->handle->add($key, $this->encodeValue($value), $expire);
    }

    /**
     * @see SzAbstractCache::incr
     */
    public function incr($key, $value = 1, $expire = null)
    {
        if (is_null($expire)) {
            $expire = 0;
        } else {
            $expire = $this->genExpire($expire);
        }

        $this->expire($key, $expire);
        return $this->handle->increment($key, $value);
    }

    /**
     * @see SzAbstractCache::delete
     */
    public function delete($key)
    {
        return $this->handle->delete($key);
    }

    /**
     * @see SzAbstractCache::flush
     */
    public function flush()
    {
        return $this->handle->flush();
    }

    /**
     * @see SzAbstractCache::expire
     */
    public function expire($key, $expire)
    {
        return $this->handle->touch($key, $expire);
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* HASH FUNCTIONS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * @see SzAbstractCache::hSet
     */
    public function hSet($key, $field, $value, $expire = null)
    {
        return false;
    }

    /**
     * @see SzAbstractCache::hMSet
     */
    public function hMSet($key, $value, $expire = null)
    {
        return $this->set($key, $this->encodeValue($value), $expire);
    }

    /**
     * @see SzAbstractCache::hGetAll
     */
    public function hGetAll($key)
    {
        return $this->decodeValue($this->get($key));
    }

    /**
     * @see SzAbstractCache::hDel
     */
    public function hDel($key, $field1, $field2 = null, $fieldN = null)
    {
        return $this->delete($key);
    }

    /**
     * @see SzAbstractCache::hGet
     */
    public function hGet($key, $hashKey)
    {
        return false;
    }

    /**
     * @see SzAbstractCache::hLen
     */
    public function hLen($key)
    {
        return false;
    }
}