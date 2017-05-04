<?php
class SzContextFactory
{

    /**
     * @var SzContext
     */
    private static $instance;

    /**
     * Initialize SzContextFactory.
     *
     * @return void
     */
    public static function init()
    {
        self::$instance = new SzContext();
    }

    /**
     * Get instance of SzContext.
     *
     * @return SzContext
     */
    public static function get()
    {
        return self::$instance;
    }

    /**
     * Singleton insurance.
     *
     * @return SzContextFactory
     */
    private function __construct()
    {
    }

}