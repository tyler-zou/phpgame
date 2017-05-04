<?php
class Builder
{

    const MODE_CREATE  = 'MODE_CREATE';
    const MODE_FLUSH   = 'MODE_FLUSH';
    const MODE_TEST    = 'MODE_TEST';
    private static $modes = array(
        self::MODE_CREATE, self::MODE_FLUSH, self::MODE_TEST
    );

    /**
     * build utility class
     *
     * @var Utility
     */
    private $util;
    /**
     * cli input params from command line
     *
     * @var array
     */
    private $argv;
    /**
     * script run mode
     *
     * @var string
     */
    private $mode;
    /**
     * script root, dir of script "exec.php"
     *
     * @var string
     */
    private $root;

    public function __construct()
    {
        $this->util = new Utility();
        $this->argv = $_SERVER['argv'];
        $this->root = __DIR__ . '/../';
    }

    /**
     * Print script start information.
     *
     * @return void
     */
    public function prePrintStartInfo()
    {
        $this->util->log('-----------------------------------');
        $this->util->log('SCRIPT STARTS:');
        $this->util->log('-----------------------------------');
        $this->util->log('$argv: ' . var_export($this->argv, true));
    }

    /**
     * Validate execution mode & argv.
     *
     * @throws Exception
     * @return void
     */
    public function preValidateArgvMode()
    {
        $mode = trim($this->argv[1]);
        if (count($this->argv) <= 1
            || !in_array($mode, self::$modes)
        ) {
            throw new Exception('Script execution mode not specified, or invalid!');
        } else {
            $this->mode = $mode;
            $this->util->log('-----------------------------------');
            $this->util->log('Script MODE: ' . $this->mode);
        }
    }

    /**
     * Run corresponding mode scripts.
     *
     * @return void
     */
    public function run()
    {
        $mode = strtolower($this->mode); // MODE_FLUSH => mode_flush
        $mode = $this->util->wordCamelize($mode, true); // mode_flush => ModeFlush

        $func = 'run' . $mode; // runModeFlush

        $this->util->log('-----------------------------------');
        $this->util->log("Script Builder::{$func} start: ");

        $params = $this->argv;
        unset($params[0], $params[1]); // remove script name & run mode
        $params = array_merge($params, array()); // reset the index from 0

        call_user_func_array(array($this, $func), $params);
    }

    /**
     * Run create script.
     *
     * <pre>
     * Create a new application source codes base with init codes. <br/>
     *
     * Step:
     * 1. check destination app path already exist or not
     * 2. copy init codes to destination app path
     * </pre>
     *
     * @param string $appName new application name
     * @param string $appRoot new application git source code root dir
     * @throws Exception
     */
    protected function runModeCreate($appName, $appRoot)
    {
        $appRoot = rtrim($appRoot, '/') . '/';
        $appPath = $appRoot . $appName;

        $this->util->log('-----------------------------------');
        $this->util->log("App path: {$appPath}");

        if (file_exists($appPath)) {
            throw new Exception('App path specified already exists!');
        } else {
            $this->util->log('App path does not exists, go on...');
        }

        $source = $this->root . 'create';
        $this->util->log('-----------------------------------');
        $this->util->log("Copy init codes from {$source} to {$appPath}");
        $this->util->copyDirectory($source, $appPath);

        $this->util->log('-----------------------------------');
        $this->util->log('Task finished at: ' . $this->util->setTime());
    }

    /**
     * Run flush script.
     *
     * <pre>
     * Flush framework | application, build the autoload.config.php config.
     * Also build *Vo | *VoList | *Model, etc...
     * </pre>
     *
     * @param string $path code root path, framework root or app root
     * @throws Exception
     * @return void
     */
    protected function runModeFlush($path)
    {
        // check $path & build $libPath & $configPath
        $path = rtrim($path, '/');
        $this->util->log('-----------------------------------');
        $this->util->log('Validate $path: ' . $path);
        $this->util->checkFolder($path);
        $libPath = $path . '/lib';
        $configPath = $path . '/config';

        // check $libPath & $configPath
        $this->util->log('Validate $libPath: ' . $libPath);
        $this->util->checkFolder($libPath, true);
        $this->util->log('Validate $configPath: ' . $configPath);
        $this->util->checkFolder($configPath, true);

        $this->processFlushModel($libPath, $configPath);
        $this->processFlushAutoload($path, $libPath, $configPath);
    }

    /**
     * Process MODE_FLUSH autoload logics.
     *
     * @param string $path
     * @param string $libPath
     * @param string $configPath
     * @return void
     */
    protected function processFlushAutoload($path, $libPath, $configPath)
    {
        // build autoload.config.php
        $this->util->log('-----------------------------------');
        $this->util->log('Build autoload.config.php...');
        $configs = $this->util->loopFolder($libPath);
        if ($configs) {
            foreach ($configs as $libFileName => $libFilePath) {
                $libFilePath = str_replace($path . '/', '', $libFilePath);
                $configs[$libFileName] = $libFilePath;
            }
        }
        $this->util->writeWholeFile($configPath . '/autoload.config.php', $this->util->exportConfigToString($configs));
    }

