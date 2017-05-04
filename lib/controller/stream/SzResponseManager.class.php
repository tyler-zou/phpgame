<?php
class SzResponseManager
{

    const PERSIST_UPDATE_BODY_KEY = 'UPDATE';
    const PERSIST_DELETE_BODY_KEY = 'DELETE';

    /**
     * protocol header to send to the client,
     * normally it's not changeable
     *
     * @var string
     */
    private $protocol = 'HTTP/1.1';

    /**
     * content type to send (a complete mime-type),
     * json type "application/json" as default
     *
     * @var string
     */
    private $contentType = 'application/json';

    /**
     * buffer list of headers <br/>
     * headerKey => headerValue
     *
     * @var array
     */
    private $headers = array();

    /**
     * buffer for response message
     *
     * @var array
     */
    private $body = array();

    /**
     * file to be downloaded
     * array($fileName, $filePath)
     *
     * @var array
     */
    private $file = array();

    /**
     * the charset the response body is encoded with
     *
     * @var string
     */
    private $charset = 'UTF-8';

    /**
     * flag used to identify whether data should be compressed in the response
     *
     * @var boolean
     */
    private $needCompress = false;

    /**
     * json_encode options, setting in app.config.php, default 0
     *
     * @var int
     */
    private $jsonEncodeOptions = 0;

    /**
     * the code value in the default 'application/json' format response body <br/>
     * default 0 means no error response, if not 0, means error response <br/>
     * array('code' => ..., 'msg' => ...) <br/>
     *
     * @var int
     */
    private $code = 0;

    /**
     * response counter
     *
     * @var int
     */
    public $responseCount = 0;

    /**
     * Initialize SzResponse.
     *
     * @return SzResponseManager
     */
    public function __construct()
    {
        $this->needCompress = SzConfig::get()->loadAppConfig('app', 'API_COMPRESS');
        $this->needBase64 = SzConfig::get()->loadAppConfig('app', 'BASE64_ENCODE');
        $this->jsonEncodeOptions = SzConfig::get()->loadAppConfig('app', 'RESPONSE_JSON_OPTIONS');
    }

    /**
     * Merge array of SzResponse into the responses already in the manager.
     *
     * @param array $responses
     * @return void
     */
    public function mergeResponses($responses)
    {
        if (!$responses || !is_array($responses)) {
            return;
        }
        foreach ($responses as $response) {
            if (!($response instanceof SzResponse)) {
                continue; // invalid response, do nothing with it
            }
            $this->mergeResponse($response);
        }
    }

    /**
     * Merge one SzResponse into the responses already in the manager.
     *
     * @param SzResponse $response
     * @return void
     */
    public function mergeResponse($response)
    {
        if (!($response instanceof SzResponse)) {
            return;
        }

        // content type
        if (!is_null($response->getContentType())) {
            $this->contentType = $response->getContentType();
        }

        // headers
        if ($response->getHeaders()) {
            $this->headers = array_merge($this->headers, $response->getHeaders());
        }

        // body
        $body = $response->getBody();
        if ($body) {
            $this->adaptResponseBody($body);
            $this->body[] = $body;
        }

        // file
        if ($response->getFile()) {
            $this->file = $response->getFile();
        }
    }

    /**
     * Merge the SzResponse from SzPersister::persist() into the manager.
     *
     * <pre>
     * The response format of which was generated in the SzPersister and would be sent to client:
     *
     * array(
     *     'UPDATE' => array(
     *         //-------------------
     *         // LIST MODE:
     *         'Item' => array( // orm name
     *             $userId => array(
     *                 $itemId => array(
     *                     $columnName => $columnValue,
     *                     ...
     *                 ),
     *                 $itemId => ...,
     *                 ...
     *             ),
     *             $userId => ...,
     *             ...
     *         ),
     *         'Soldier' => ...,
     *         //-------------------
     *         // SINGLETON MODE:
     *         'Profile' => array(
     *             $userId => array(
     *                 $columnName => $columnValue,
     *                 ...
     *             ),
     *             $userId => ...,
     *             ...
     *         ),
     *         'Token' => ...,
     *     )
     * )
     * </pre>
     *
     * @see SzPersister::persist()
     *
     * @return void
     */
    private function mergePersistResponse()
    {
        $persisterResponseList = SzPersister::get()->getResponseList();

        $updateList = $deleteList = array();

        if (SzUtility::checkArrayKey(SzResponseManager::PERSIST_UPDATE_BODY_KEY, $persisterResponseList)) {
            $updateList = $persisterResponseList[SzResponseManager::PERSIST_UPDATE_BODY_KEY];
            $this->filterResponseBody($updateList);
            $this->adaptResponseBody($updateList);
        }
        if (!empty($updateList)) {
            $this->body[SzResponseManager::PERSIST_UPDATE_BODY_KEY] = $updateList;
        }

        if (SzUtility::checkArrayKey(SzResponseManager::PERSIST_DELETE_BODY_KEY, $persisterResponseList)) {
            $deleteList = $persisterResponseList[SzResponseManager::PERSIST_DELETE_BODY_KEY];
            $this->filterResponseBody($deleteList);
        }
        if (!empty($deleteList)) {
            $this->body[SzResponseManager::PERSIST_DELETE_BODY_KEY] = $deleteList;
        }
    }

