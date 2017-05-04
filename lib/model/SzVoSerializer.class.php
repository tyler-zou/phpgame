<?php
class SzVoSerializer
{

    /**
     * Serialize SzAbstractVo into string.
     *
     * @see SzAbstractVo::toPureArray
     *
     * @param SzAbstractVo $vo
     * @return string
     * <pre>
     * $vo->toPureArray()
     * </pre>
     */
    public static function serialize($vo)
    {
        return $vo->toPureArray();
    }

    /**
     * Unserialize string into SzAbstractVo.
     *
     * @see ReflectionClass::newInstanceArgs
     *
     * @param array $data
     * <pre>
     * array:
     * array(123, 456, 789, "name")
     * </pre>
     * @param $reflection
     * <pre>
     * 1. instanceof ReflectionClass with class name initialized
     * 2. string of voClassName
     * </pre>
     * @return object
     */
    public static function unserialize($data, $reflection)
    {
        if (!($reflection instanceof ReflectionClass)) {
            $reflection = new ReflectionClass($reflection);
        }

        return $reflection->newInstanceArgs($data);
    }

}