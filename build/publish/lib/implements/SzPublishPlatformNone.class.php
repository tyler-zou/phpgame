<?php
class SzPublishPlatformNone extends SzAbstractPublishPlatform
{

    /**
     * @see SzAbstractPublishPlatform::__construct
     */
    public function __construct($configs)
    {
        parent::__construct($configs);
    }

    /**
     * @see SzAbstractPublishPlatform::parsePlatformId
     */
    public function parsePlatformId($needLogin = true)
    {
        return 1; // null platform, means test environment, platformId 1 returned
    }

}