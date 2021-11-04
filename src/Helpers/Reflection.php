<?php

namespace DM\LighthouseSchemaGenerator\Helpers;

use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionException;

class Reflection
{
    /**
     * @param string|object $objectOrClass
     * @return ReflectionClass
     * @throws ReflectionException
     */
    public function reflectionClass($objectOrClass): ReflectionClass
    {
        return (new ReflectionClass($objectOrClass));
    }

    /**
     * @param object $object
     * @return ReflectionObject
     */
    public function reflectionObject(object $object): ReflectionObject
    {
        return (new ReflectionObject($object));
    }
}