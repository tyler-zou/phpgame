<?php
class SzPublishPlatformOgz extends SzAbstractPublishPlatform
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
            // 开发人员登录
            $platformId = $_REQUEST['user_id'];
        } else {
            // 正常 OGZ 登录
            $data = (isset($_REQUEST['sig']) && isset($_REQUEST['uid'])) ? $this->validateSignature() : null;
            if ($data) {
                $platformId = $_REQUEST['uid'];
            }
        }

        return $platformId;
    }

    /**
     * Validates the signature.
     *
     * @return boolean
     */
    protected function validateSignature()
    {
        $str = '';
        foreach ($_REQUEST as $key => $value) {
            if ($key == 'sig') {
                continue;
            }
            $str .= $key . '='. $value;
        }
        return (md5($str . $this->configs['APP_SECRET']) == $_REQUEST['sig']);
    }
}