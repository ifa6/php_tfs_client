<?php
class TestCase extends \PHPUnit_Framework_TestCase
{
    public function callPrivateMethod($obj, $method_name, $args=array())
    {
        $refl = new \ReflectionClass($obj);
        $method = $refl->getMethod($method_name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    public function getPrivateProperty($obj, $prop_name, $static=false)
    {
        $refl = new \ReflectionClass($obj);
        foreach ( $refl->getProperties() as $prop ) {
            if ( $prop->isStatic() == $static && $prop->getName() == $prop_name ) {
                $prop->setAccessible(true);
                return $prop;
            }
        }
    }
}
