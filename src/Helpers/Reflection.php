<?php

namespace DM\LighthouseSchemaGenerator\Helpers;

use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionNamedType;
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

    /**
     * @param ReflectionMethod $method
     * @return ReflectionNamedType|null
     */
    public function getReturnType(ReflectionMethod $method): ?ReflectionNamedType
    {
        return $method->getReturnType();
    }
}