<?php
class SzParam
{

    /**
     * Get $_REQUEST.
     *
     * @return array $_REQUEST
     */
    public static function getWholeRequest()
    {
        return $_REQUEST;
    }

    /**
     * Get param from $_REQUEST.
     *
     * @param string $name
     * @param string $default null
     * @return string $value
     */
    public static function getReqParam($name, $default = null)
    {
        return isset($_REQUEST[$name]) ? SzUtility::escape($_REQUEST[$name]) : $default;
    }

    /**
     * Get $_POST.
     *
     * @return array $_POST
     */
    public static function getWholePost()
    {
        return $_POST;
    }

    /**
     * Get param from $_POST.
     *
     * @param string $name
     * @param string $default null
     * @return string $value
     */
    public static function getPostParam($name, $default = null)
    {
        return isset($_POST[$name]) ? SzUtility::escape($_POST[$name]) : $default;
    }

    /**
     * Get $_GET.
     *
     * @return array $_GET
     */
    public static function getWholeGet()
    {
        return $_GET;
    }

    /**
     * Get param from $_GET.
     *
     * @param string $name
     * @param string $default null
     * @return string $value
     */
    public static function getGetParam($name, $default = null)
    {
        return isset($_GET[$name]) ? SzUtility::escape($_GET[$name]) : $default;
    }

    /**
     * Get $_COOKIE.
     *
     * @return array $_COOKIE
     */
    public static function getWholeCookie()
    {
        return $_COOKIE;
    }

    /**
     * Get param from $_COOKIE.
     *
     * @param string $name
     * @param string $default null
     * @return string $value
     */
    public static function getCookieParam($name, $default = null)
    {
        return isset($_COOKIE[$name]) ? SzUtility::escape($_COOKIE[$name]) : $default;
    }

    /**
     * Set cookie param.
     *
     * @param string $name
     * @param string $value
     * @param int $expire expire timestamp
     * @param string $host default localhost
     * @return string $value which is set
     */
    public static function setCookieParam($name, $value, $expire, $host = 'localhost')
    {
        if ($host == 'localhost' || $host == '127.0.0.1') {
            setcookie($name, $value, $expire, '/');
        } else {
            setcookie($name, $value, $expire, '/', ".{$host}");
        }
        return $value;
    }

    /**
     * Parse the PHP request inputs.
     *
     * <pre>
     * The returned $inputs are all the params comes from "$_REQUEST['*']".
     * </pre>
     *
     * @return array $inputs
     */
    public static function parseRawInputs()
    {
        $inputs = SzParam::getReqParam('*');

        $needCompress = SzConfig::get()->loadAppConfig('app', 'API_COMPRESS'); // flag used to identify whether data compressed in the request
        $needBase64 = SzConfig::get()->loadAppConfig('app', 'BASE64_ENCODE'); // flag used to identify whether data shall be base64_encode after compress

        $rawInputs = '';
        if ($inputs) {
            if (is_string($inputs) && $needBase64) {
                $inputs = SzUtility::base64Decode($inputs);
            }
            if (is_string($inputs) && $needCompress) {
                $inputs = SzUtility::decompress($inputs, false);
            }

            $rawInputs = $inputs;
            if (is_string($inputs)) {
                $inputs = json_decode($inputs, true);
            }
            if (is_null($inputs) || !is_array($inputs)) { // means origin inputs are invalid json format
                $inputs = array();
            }
        }

        SzConfig::get()->loadAppConfig('app', 'API_REPEAT_CHECK') && self::checkApiSign($rawInputs);

        return $inputs;
    }

    /**
     * check api sign is valid
     *
     * @param $rawInputs
     * @throws SzException 10405
     */
    public static function checkApiSign($rawInputs)
    {
        $secret = SzConfig::get()->loadAppConfig('app', 'API_SIGN_SECRET');
        $params = array(
            '*' => $rawInputs,
            'halt' => SzParam::getReqParam('halt'),
        );
        if (SzParam::getReqParam('sign') != SzUtility::makeSign($params, $secret)) {
            throw new SzException(10405);
        }
    }
}