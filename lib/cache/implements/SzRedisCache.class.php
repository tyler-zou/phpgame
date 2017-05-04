<?php
class SzRedisCache extends SzAbstractCache
{

    /**
     * @see SzAbstractCache::connect
     */
    protected function connect($server)
    {
        $handle = new Redis();

        $isConnected = $handle->connect($server[0], $server[1]);
        if (!$isConnected) {
            throw new SzException(10700, array($server[0], $server[1]));
        }
        $handle->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE); // no serializer

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
        $results = array();
        $redisResponses = $this->handle->mget($keys);

        if ($redisResponses) {
            foreach ($redisResponses as $index => $value) {
                $key = $keys[$index];
                if ($value) {
                    $results[$key] = $this->decodeValue($value);
                } else {
                    $results[$key] = false;
                }
            }
        }

        return $results;
    }

    /**
     * @see SzAbstractCache::set
     */
    public function set($key, $value, $expire = null)
    {
        $result = null;

        $value = $this->encodeValue($value);

        if (is_null($expire)) {
            $result = $this->handle->set($key, $value);
        } else {
            $result = $this->handle->setex($key, $this->genExpire($expire), $value);
        }

        return $result;
    }

    /**
     * @see SzAbstractCache::mSet
     */
    public function mSet($items, $expire = null)
    {
        foreach ($items as $key => $value) {
            $items[$key] = $this->encodeValue($value);
        }
        $result = $this->handle->mset($items);
        if (!is_null($expire)) {
            foreach ($items as $key => $value) {
                $this->expire($key, $expire);
            }
        }

        return $result;
    }


    /**
     * @see SzAbstractCache::add
     */
    public function add($key, $value, $expire = null)
    {
        $result = $this->handle->setnx($key, $this->encodeValue($value));
        if (!is_null($expire)) {
            $this->expire($key, $expire);
        }

        return $result;
    }

    /**
     * @see SzAbstractCache::incr
     */
    public function incr($key, $value = 1, $expire = null)
    {
        $result = $this->handle->incrBy($key, $value);
        if (!is_null($expire)) {
            $this->expire($key, $expire);
        }

        return $result;
    }

    /**
     * @see SzAbstractCache::delete
     */
    public function delete($key)
    {
        return $this->handle->del($key);
    }

    /**
     * @see SzAbstractCache::flush
     */
    public function flush()
    {
        return $this->handle->flushAll();
    }

    /**
     * @see SzAbstractCache::expire
     */
    public function expire($key, $expire)
    {
        return $this->handle->expire($key, $this->genExpire($expire));
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* HASH FUNCTIONS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * @see SzAbstractCache::hSet
     */
    public function hSet($key, $field, $value, $expire = null)
    {
        $result = $this->handle->hSet($key, $field, $this->encodeValue($value));
        if (!is_null($expire)) {
            $this->expire($key, $expire);
        }

        return $result;
    }

    /**
     * @see SzAbstractCache::hMSet
     */
    public function hMSet($key, $params, $expire = null)
    {
        foreach ($params as $index => $value) {
            $params[$index] = $this->encodeValue($value);
        }
        $result = $this->handle->hMset($key, $params);
        if (!is_null($expire)) {
            $this->expire($key, $expire);
        }

        return $result;
    }

    /**
     * @see SzAbstractCache::hGetAll
     */
    public function hGetAll($key)
    {
        $results = $this->handle->hGetAll($key);

        if ($results) {
            foreach ($results as $key => $value) {
                $results[$key] = $this->decodeValue($value);
            }
        }

        return $results;
    }

    /**
     * @see SzAbstractCache::hDel
     */
    public function hDel($key, $field1, $field2 = null, $fieldN = null)
    {
        /**
         * All the arguments listed at the declaration line are useless.
         * Since this function can accept multi | unlimited counts of $field arguments,
         * we have to implement it via "call_user_func_array" & "func_get_args".
         */
        return call_user_func_array(array($this->handle, 'hDel'), func_get_args());
    }

    /**
     * @see SzAbstractCache::hGet
     */
    public function hGet($key, $hashKey)
    {
        return $this->decodeValue($this->handle->hGet($key, $hashKey));
    }

    /**
     * @see SzAbstractCache::hLen
     */
    public function hLen($key)
    {
        return $this->handle->hLen($key);
    }
}