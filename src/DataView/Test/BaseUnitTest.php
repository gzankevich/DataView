<?php

namespace DataView\Test;

abstract class BaseUnitTest extends \PhpUnit_Framework_TestCase
{
    /**
     * Allows us to call a non-public method without having to subclass
     *
     * @param stdClass $object An object
     * @param string $methodName The name of the method to call
     * @param array $arguments The arguments to pass to the method
     * @return mixed Whatever the method returns
     */
    public function callNonPublicMethod($object, $methodName, array $arguments = array())
    {
        $class = new \ReflectionClass($object);

        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arguments);
    }
}
