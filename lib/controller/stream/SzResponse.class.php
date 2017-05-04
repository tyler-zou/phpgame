<?php
class SzResponse
{

    /**
     * content type to send (a complete mime-type)
     *
     * @var string
     */
    private $contentType = null;
    /**
     * list of headers <br/>
     * headerKey => headerValue
     *
     * @var array
     */
    private $headers = array();
    /**
     * buffer for response message
     *
     * @var string|array
     */
    private $body = null;
    /**
     * file to be downloaded <br/>
     * array($fileName, $filePath)
     *
     * @var array
     */
    private $file = array();

    /**
     * Initialize response.
     *
     * @param string|array $body default null
     * @param array $headers default array()
     * @param string $contentType default null
     * @param array $file default array()
     * @return SzResponse
     */
    public function __construct(
        $body        = null,
        $headers     = array(),
        $contentType = null,
        $file        = array())
    {
        if (!is_null($body)) {
            $this->body = $body;
        }
        if ($headers) {
            $this->headers = $headers;
        }
        if ($contentType) {
            $this->contentType = $contentType;
        }
        if ($file) {
            $this->file = $file;
        }
    }

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
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

}