<?php
class SzAutoload
{

    /**
     * @var SzAutoload
     */
    private static $instance;

    /**
     * className => relativeClassPath
     *
     * @var array
     */
    private $frameAutoloads = array();
    /**
     * className => relativeClassPath
     *
     * @var array
     */
    private $appAutoloads = array();

    /**
     * className => moduleName
     *
     * @var array
     */
    private $moduleClassNames = array();
    /**
     * moduleName => array(
     *     className => relativeClassPath
     * )
     *
     * @var array
     */
    private $moduleAutoloads = array();

    /**
     * Initialize SzAutoload.
     *
     * @throws SzException 10000
     * @return void
     */
    public static function init()
    {
        self::$instance = new SzAutoload();
        if (false === spl_autoload_register(array(self::$instance, 'autoload'))) {
            throw new SzException(10000);
        }
    }

    /**
     * Initialize SzAutoload.
     *
     * @return SzAutoload
     */
    private function __construct()
    {
        // app
        $this->appAutoloads = SzConfig::get()->loadAppConfig('autoload');

        // framework
        $this->frameAutoloads = array_merge(
            SzConfig::get()->loadFrameConfig('autoload'),
            SzConfig::get()->loadFrameConfig('vendor')
        );

        // modules
        if (count(SzSystem::$MODULE_VERS) > 0) {
            foreach (SzSystem::$MODULE_VERS as $moduleName => $moduleVer) {
                $moduleAutoloads = SzConfig::get()->loadModuleConfig($moduleName, 'autoload');

                if (is_array($moduleAutoloads) && $moduleAutoloads) {
                    foreach ($moduleAutoloads as $moduleClassName => $moduleClassPath) {
                        $this->moduleClassNames[$moduleClassName] = $moduleName;
                        $this->moduleAutoloads[$moduleName][$moduleClassName] = "{$moduleName}/{$moduleVer}/{$moduleClassPath}";
                    }
                }
            }
        }
    }

    /**
     * Autoload function to be called when class not found.
     *
     * @param string $className
     * @throws SzException 10001, 10002
     * @return void
     */
    public function autoload($className)
    {
        $classPath = '';

        if (isset($this->frameAutoloads[$className])) {
            // framework classes
            $classPath = SzSystem::$FRAME_ROOT . '/' . $this->frameAutoloads[$className];
        } else if (isset($this->appAutoloads[$className])) {
            // app classes
            $classPath = SzSystem::$APP_ROOT . '/' . $this->appAutoloads[$className];
        } else if (isset($this->moduleClassNames[$className])) {
            // module classes
            $moduleName = $this->moduleClassNames[$className];
            $classPath = SzSystem::$MODULE_ROOT . '/' . $this->moduleAutoloads[$moduleName][$className];
        }

        if (!$classPath) {
            /**
             * don't throw exception here,
             * since some vendor also registered autoload functions,
             * if we throw exception here, vendors will never have chance to load their classes.
             */
            SzLogger::get()->warn('SzAutoload: Class definition not found in autoload config file', array('className' => $className));
        } else if (!file_exists($classPath)) {
            throw new SzException(10002, $classPath); // class file not found!
        } else {
            include $classPath;
        }
    }

}