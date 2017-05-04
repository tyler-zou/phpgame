<?php
class SzPublishPlatformFacebook extends SzAbstractPublishPlatform
{

    /**
     * signed request algorithm
     *
     * @var string
     */
    protected $signedRequestAlgorithm = 'HMAC-SHA256';

    /**
     * login url of facebook
     *
     * <pre>
     * <b>Placeholder:</b>
     * client_id => APP_ID (publish.config.php)
     * redirect_uri => APP_CANVAS (publish.config.php)
     * </pre>
     *
     * @var string
     */
    protected $facebookLoginUrl = 'https://www.facebook.com/v2.1/dialog/oauth?client_id=%s&redirect_uri=%s&scope=user_friends,publish_actions';

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
            $data = isset($_REQUEST['signed_request']) ? $this->parseSignedRequest($_REQUEST['signed_request']) : null;
            if ($data && isset($data['user_id'])) {
                $platformId = $data['user_id'];
            } else if ((!$data || !isset($data['user_id'])) && $needLogin) {
                unset($_GET['act']);
                $loginUrl = '//' . $this->configs['WEB_HOST'] . '/apps/master/' . $this->configs['ONLINE_VER'] . '/www/index.php?' . http_build_query($_GET);
                echo '<script type="text/javascript">location.href="'. $loginUrl .'"</script>';
                exit;
            }
        }

        return $platformId;
    }

    /**
     * Parses a signedRequest and validates the signature.
     *
     * @param string $signedRequest A signed token
     * @return array The payload inside it or null if the sig is wrong
     */
    protected function parseSignedRequest($signedRequest)
    {
        list($encodedSig, $payload) = explode('.', $signedRequest, 2);

        // decode the data
        $sig = $this->base64UrlDecode($encodedSig);
        $data = json_decode($this->base64UrlDecode($payload), true);

        if (strtoupper($data['algorithm']) !== $this->signedRequestAlgorithm) {
            error_log("Facebook: Unknown algorithm. Expected {$this->signedRequestAlgorithm}.");
            return null;
        }

        // check sig
        $expectedSig = hash_hmac('sha256', $payload, $this->configs['APP_SECRET'], $raw = true);
        if ($sig != $expectedSig) {
            error_log("Facebook: Bad Signed JSON signature!");
            return null;
        }

        return $data;
    }

    /**
     * Base64 encoding that doesn't need to be urlencode()ed.
     * Exactly the same as base64_encode except it uses
     *   - instead of +
     *   _ instead of /
     *   No padded =
     *
     * @param string $input base64UrlEncoded string
     * @return string
     */
    protected function base64UrlDecode($input)
    {
        return base64_decode(strtr($input, '-_', '+/'));
    }

}