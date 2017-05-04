<?php
class SzPublishPlatformTencent extends SzAbstractPublishPlatform
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
        return $_REQUEST['openid'];
    }

}