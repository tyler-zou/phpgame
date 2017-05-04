<?php
class SzSmarty extends Smarty
{

    /**
     * @var SzSmarty
     */
    private static $instance;

    /**
     * Get instance of SzSmarty.
     *
     * @return SzSmarty
     */
    public static function get()
    {
        if (!self::$instance) {
            self::init();
        }
        return self::$instance;
    }

    /**
     * Initialize instance of SzSmarty.
     *
     * @return void
     */
    private static function init()
    {
        self::$instance = new SzSmarty();

        // set default app view dir
        $defaultViewDir = SzSystem::$APP_ROOT . '/view';
        if (file_exists($defaultViewDir)) {
            self::$instance->registerTemplateDir($defaultViewDir);
        }
    }

    /**
     * Initialize Smarty dirs.
     *
     * @return SzSmarty
     */
    public function __construct()
    {
        parent::__construct();
        $this->setCompileDir('/tmp');
        $this->setCacheDir('/tmp');
        $this->setConfigDir('/tmp');
    }

    /**
     * Register an new smarty template dir.
     *
     * @param string $dir
     * @return void
     */
    public function registerTemplateDir($dir)
    {
        if (SzFile::checkFolder($dir)) {
            $this->setTemplateDir($dir);
        }
    }

}