    /**
     * Process MODE_FLUSH model code generation logics.
     *
     * @param string $libPath
     * @param string $configPath
     * @return void
     */
    protected function processFlushModel($libPath, $configPath)
    {
        // build models
        $this->util->log('-----------------------------------');
        $this->util->log('Build orm models...');
        if (!file_exists($configPath . '/orm.config.php')) {
            $this->util->log('No orm.config.php file, pass...');
            return; // no orm config file
        }
        $ormConfigs = require $configPath . '/orm.config.php';
        if (!$ormConfigs || !is_array($ormConfigs)) {
            $this->util->log('No orm.config.php content, pass...');
            return; // orm config empty or invalid
        }

        // prepare smarty instance
        require __DIR__ . '/../../vendor/smarty/Smarty.class.php';
        $smarty = new Smarty();
        $smarty->setTemplateDir(__DIR__ . '/../templates')->setCompileDir('/tmp')
            ->setCacheDir('/tmp')->setConfigDir('/tmp');

        // loop orms to generate codes
        foreach ($ormConfigs as $ormName => $ormConfig) {
            // check orm dir
            $this->util->log('-----------------------------------');
            $this->util->log("ORM: {$ormName} ...");
            $this->util->log('-----------------------------------');
            $ormDir = $libPath . '/model/' . $ormName;
            if (!file_exists($ormDir)) {
                $this->util->log('Orm dir does not exist, create it: ' . $ormDir);
                $this->util->createDirectory($ormDir);
            } else {
                $this->util->log('Orm dir exists, go on...');
            }

            // generate vo code
            $this->generateOrmVo($smarty, $ormDir, $ormName, $ormConfig);
            // generate vo list code
            $this->generateOrmVoList($smarty, $ormDir, $ormName, $ormConfig);
            // 处理 ormConfig中的 tableShardCount字段值为-1时，优先读取自定义常量
            if ($ormConfig['tableShardCount'] === -1) {
                $tableArr = explode("_", $ormConfig['table']);
                $ormConfig['moduleName'] = strtolower($tableArr[0] . "_" . $tableArr[1]);
                $tableShardCountDefineName = strtoupper($ormConfig['table']).'_TABLE_SHARD_COLUMN';
                $ormConfig['tableShardCountValue'] = "defined(\"{$tableShardCountDefineName}\") ? {$tableShardCountDefineName} : null";
            }
            // generate model code
            $this->generateOrmModel($smarty, $ormDir, $ormName, $ormConfig);
        }
    }

    /**
     * Generate orm *Vo class code file.
     *
     * @param Smarty $smarty
     * @param string $ormDir
     * @param string $ormName
     * @param array $ormConfig
     * @return void
     */
    protected function generateOrmVo($smarty, $ormDir, $ormName, $ormConfig)
    {
        $columns = $toArrayColumns = $ormConfig['columns'];

        if ($ormConfig['toArrayFilter']) {
            foreach ($ormConfig['toArrayFilter'] as $columnId) {
                unset($toArrayColumns[$columnId]);
            }
        }

        $voName = $ormName . 'Vo';
        $dbType = $ormConfig['dbType'];

        $jsonColumns = $ormConfig['jsonColumns'];
        $jsonColumnsLengthWarn = array();
        $jsonColumnsLengthLimit = array();
        if ($jsonColumns) {
            foreach ($jsonColumns as $columnId => $lengthLimit) {
                $jsonColumnsLengthWarn[$columnId] = floor($lengthLimit * 0.9);
                $jsonColumnsLengthLimit[$columnId] = $lengthLimit;
            }
        }

        $smarty->clearAllAssign();
        $smarty->assign('VO_NAME', $voName);
        $smarty->assign('COLUMNS', $columns);
        $smarty->assign('DB_TYPE', $dbType);
        $smarty->assign('ORM_NAME', $ormName);
        $smarty->assign('TOARR_COLUMNS', $toArrayColumns);
        $smarty->assign('COL_COUNT', count($ormConfig['columns']));
        $smarty->assign('JSON_COLUMNS', array_keys($jsonColumns));
        $smarty->assign('JSON_COLUMNS_LENGTH_WARN', $jsonColumnsLengthWarn);
        $smarty->assign('JSON_COLUMNS_LENGTH_LIMIT', $jsonColumnsLengthLimit);

        $codes = '<?php' . PHP_EOL . $smarty->fetch('model/vo.tpl');
        $this->util->writeWholeFile("{$ormDir}/{$voName}.class.php", $codes);
    }

    /**
     * Generate orm *VoList class code file.
     *
     * @param Smarty $smarty
     * @param string $ormDir
     * @param string $ormName
     * @param array $ormConfig
     * @return void
     */
    protected function generateOrmVoList($smarty, $ormDir, $ormName, $ormConfig)
    {
        if (!$ormConfig['isList']) {
            return;
        }
        $voName = $ormName . 'Vo';
        $listName = $ormName . 'VoList';
        $dbType = $ormConfig['dbType'];

        $smarty->clearAllAssign();
        $smarty->assign('VO_NAME', $voName);
        $smarty->assign('LIST_NAME', $listName);
        $smarty->assign('DB_TYPE', $dbType);
        $smarty->assign('ORM_NAME', $ormName);

        $codes = '<?php' . PHP_EOL . $smarty->fetch('model/volist.tpl');
        $this->util->writeWholeFile("{$ormDir}/{$listName}.class.php", $codes);
    }

