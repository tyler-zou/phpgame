<?php
class SzException extends Exception
{

    const EX_FILE_NAME = 'exception'; // exception config file name of the framework

    const EX_TYPE_FWK = '1'; // start number of framework exception
    const EX_TYPE_APP = '2'; // start number of application exception
    const EX_TYPE_MDU = '3'; // start number of module exception

    /**
     * Construction, convert exception data from SzException format to Exception format.
     *
     * @param int $code
     * @param array $params default null, means no params given
     * @param string $moduleName module name, default null, required when exception was thrown in some module
     * @return SzException
     */
    public function __construct($code, $params = null, $moduleName = null)
    {
        parent::__construct('', $code);
        $this->message = $this->getExMsg($params, $moduleName);
    }

    /**
     * Get formatted exception message.
     *
     * @param array $params default null
     * @param string $moduleName
     * @return string $message
     */
    public function getExMsg($params, $moduleName)
    {
        // determine exception type
        $code = (string)$this->getCode();
        $type = $code[0];

        // get message
        $message = '';
        switch ($type) {
            case self::EX_TYPE_FWK:
                $message = SzConfig::get()->loadFrameConfig(self::EX_FILE_NAME, $this->getCode());
                break;
            case self::EX_TYPE_APP:
                $message = SzConfig::get()->loadAppConfig(self::EX_FILE_NAME, $this->getCode());
                break;
            case self::EX_TYPE_MDU:
                $message = SzConfig::get()->loadModuleConfig($moduleName, self::EX_FILE_NAME, $this->getCode());
                break;
            default:
                $message = 'Error message type not defined!';
                break;
        }

        // input parameters
        if (!is_null($params)) {
            if (!is_array($params)) {
                $params = array($params);
            }
            $message = vsprintf($message, $params);
        }

        return $message;
    }
}