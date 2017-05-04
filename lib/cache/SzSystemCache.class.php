<?php
/**
 * <b>NOTE</b>: <br/>
 * This class is used to cache some system level useful resources in PHP runtime. <br/>
 * Type of resources which can be cached in this manager is strictly limited, <br/>
 * and defined in this class as static const. <br/>
 *
 * <b>DO NOT</b> use this class to cache some strings or other resources <br/>
 * whose type is not defined in the static const types.
 */
class SzSystemCache
{

    // CONFIG
    const CONFIG_FRAMEWORK_FILE = 'CONFIG_FRAMEWORK_FILE';
    const CONFIG_APP_FILE       = 'CONFIG_APP_FILE';
    const CONFIG_MODULE_FILE    = 'CONFIG_MODULE_FILE';

    // CONTROLLER
    const CTRL_ACTION_INSTANCE  = 'CTRL_ACTION_INSTANCE';
    const CTRL_PERSIST_DATA     = 'CTRL_PERSIST_DATA';
    const CTRL_KEY_MANUAL       = 'CTRL_KEY_MANUAL';

    // MODEL
    const MODEL_DB_INSTANCE      = 'MODEL_DB_INSTANCE_%s'; // "MODEL_DB_INSTANCE_MySql", "MODEL_DB_INSTANCE_Payment"
    const MODEL_QUERY_INSTANCE   = 'MODEL_QUERY_INSTANCE';
    const MODEL_CLASS_INSTANCE   = 'MODEL_CLASS_INSTANCE';
    const MODEL_VO_INSTANCE      = 'MODEL_VO_INSTANCE';
    const MODEL_VO_LIST_INSTANCE = 'MODEL_VO_LIST_INSTANCE';

    // CACHE
    const CACHE_CLASS_INSTANCE   = 'CACHE_CLASS_%s_%s';    // "CACHE_CLASS_App_Redis", "CACHE_CLASS_Static_Memcached"

    // UTILITY
    const UTIL_WORD_HUMANIZE   = 'UTIL_WORD_HUMANIZE';
    const UTIL_WORD_CAMELIZE   = 'UTIL_WORD_CAMELIZE';
    const UTIL_WORD_UNDERSCORE = 'UTIL_WORD_UNDERSCORE';
    const UTIL_CONSISTENT_HASH = 'UTIL_CONSISTENT_HASH';
    /**
     * cached resources
     *
     * <pre>
     * cacheType => array(
     *     cacheKey => cachedValue
     * )
     * </pre>
     *
     * @var array
     */
    private static $caches = array();

    /**
     * Get or Set a global resource in cache.
     *
     * @param string $type
     * @param string $key
     * @param string $value default null, means get action no value input
     * @return string $value false returned when get action & no cached value found
     */
    public static function cache($type, $key, $value = null)
    {
        $result = $value;

        if (is_null($value)) { // read action
            if (!SzUtility::checkArrayKey($type, self::$caches)) {
                self::$caches[$type] = array();
                $result = false;
            } else if (SzUtility::checkArrayKey($key, self::$caches[$type])) {
                $result = self::$caches[$type][$key];
            } else {
                $result = false;
            }
        } else { // write action
            self::$caches[$type][$key] = $value;
        }

        return $result;
    }

    /**
     * Get the total count of one type of cache.
     *
     * @param string $type
     * @return int
     */
    public static function countType($type)
    {
        if (!SzUtility::checkArrayKey($type, self::$caches)) {
            return 0;
        }
        return count(self::$caches[$type]);
    }

    /**
     * Dump all cached objects.
     *
     * <pre>
     * This function is only used in <b>TESTING</b>.
     * </pre>
     *
     * @param string $type default null, means dump all types
     * @param boolean $display default false, whether to print SzSystemCache::$caches on page
     * @return array
     */
    public static function dump($type = null, $display = false)
    {
        if (is_null($type) && $display) {
            var_dump(self::$caches);
        } else if ($type && $display) {
            var_dump(self::$caches[$type]);
        }
        return (is_null($type)) ? self::$caches : self::$caches[$type];
    }

    /**
     * remove cached data of the specified type from self::$caches.
     * if type is null, the function will remove all cached data;
     *
     * @param string $type default null, means remove all cached data
     * @return void
     */
    public static function remove($type = null)
    {
        if (is_null($type)) {
            self::$caches = array();
        } else {
            if (SzUtility::checkArrayKey($type, self::$caches)) {
                unset(self::$caches[$type]);
            }
        }
    }

}