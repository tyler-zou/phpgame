<?php
abstract class SzAbstractCache
{

    const CACHE_TYPE_MEMCACHED = 'Memcached';
    const CACHE_TYPE_REDIS     = 'Redis';

    public static $VALID_CACHE_TYPES = array(
        self::CACHE_TYPE_MEMCACHED, self::CACHE_TYPE_REDIS
    );

    /**
     * default expire time, in seconds <br/>
     * default is 1296000 = 2 weeks
     *
     * @var int
     */
    const EXPIRES = 1296000;

    /**
     * instance of the cache handler
     *
     * @var Memcache|Memcached|Redis
     */
    protected $handle;

    /**
     * max % variance in actual expiration time <br/>
     *
     * <pre>
     * max variance (in percent) in expiration of cache.
     * Thus, for a variance of 10 if an expiration time of 100 seconds is specified,
     * the item will actually expire in 90-110 seconds (selected randomly).
     * Designed to prevent mass simultaneous expiration of cache objects.
     * </pre>
     *
     * @var int
     */
    protected $variance = 10;
    /**
     * data size over which the data would be compressed before store in cache <br/>
     * default is 0, means no compress enabled
     * FIXME tobe implemented
     *
     * @var int
     */
    protected $compressSize = 0;

    /**
     * Initialize the cache class.
     *
     * @param array $server the detail of the configs: refer to config file "cache.config.php"
     * @return SzAbstractCache
     */
    public function __construct($server)
    {
        $this->handle = $this->connect($server);
    }

    /**
     * Connect to the cache server.
     *
     * @param array $server array(host, port)
     * @return Memcache|Memcached|Redis
     */
    protected abstract function connect($server);