    /**
     * response body filter  var app.config.php PERSIST_RESULT_FILTER.
     *
     * @param $body
     */
    private function filterResponseBody(&$body)
    {
        // filter unnecessary orm data
        $persistFilter = SzConfig::get()->loadAppConfig('app', 'PERSIST_RESULT_FILTER');
        if ($persistFilter && is_array($persistFilter)) {
            foreach ($persistFilter as $ormName) {
                unset($body[$ormName]);
            }
        }
    }

    /**
     * response body force to object var app.config.php PERSIST_RESULT_FORCE_OBJECT.
     *
     * @param $body
     */
    private function adaptResponseBody(&$body)
    {
        // get app.config.php PERSIST_RESULT_FORCE_OBJECT data
        $adaptOrm = SzConfig::get()->loadAppConfig('app', 'PERSIST_RESULT_FORCE_OBJECT');
        if ($adaptOrm && is_array($adaptOrm)) {
            foreach ($adaptOrm as $ormName) {
                if (is_array($body) && SzUtility::checkArrayKey($ormName, $body)) {
                    foreach ($body[$ormName] as $pkField => $response) {
                        $body[$ormName][$pkField] = (object)$response;
                    }
                }
            }
        }
    }

    /**
     * Send out the HTTP response.
     *
     * @throws SzException 10208
     * @return void
     */
    public function send()
    {
        $this->sendHeader("{$this->protocol} 200 OK"); // FIXME always 200, with exception code in response body

        if ($this->file) {
            // download file
            list($fileName, $filePath) = $this->file;
            $file = "{$filePath}/{$fileName}";
            if (!file_exists($file) || !is_file($file) || !is_readable($file)) {
               throw new SzException(10208, $filePath);
            }

            $this->sendHeader('Content-Description: File Transfer');
            $this->sendHeader('Content-Type: application/octet-stream');
            $this->sendHeader("Content-Disposition: attachment; filename=\"{$fileName}\"");
            $this->sendHeader('Content-Transfer-Encoding: binary');
            $this->sendHeader('Connection: Keep-Alive');
            $this->sendHeader('Expires: 0');
            $this->sendHeader('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            $this->sendHeader('Pragma: public');
            $this->sendHeader('Content-Length: ' . filesize($file));
            readfile($file);
        } else {
            // send response
            $this->formatContent();
            $this->formatContentLength();
            $this->formatContentType();
            if ($this->headers) {
                foreach ($this->headers as $headerKey => $headerValue) {
                    $this->sendHeader($headerKey, $headerValue);
                }
            }

            $this->sendContent();
        }
    }

    /**
     * Sends a header to the client.
     *
     * @param string $name the header name
     * @param string $value the header value, default null
     * @return void
     */
    private function sendHeader($name, $value = null)
    {
        if (!headers_sent()) {
            if (is_null($value)) {
                header($name);
            } else {
                header("{$name}: {$value}");
            }
        }
    }

    /**
     * Encode body from array to json string, also compress it if required.
     *
     * @return void
     */
    private function formatContent()
    {
        if ($this->getContentType() == 'application/json') {
            // API request
            $this->body = array(
                // "code" & "msg" are always included in the default response body
                'code' => $this->code,
                'msg'  => $this->body,
                'gmt'  => SzSystem::getSysTime(),
            );
            $this->mergePersistResponse();

            $this->body = json_encode($this->body, $this->jsonEncodeOptions);
            if ($this->needCompress) {
                $this->body = SzUtility::compress($this->body);
            }
            if ($this->needBase64) {
                $this->body = SzUtility::base64Encode($this->body, false);
            }
        } else {
            if ($this->getContentType() == 'text/html') {
                // HTML page, shall always be the first & only content in the body array
                $this->body = array_shift($this->body);
            } else if (is_array($this->body)) {
                // NOT HTML & JSON, also need encode array to json string
                $this->body = json_encode($this->body, $this->jsonEncodeOptions);
            }
        }
    }

    /**
     * Set header of Content-Length according to the length of $this->body.
     *
     * @return void
     */
    private function formatContentLength()
    {
        $length = 0;

        if ($this->body) {
            if (ini_get('mbstring.func_overload') & 2 && function_exists('mb_strlen')) {
                $length = mb_strlen($this->body, '8bit');
            } else {
                $length = strlen($this->body);
            }
        }

        $this->headers['Content-Length'] = $length;
    }

    /**
     * Set header of Content-Type.
     *
     * @return void
     */
    private function formatContentType()
    {
        $whiteList = array(
            'application/javascript',
            'application/json',
            'application/xml',
            'application/rss+xml'
        );

        $charset = false;
        if ($this->charset
            && (strpos($this->contentType, 'text/') === 0 || in_array($this->contentType, $whiteList))
        ) {
            $charset = true;
        }

        if ($charset) {
            $this->headers['Content-Type'] = "{$this->contentType}; charset={$this->charset}";
        } else {
            $this->headers['Content-Type'] = "{$this->contentType}";
        }
    }

    /**
     * Sends a content string to the client.
     *
     * @return void
     */
    private function sendContent()
    {
        echo $this->body;
    }

    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    //-* SETTERS & GETTERS
    //-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-
    /**
     * @param array|string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return array|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param boolean $needCompress
     */
    public function setCompress($needCompress)
    {
        $this->needCompress = $needCompress;
    }

    /**
     * @return boolean
     */
    public function getCompress()
    {
        return $this->needCompress;
    }

    /**
     * @param string $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param array $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return array
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param array $headers
     */
    public function addHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }
}