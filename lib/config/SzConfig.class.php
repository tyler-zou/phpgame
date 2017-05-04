<?php
class SzConfig
{

    /**
     * @var SzConfig
     */
    private static $instance;

    /**
     * Initialize SzConfig.
     *
     * @return void
     */
    public static function init()
    {
        self::$instance = new SzConfig();
    }

    /**
     * Initialize SzConfig.
     *
     * @return SzConfig
     */
    private function __construct()
    {
        $this->lang = $this->loadAppConfig('app', 'LANG'); // load & set system language name
    }

    /**
     * Get initialized SzConfig.
     *
     * @return SzConfig
     */
    public static function get()
    {
        return self::$instance;
    }

    /**
     * cached system language name
     *
     * @var string
     */
    private $lang = null;

    /**
     * Load framework config.
     *
     * @see SzConfig::loadConfig
     *
     * @param string $fileName possible to contain path info in $fileName e.g "user/user.config.php"
     * @param string|array $key default null, means load whole file
     * @return mixed
     */
    public function loadFrameConfig($fileName, $key = null)
    {
        $configs = SzSystemCache::cache(SzSystemCache::CONFIG_FRAMEWORK_FILE, $fileName);
        if (!$configs) {
            $path = SzSystem::$FRAME_ROOT . "/config/{$fileName}.config.php";
            $configs = $this->loadConfigFile($path);
            SzSystemCache::cache(SzSystemCache::CONFIG_FRAMEWORK_FILE, $fileName, $configs);
        }

        return $this->loadConfig($key, $configs, $fileName);
    }

    /**
     * Load app config.
     *
     * @see SzConfig::loadConfig
     *
     * @param string $fileName possible to contain path info in $fileName e.g "user/user.config.php"
     * @param string|array $key default null, means load whole file
     * @param boolean $withLang default false, means no need to add language sub dir in path
     * @param boolean $errNotFound whether to throw exception when config not found
     * @return mixed
     */
    public function loadAppConfig($fileName, $key = null, $withLang = false, $errNotFound = true)
    {
        // get config file dir
        if ($withLang) {
            $configDir = SzSystem::$APP_ROOT . "/config/{$this->lang}";
        } else {
            $configDir = SzSystem::$APP_ROOT . "/config/";
        }

        // check whether to read data from split config file
        $splitFlag = false;
        if (!is_null($key)) {
            $path = $configDir . "/" . $fileName;
            if (is_dir($path)) {
                $splitFlag = true;
            }
        }

        // load cached data
        /**
         * Cached config data structure:
         * array(
         *     'intact'  => true, // indicate current cached data is whole of the data in config file or part of the data in config file
         *     'configs' => $configs // config data
         * );
         */
        $cacheData = SzSystemCache::cache(SzSystemCache::CONFIG_APP_FILE, $fileName);
        $configIntact = is_array($cacheData) && SzUtility::checkArrayKey('intact', $cacheData) && $cacheData['intact'];
        $configs = $cacheData ? $cacheData['configs'] : array();

        // if the value of 'intact' was set to true, means cache data was intact, no need to read config file again
        if (!$configIntact) {
            if (!$splitFlag) {
                $path = $configDir . "/{$fileName}.config.php";
                $configs = $this->loadConfigFile($path, $errNotFound);
                $data = array(
                    'intact'  => true,
                    'configs' => $configs
                );
                SzSystemCache::cache(SzSystemCache::CONFIG_APP_FILE, $fileName, $data);
            } else {
                $refreshFlag = false; // flag used to indicate whether cached data shall be refreshed or not

                if (is_array($key)) {
                    foreach ($key as $search) {
                        if (!SzUtility::checkArrayKey($search, $configs)) {
                            $path = $configDir . "/" . $fileName . "/{$search}.config.php";
                            $configs[$search] = $this->loadConfigFile($path);
                            $refreshFlag = true;
                        }
                    }
                } else {
                    if (!SzUtility::checkArrayKey($key, $configs)) {
                        $path = $configDir . "/" . $fileName . "/{$key}.config.php";
                        $configs[$key] = $this->loadConfigFile($path);
                        $refreshFlag = true;
                    }
                }

                // if some new data was read from config file, need to set to cache
                if ($refreshFlag) {
                    $data = array(
                        'intact'  => false,
                        'configs' => $configs
                    );
                    SzSystemCache::cache(SzSystemCache::CONFIG_APP_FILE, $fileName, $data);
                }
            }
        }

        return $this->loadConfig($key, $configs, $fileName);
    }

