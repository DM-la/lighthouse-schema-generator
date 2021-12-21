<?php

namespace DM\LighthouseSchemaGenerator\Parsers;

use ReflectionMethod;
use ReflectionException;
use ReflectionNamedType;
use Illuminate\Database\Eloquent\Model;
use DM\LighthouseSchemaGenerator\Helpers\Reflection;
use Illuminate\Database\Eloquent\Relations\Relation;
use DM\LighthouseSchemaGenerator\Support\DirectiveGenerator;
use ReflectionType;

class MethodParser
{
    /** @var Reflection */
    private $reflection;

    public function __construct(Reflection $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * @param Model $model
     * @param ReflectionMethod $method
     * @return string
     * @throws ReflectionException
     */
    public function parse(Model $model, ReflectionMethod $method): string
    {
        $data = '';

        /** @var ReflectionNamedType $returnType */
        $returnType = $this->reflection->getReturnType($method);
        /** @phpstan-ignore-next-line */
        if ($returnType && (! $returnType->isBuiltin()) && $method->hasReturnType()) {
            $methodName = $method->getName();
            $relation = $this->reflection->reflectionClass($returnType->getName());
            if ($method->getNumberOfParameters() == 0 && $relation->isSubclassOf(Relation::class)) {
                $relatedClassName = class_basename($method->invoke($model)->getRelated());
                $relationClassName = $relation->getShortName();
                $data .= DirectiveGenerator::generate($methodName, $relatedClassName, $relationClassName);
            }
        }

        return $data;
    }
}