    /**
     * Generate orm *Model class code file.
     *
     * @param Smarty $smarty
     * @param string $ormDir
     * @param string $ormName
     * @param array $ormConfig
     * @return void
     */
    protected function generateOrmModel($smarty, $ormDir, $ormName, $ormConfig)
    {
        $voName = $ormName . 'Vo';
        $listName = $ormName . 'VoList';
        $modelName = $ormName . 'Model';
        $dbType = $ormConfig['dbType'];

        $smarty->clearAllAssign();
        $smarty->assign('ORM_NAME', $ormName);
        $smarty->assign('VO_NAME', $voName);
        $smarty->assign('LIST_NAME', $listName);
        $smarty->assign('DB_TYPE', $dbType);
        $smarty->assign('MODEL_NAME', $modelName);
        $smarty->assign('CONFIG', $ormConfig);
        $smarty->assign('COL_COUNT', count($ormConfig['columns']));

        $codes = '<?php' . PHP_EOL . $smarty->fetch('model/model.tpl');
        $this->util->writeWholeFile("{$ormDir}/{$modelName}.class.php", $codes);
    }

    /**
     * Run test script.
     *
     * <pre>
     * Initialize PHPUnit runtime environment, and run all the test scripts
     * under the dir of given "$path/test".
     * </pre>
     *
     * @param string $path code root path, framework root or app root
     * @return void
     */
    protected function runModeTest($path = null)
    {
        $frameworkPath = __DIR__ . '/../..';

        if (!is_null($path)) {
            // test path given
            $path = rtrim($path, '/');
        } else {
            // test target is framework
            $path = $frameworkPath;
        }

        // set phpunit include path
        $this->util->log('-----------------------------------');
        $this->util->log('Build PHPUnit include path: ');
        $phpunitIncludePath = $frameworkPath . '/vendor/phpunit';
        $includePath = ini_get('include_path');
        if ($includePath[strlen($includePath) - 1] == ':') { // last character of current include path is ':'
            $includePath .= $phpunitIncludePath;
        } else {
            $punctuation = (in_array(PHP_OS, array('WINNT','WIN32','Windows'))) ? ';' : ':';
            $includePath .= "{$punctuation}{$phpunitIncludePath}";
        }
        ini_set('include_path', $includePath);
        $this->util->log($includePath);

        // detect test case scripts, and generate test configuration xml
        $this->util->log('-----------------------------------');
        $this->util->log('Generate PHPUnit configuration xml: ');
        $xml = $this->generateConfigXml(
                    array(
                        'verbose' => 'true', // more information in test running
                        'colors' => 'true',  // color display in test running
                    ),
                    array("suit-{$path}" => "{$path}/test")
                );
        $this->util->writeWholeFile('/tmp/phpunit.xml', $xml);
        $this->util->log($xml);

        // run test cases
        $this->util->log('-----------------------------------');
        $this->util->log('Run: ');
        $_SERVER['argv'] = array();
        $_SERVER['argv'][0] = __FILE__;
        $_SERVER['argv'][1] = '--configuration=/tmp/phpunit.xml';
        $_SERVER['argv'][2] = '--debug';
        define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');
        require $phpunitIncludePath . '/PHPUnit/Autoload.php';
        PHPUnit_TextUI_Command::main();
    }

    /**
     * Generate phpunit config xml file content.
     *
     * @param array $testConfigs
     * @param array $testSuits
     * @return string $xml
     */
    protected function generateConfigXml($testConfigs, $testSuits)
    {
        if (!$testConfigs || !is_array($testConfigs) ||
            !$testSuits || !is_array($testSuits)
        ) {
            return ''; // no config found to generate the xml config file
        }

        // start
        $xml = '<?xml version="1.0" encoding="utf-8" ?>';
        $xml .= '<phpunit';
        foreach ($testConfigs as $configKey => $configValue) {
            $xml .= " {$configKey}=\"{$configValue}\"";
        }
        $xml .= '>';

        // build testsuites
        if ($testSuits && is_array($testSuits)) {
            $xml .= '<testsuites>';
            foreach ($testSuits as $suitName => $suitPath) {
                try {
                    $this->util->checkFolder($suitPath); // make sure test cases really exist
                } catch (Exception $e) {
                    $this->util->log("Expected test cases path: {$suitPath} is empty, ignore it.");
                    continue;
                }
                $xml .= "<testsuite name=\"{$suitName}\">";
                $xml .= "<directory suffix=\"Test.php\">{$suitPath}</directory>";
                $xml .= '</testsuite>';
            }
            $xml .= '</testsuites>';
        }

        // end
        $xml .= '</phpunit>';

        return $xml;
    }

}