    /**
     * Load module config.
     *
     * @see SzConfig::loadConfig
     *
     * @param string $moduleName
     * @param string $fileName possible to contain path info in $fileName e.g "user/user.config.php"
     * @param string|array $key default null, means load whole file
     * @return mixed
     */
    public function loadModuleConfig($moduleName, $fileName, $key = null)
    {
        $configs = SzSystemCache::cache(SzSystemCache::CONFIG_MODULE_FILE . "-{$moduleName}", $fileName);
        if (!$configs) {
            $path = SzSystem::$MODULE_ROOT . '/' . $moduleName . '/' . SzSystem::$MODULE_VERS[$moduleName] . "/config/{$fileName}.config.php";
            $configs = $this->loadConfigFile($path);
            SzSystemCache::cache(SzSystemCache::CONFIG_MODULE_FILE . "-{$moduleName}", $fileName, $configs);
        }

        return $this->loadConfig($key, $configs, $fileName);
    }

    /**
     * Load config from configs via $key. <br/>
     *
     * <pre>
     * if $key is array, means load the multi depth part of $configs
     * e.g $configs = array(
     *                    'keyL1a' => array(
     *                        'keyL2a' => 1, 'keyL2b' => 2
     *                    ),
     *                    'keyL1b' => ...
     *                );
     *     loadConfig(array('keyL1a', 'keyL2a'), $configs); => 1
     * </pre>
     *
     * @param string|array $key
     * @param array $configs
     * @param string $fileName only used for logging when exception encountered
     * @throws SzException 10100
     * @return mixed
     */
    public function loadConfig($key, $configs, $fileName)
    {
        $config = null;
        if (is_null($key)) {
            // $key is null, means load the whole $configs, no need to do filter
            $config = $configs;
        } else if (is_array($key)) {
            // $key is array, multi depth configs
            $config = array();
            $cachedConfigs = $configs; // make a copy of $configs
            foreach ($key as $search) {
                // loop required key array, search $configs continuously
                if (!isset($cachedConfigs[$search])) {
                    $this->throwException(10100, array(implode('|', $key), $fileName));
                }
                $config[] = $cachedConfigs[$search];
            }
        } else if (!isset($configs[$key])) {
            // exception will be thrown here, if key exists with "NULL" value, since "isset" is used
            $this->throwException(10100, array($key, $fileName));
        } else {
            $config = $configs[$key];
        }

        return $config;
    }

    /**
     * Load & cache config file into php runtime.
     *
     * @param string $filePath
     * @param boolean $errNotFound whether to throw exception when filePath not found
     * @throws SzException 10101
     * @return array $configs
     */
    private function loadConfigFile($filePath, $errNotFound = true)
    {
        if (!file_exists($filePath)) {
            if ($errNotFound) {
                $this->throwException(10101, $filePath); // file not found!
            } else {
                return array();
            }
        }

        return include $filePath;
    }

    /**
     * In SzSystem initialization process, exception thrown could be in SzAutoload,
     * and at that time, SzException have not been loaded yet.
     * Use this utility function to throw exception.
     * @param int $code
     * @param string | array $message
     * @throws SzException
     * @throws Exception
     * @return void
     */
    private function throwException($code, $message)
    {
        if (class_exists('SzException')) {
            throw new SzException($code, $message);
        } else {
            if (is_array($message)) {
                $message = json_encode($message);
            }
            throw new Exception($message, $code);
        }
    }

    /**
     * Set cached system language name
     *
     * @param string $lang
     * @return void
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }
}