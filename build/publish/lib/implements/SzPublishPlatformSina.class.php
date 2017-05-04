<?php
class SzPublishPlatformSina extends SzAbstractPublishPlatform
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
        $platformId = null;

        if (isset($_REQUEST['pf_token']) // "pf_token" to replace the "signed_request" given
            && $_REQUEST['pf_token'] == SzPublish::MAGIC_TOKEN // it's magic token
            && isset($_REQUEST['user_id']) // also userId given
        ) {
            // developer magic token detected
            $platformId = $_REQUEST['user_id'];
        } else {
            // regular facebook login process
            $platformId = isset($_REQUEST['wyx_user_id']) ? $_REQUEST['wyx_user_id'] : null;
        }

        return $platformId;
    }

}