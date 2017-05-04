<?php
class SzCacheFactory extends SzAbstractCacheFactory
{

    /**
     * Get the instance of SzCacheFactory.
     *
     * @return SzCacheFactory
     */
    public static function get()
    {
        if (!self::$instance) {
            self::$instance = new SzCacheFactory();
        }

        return self::$instance;
    }

    /**
     * Do nothing here if the application has no specific requirements of the cache
     * instance retrieving process, otherwise you have to overwrite the API
     * "getAppCache" & "getStaticCache".
     *
     * Though there can be no implementations in this class,
     * the class have to be declared here, with factory functionality.
     */

}