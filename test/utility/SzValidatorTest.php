<?php
require_once dirname(dirname(__DIR__)) . '/test/SzTestAbstract.class.php';

class SzValidatorTest extends SzTestAbstract
{

    /**
     * Test SzValidator::validateObjectInstanceOf
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10215
     */
    public function test_ValidateObjectInstanceOf_Error_10215()
    {
        SzValidator::validateObjectInstanceOf(new stdClass(), 'SzValidator');
    }

    /**
     * Test SzValidator::validateArrayKeys
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10216
     */
    public function test_ValidateArrayKeys_Error_1010216()
    {
        $array = array('key1' => 1, 'Key2' => 2, 'Key3' => 3);
        SzValidator::validateArrayKeys('key1', $array);
    }

    /**
     * Test SzValidator::validateArrayKeys
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10217
     */
    public function test_ValidateArrayKeys_Error_1010217()
    {
        $array = array('key1' => 1, 'Key2' => 2, 'Key3' => 3);
        SzValidator::validateArrayKeys(array('key1', 'Key5'), $array);
    }

    /**
     * Test SzValidator::validateInt
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10218
     */
    public function test_ValidateInt_Error_10218()
    {
        $array = array('A');
        SzValidator::validateInt($array);
    }

    /**
     * Test SzValidator::validateInt
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10219
     */
    public function test_ValidateInt_Error_10219()
    {
        $array = array(1.1);
        SzValidator::validateInt($array);
    }

    /**
     * Test SzValidator::validateFloat
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10218.
     */
    public function test_ValidateFloat_Error_10218()
    {
        $array = array(1.1, 1.2, 'A');
        SzValidator::validateFloat($array);
    }

    /**
     * Test SzValidator::validateFloat
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10220.
     */
    public function test_ValidateFloat_Error_10220()
    {
        $array = array(1.1, 1.2, 2);
        SzValidator::validateFloat($array);
    }

    /**
     * Test SzValidator::validateNumeric
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10218.
     */
    public function test_ValidateNumeric_Error_10218()
    {
        $array = array(1.1, 1.2, 2, null);
        SzValidator::validateNumeric($array);
    }

    /**
     * Test SzValidator::validateNumericOrNull
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10221
     */
    public function test_ValidateNumericOrNull_Error_10221()
    {
        $array = array(1.1, 1.2, 2, null, 'A');
        SzValidator::validateNumericOrNull($array);
    }

    /**
     * Test SzValidator::validateBoolean
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10222
     */
    public function test_ValidateBoolean_Error_10222()
    {
        $array = array(true, false, 1);
        SzValidator::validateBoolean($array);
    }

    /**
     * Test SzValidator::validateString
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10223
     */
    public function test_ValidateString_Error_10223()
    {
        $array = array('A', '1', 1);
        SzValidator::validateString($array);
    }

    /**
     * Test SzValidator::validateStringOrNumeric
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10224
     */
    public function test_ValidateStringOrNumeric_Error_10224()
    {
        $array = array('A', '1', 1, null);
        SzValidator::validateStringOrNumeric($array);
    }

    /**
     * Test SzValidator::validateStringOrNull
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10225
     */
    public function test_ValidateStringOrNull_Error_10225()
    {
        $array = array('A', '1', null, 1);
        SzValidator::validateStringOrNull($array);
    }

    /**
     * Test SzValidator::validateJson
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10226
     */
    public function test_ValidateJson_Error_10226()
    {
        $array = array('[1, 2, 3]', '{"1":1, "2":2, "3":3}', '[1:1, 2:2, 3:3]');
        SzValidator::validateJson($array);
    }

    /**
     * Test SzValidator::validateTimeString
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10227
     */
    public function test_ValidateTimeString_Error_10227()
    {
        $array = array('2014-01-01 00:00:00', '2014-01-01', '20140101', '20141301');
        SzValidator::validateTimeString($array);
    }

    /**
     * Test SzValidator::validateStrictTimeString
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10229
     */
    public function test_ValidateStrictTimeString_Error_10229()
    {
        $array = array('2014-01-01 00:00:00', '2014-01-01');
        SzValidator::validateStrictTimeString($array);
    }

    /**
     * Test SzValidator::validateArray
     *
     * @expectedException       SzException
     * @expectedExceptionCode   10228
     */
    public function test_ValidateArray_Error_10228()
    {
        $array = '1';
        SzValidator::validateArray($array);
    }


}