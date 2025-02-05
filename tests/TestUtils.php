<?php
declare(strict_types=1);

trait TestUtils
{
    /**
     * Call protected/private method of a class.
     * @throws ReflectionException
     */
    protected function callMethod($obj, $name, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        // $method->setAccessible(true); // uncomment if PHP < 8.1.0
        return $method->invokeArgs($obj, $args);
    }
}