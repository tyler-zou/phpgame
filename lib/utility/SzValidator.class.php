<?php
class SzValidator
{

    /**
     * Validate input object is the instance of target class.
     *
     * @param object $inputObject
     * @param string $targetClass
     * @throws SzException 10215
     * @return void
     */
    public static function validateObjectInstanceOf($inputObject, $targetClass)
    {
        if (!($inputObject instanceof $targetClass)) {
            throw new SzException(10215, array(get_class($inputObject), $targetClass));
        }
    }

    /**
     * Validate target array has all key required.
     *
     * @param array $requiredKeys array of keys
     * @param array $inputArray array to be converted
     * @throws SzException 10216, 10217
     * @return void
     */
    public static function validateArrayKeys($requiredKeys, $inputArray)
    {
        if (!is_array($requiredKeys) || !$inputArray) {
            throw new SzException(10216);
        }
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $inputArray)) {
                throw new SzException(10217, $key);
            }
        }
    }

    /**
     * Validate input parameters are all int.
     *
     * @param mixed $input passed as reference
     * @throws SzException 10218, 10219
     * @return void
     */
    public static function validateInt(&$input)
    {
        if (is_array($input)) {
            foreach ($input as $param) {
                self::validateInt($param);
            }
        } else {
            if (!is_numeric($input)) {
                throw new SzException(10218, var_export($input, true));
            }
            $input += 0;
            if (!is_int($input)) {
                throw new SzException(10219, var_export($input, true));
            }
        }
    }

    /**
     * Validate input parameters are all float.
     *
     * @param mixed $input passed as reference
     * @throws SzException 10218, 10220
     * @return void
     */
    public static function validateFloat(&$input)
    {
        if (is_array($input)) {
            foreach ($input as $param) {
                self::validateFloat($param);
            }
        } else {
            if (!is_numeric($input)) {
                throw new SzException(10218, var_export($input, true));
            }
            $input += 0;
            if (!is_float($input)) {
                throw new SzException(10220, var_export($input, true));
            }
        }
    }

    /**
     * Validate input parameters are all number.
     *
     * @param mixed $input
     * @throws SzException 10218
     * @return void
     */
    public static function validateNumeric($input)
    {
        if (is_array($input)) {
            foreach ($input as $param) {
                self::validateNumeric($param);
            }
        } else {
            if (!is_numeric($input)) {
                throw new SzException(10218, var_export($input, true));
            }
        }
    }

    /**
     * Validate input parameters are all number or null.
     *
     * @param mixed $input
     * @throws SzException 10221
     * @return void
     */
    public static function validateNumericOrNull($input)
    {
        if (is_array($input)) {
            foreach ($input as $param) {
                self::validateNumericOrNull($param);
            }
        } else {
            if (!is_numeric($input) && !is_null($input)) {
                throw new SzException(10221, var_export($input, true));
            }
        }
    }

    /**
     * Validate input parameters are all boolean.
     *
     * @param mixed $input
     * @throws SzException 10222
     * @return void
     */
    public static function validateBoolean($input)
    {
        if (is_array($input)) {
            foreach ($input as $param) {
                self::validateBoolean($param);
            }
        } else {
            if (!is_bool($input) && strtolower($input) != 'true' && strtolower($input) != 'false') {
                throw new SzException(10222, var_export($input, true));
            }
        }
    }

    /**
     * Validate input parameters are all string.
     *
     * @param mixed $input
     * @throws SzException 10223
     * @return void
     */
    public static function validateString($input)
    {
        if (is_array($input)) {
            foreach ($input as $param) {
                self::validateString($param);
            }
        } else {
            if (!is_string($input)) {
                throw new SzException(10223, var_export($input, true));
            }
        }
    }

    /**
     * Validate input parameters are all string or number.
     *
     * @param mixed $input
     * @throws SzException 10224
     * @return void
     */
    public static function validateStringOrNumeric($input)
    {
        if (is_array($input)) {
            foreach ($input as $param) {
                self::validateStringOrNumeric($param);
            }
        } else {
            if (!is_string($input) && !is_numeric($input)) {
                throw new SzException(10224, var_export($input, true));
            }
        }
    }

    /**
     * Validate input parameters are all string or null.
     *
     * @param mixed $input
     * @throws SzException 10225
     * @return void
     */
    public static function validateStringOrNull($input)
    {
        if (is_array($input)) {
            foreach ($input as $param) {
                self::validateStringOrNull($param);
            }
        } else {
            if (!is_string($input) && !is_null($input)) {
                throw new SzException(10225, var_export($input, true));
            }
        }
    }

    /**
     * Validate input parameters are all json.
     *
     * @param mixed $input
     * @throws SzException 10226
     * @return void
     */
    public static function validateJson($input)
    {
        if (is_array($input)) {
            foreach ($input as $param) {
                self::validateJson($param);
            }
        } else {
            $tmp = json_decode($input, true);
            if (is_null($tmp) || !is_array($tmp)) {
                throw new SzException(10226, var_export($input, true));
            }
        }
    }

    /**
     * Validate input parameters are all meaningful time string.
     *
     * @param mixed $input
     * @throws SzException 10227
     * @return void
     */
    public static function validateTimeString($input)
    {
        if (is_array($input)) {
            foreach ($input as $param) {
                self::validateTimeString($param);
            }
        } else {
            $tmp = strtotime($input);
            if (false === $tmp || $tmp <= 0) {
                throw new SzException(10227, var_export($input, true));
            }
        }
    }

    /**
     * Use regular expressions to validate time string.
     *
     * <pre>
     * The format of the time string have to be "YYYY-mm-dd HH:ii:ss".
     * </pre>
     *
     * @param mixed $input
     * @throws SzException 10229
     * @return void
     */
    public static function validateStrictTimeString($input)
    {
        if (is_array($input)) {
            foreach ($input as $param) {
                self::validateStrictTimeString($param);
            }
        } else {
            $result = preg_match('/^(\d{4})-(\d\d)-(\d\d)\s(\d\d):(\d\d):(\d\d)$/', $input);
            if (false === $result || $result <= 0) {
                throw new SzException(10229, var_export($input, true));
            }
        }
    }

    /**
     * Validate input param is array.
     *
     * @param mixed $input
     * @throws SzException 10228
     * @return void
     */
    public static function validateArray($input)
    {
        if (!is_array($input)) {
            throw new SzException(10228, var_export($input, true));
        }
    }

    /**
     * Validate input param is ip address string.
     *
     * @param mixed $input
     * @throws SzException 10230
     * @return void
     */
    public static function validateIpAddress($input) {
        if (!filter_var($input, FILTER_VALIDATE_IP)) {
            throw new SzException(10230, var_export($input, true));
        }
    }

}