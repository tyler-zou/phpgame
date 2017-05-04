<?php
class SzPublishPlatformVk extends SzAbstractPublishPlatform
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
            $data = (isset($_REQUEST['auth_key']) && isset($_REQUEST['viewer_id'])) ? $this->validateSignature($_REQUEST['auth_key'], $_REQUEST['viewer_id']) : null;
            if ($data) {
                $platformId = $_REQUEST['viewer_id'];
            }
        }

        return $platformId;
    }

    /**
     * Validates the signature.
     *
     * @param string $signature A signed token
     * @param string $platformId
     * @return boolean
     */
    protected function validateSignature($signature, $platformId)
    {
        return (md5($this->configs['APP_ID'] . '_' . $platformId . '_' . $this->configs['APP_SECRET']) == $signature);
    }
}