<?php
/**
 * <b>NOTE</b>: <br/>
 * There should be one method named "execute" implemented in extender class. <br/>
 * The params number is various, and the types of the params shall be defined in the $this->paramTypes. <br/>
 * And the return value shall be "SzResponse".
 */
abstract class SzAbstractAction
{

    const TYPE_INT     = 'TYPE_INT';
    const TYPE_FLOAT   = 'TYPE_FLOAT';
    const TYPE_STRING  = 'TYPE_STRING';
    const TYPE_BOOLEAN = 'TYPE_BOOLEAN';
    const TYPE_ARRAY   = 'TYPE_ARRAY';
    const TYPE_JSON    = 'TYPE_JSON';

    const TYPE_INT_EMPTY     = 0;
    const TYPE_FLOAT_EMPTY   = 0.0;
    const TYPE_STRING_EMPTY  = '';
    const TYPE_BOOLEAN_EMPTY = false;
    const TYPE_ARRAY_EMPTY   = '[]';

    /**
     * the definition of valid input param types of the "execute" method of this action
     * <pre>
     * e.g
     *     0 => TYPE_INT
     *     1 => TYPE_FLOAT
     *     2 => TYPE_STRING
     *     ...
     * </pre>
     *
     * @var array
     */
    protected $paramTypes = array();

    /**
     * Validate the types of input params are valid or not.
     *
     * @param array $params passed as reference
     * @throws SzException 10404
     * @return void
     */
    public function validateParams(&$params)
    {
        if ($this->paramTypes && is_array($this->paramTypes)) { // params validation defined, run it
            foreach ($this->paramTypes as $paramIndex => $paramType) {
                if (!SzUtility::checkArrayKey($paramIndex, $params)) {
                    throw new SzException(10404); // index not exist, means input params count invalid
                }
                $this->validateParam($params[$paramIndex], $paramType);
            }
        }
    }

    /**
     * Validate the type of the given param is valid or not.
     * if the value of $param is 0, null, false, the value will be set to type default value.
     *
     * @param mixed $param passed as reference
     * @param string $paramType
     * @throws SzException 10403
     * @return void
     */
    protected function validateParam(&$param, $paramType)
    {
        if (empty($param)) {
            switch ($paramType) {
                case self::TYPE_INT:
                    $param = self::TYPE_INT_EMPTY;
                    break;
                case self::TYPE_FLOAT:
                    $param = self::TYPE_FLOAT_EMPTY;
                    break;
                case self::TYPE_STRING:
                    $param = self::TYPE_STRING_EMPTY;
                    break;
                case self::TYPE_BOOLEAN:
                    $param = self::TYPE_BOOLEAN_EMPTY;
                    break;
                case self::TYPE_ARRAY:
                case self::TYPE_JSON:
                    $param = json_decode(self::TYPE_ARRAY_EMPTY);
                    break;
                default:
                    throw new SzException(10403, $paramType);
                    break;
            }
        } else {
            switch ($paramType) {
                case self::TYPE_INT:
                    SzValidator::validateInt($param);
                    break;
                case self::TYPE_FLOAT:
                    SzValidator::validateFloat($param);
                    break;
                case self::TYPE_STRING:
                    SzValidator::validateString($param);
                    break;
                case self::TYPE_BOOLEAN:
                    SzValidator::validateBoolean($param);
                    break;
                case self::TYPE_ARRAY:
                    SzValidator::validateArray($param);
                    break;
                case self::TYPE_JSON:
                    SzValidator::validateJson($param);
                    $param = json_decode($param, true);
                    break;
                default:
                    throw new SzException(10403, $paramType);
                    break;
            }
        }
    }

    /**
     * Build the default SzResponse with only body filled. <br/>
     * Also it's possible to give no param to build an empty SzResponse.
     *
     * @param string|array $body default null
     * @return SzResponse
     */
    protected function buildResponse($body = null)
    {
        return new SzResponse($body);
    }

}