<?php
abstract class SzAbstractPublishPlatform
{

    /**
     * content of the config file "publish.config.php"
     *
     * @var array
     */
    protected $configs;

    /**
     * Initialize SzAbstractPublishPlatform.
     *
     * @param $configs
     * @return SzAbstractPublishPlatform
     */
    public function __construct($configs)
    {
        $this->configs = $configs;
    }

    /**
     * Parse the input params and get the platform id.
     *
     * @param boolean $needLogin default true, does it necessary to direct page to platform login page, if user token not found
     * @return string
     */
    abstract public function parsePlatformId($needLogin = true);

}