    /**
     * Generate an expire time with variance calculated in it.
     *
     * @param int $expires in seconds, default null, means use system default expire time
     * @return int
     */
    public function genExpire($expires = null)
    {
        if (is_null($expires)) {
            $expires = self::EXPIRES;
        }
        $expiresVariance = mt_rand(0, $expires * 0.02 * $this->variance) - $expires * 0.01 * $this->variance;
        $expires = (int)($expires + $expiresVariance);

        return $expires;
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* BASIC (KEY & VALUE) STRUCTURE
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Get data from cache according to $key.
     *
     * @see Memcached::get
     * @see Redis::get
     *
     * @param string $key
     * @return mixed
     */
    public abstract function get($key);

    /**
     * Get multi data from multi $keys.
     *
     * @see Memcached::getMultiple
     * @see Redis::mget
     *
     * @param array $keys
     * @return array $data
     * <pre>
     * array(
     *     key1 => value,
     *     key2 => false, // means key does not exist
     *     ...
     * )
     * </pre>
     */
    public abstract function mGet($keys);

    /**
     * Set data into cache.
     *
     * @see Memcached::set
     * @see Redis::setex
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire in seconds, default null, means no expire
     * @return boolean
     */
    public abstract function set($key, $value, $expire = null);

    /**
     * Set multi items into cache. All items share the same expire time.
     *
     * @see Memcached::setMulti
     * @see Redis::mset
     *
     * @param $items
     * <pre>
     * array(
     *     itemKey => itemValue,
     *     ...
     * )
     * </pre>
     * @param int $expire in seconds, default null, means no expire
     * @return boolean
     */
    public abstract function mSet($items, $expire = null);

    /**
     * Add data into cache if $key does not exist.
     *
     * @see Memcached::add
     * @see Redis::setnx
     * @see Redis::expire
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire in seconds, default null, means no expire
     * @return boolean
     */
    public abstract function add($key, $value, $expire = null);

    /**
     * Increase a $key by $value.
     *
     * @see Memcached::increment
     * @see Redis::incr
     *
     * @param string $key
     * @param int $value default 1
     * @param int $expire in seconds, default null, means no expire
     * @return int
     */
    public abstract function incr($key, $value = 1, $expire = null);

    /**
     * Delete a $key in cache.
     *
     * @see Memcached::delete
     * @see Redis::delete
     *
     * @param string $key
     * @return boolean
     */
    public abstract function delete($key);

    /**
     * Flush all the data in cache.
     *
     * @see Memcached::flush
     * @see Redis::flushAll
     *
     * @return boolean
     */
    public abstract function flush();

    /**
     * Set the expire time of the $key.
     *
     * @see Redis::expire
     *
     * @param string $key
     * @param int $expire in seconds
     * @return boolean
     */
    public abstract function expire($key, $expire);

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* HASH STRUCTURE
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Set a $value into the $field of the $key.
     *
     * @see Redis::hSet
     *
     * @param string $key
     * @param string $field
     * @param mixed $value
     * @param int $expire in seconds, default null, means no expire
     * @return int
     */
    public abstract function hSet($key, $field, $value, $expire = null);

    /**
     * Set multi $value into multi $field of the $key.
     *
     * @see Redis::hMSet
     *
     * @param string $key
     * @param array $params array($field => $value)
     * @param int $expire in seconds, default null, means no expire
     * @return boolean
     */
    public abstract function hMSet($key, $params, $expire = null);

    /**
     * Gets a value from the hash stored at key.
     * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     *
     * @see Redis::hGet
     *
     * @param   string  $key
     * @param   string  $hashKey
     * @return  string  The value, if the command executed successfully BOOL FALSE in case of failure
     */
    public abstract function hGet($key, $hashKey);

    /**
     * Returns the length of a hash, in number of items
     *
     * @see Redis::hLen
     *
     * @param   string  $key
     * @return  int     the number of items in a hash, FALSE if the key doesn't exist or isn't a hash.
     */
    public abstract function hLen($key);

    /**
     * Get all $fields from the $key.
     *
     * @see Redis::hGetAll
     *
     * @param string $key
     * @return array
     */
    public abstract function hGetAll($key);

    /**
     * Delete multi $fields from $key.
     *
     * @see Redis::hDel
     *
     * @param string $key
     * @param string $field1
     * @param string $field2
     * @param string $fieldN
     * @return int
     */
    public abstract function hDel($key, $field1, $field2 = null, $fieldN = null);

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* UTILITIES
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * Encode inputted value into string format.
     *
     * @param mixed $rawValue
     * @throws SzException 10704
     * @return string $value
     */
    protected function encodeValue($rawValue)
    {
        $value = $rawValue; // default raw value

        if (is_resource($rawValue) || is_object($rawValue)) {
            throw new SzException(10704, gettype($rawValue)); // value type not supported
        }
        if (!is_string($rawValue)) {
            /**
             * boolean: var_dump(json_encode(true)); => string(4) "true"
             * integer: var_dump(json_encode(1));    => string(1) "1"
             * double:  var_dump(json_encode(2.1));  => string(3) "2.1"
             * null:    var_dump(json_encode(null)); => string(4) "null"
             * array:   var_dump(json_encode(array("name" => "david"))); => string(16) "{"name":"david"}"
             */
            $value = json_encode($rawValue);
        }
        // string: use raw value

        return $value;
    }

    /**
     * Decode value into array or other mixed type.
     *
     * @param string $encodedValue value got from memcached
     * @return mixed $value
     */
    protected function decodeValue($encodedValue)
    {
        $value = '';

        if (is_bool($encodedValue)) {
            // boolean result should means the result from cache system directly, no need to decode
            $value = $encodedValue;
        } else {
            try {
                SzValidator::validateJson($encodedValue);
                // no exception, means json string
                $value = json_decode($encodedValue, true);
            } catch (Exception $e) {
                // exception, means not json string
                if ($encodedValue == 'true' || $encodedValue == 'false' || $encodedValue == 'null') {
                    // since this value is encoded by json_encode, so it's always lower case
                    $value = json_decode($encodedValue); // "true" => true || "false" => false || "null" => null
                } else {
                    /**
                     * integer: "1"
                     * double:  "2.1"
                     * string:  "any string value"
                     */
                    $value = $encodedValue;
                }
            }
        }

        return $value;
    }

}