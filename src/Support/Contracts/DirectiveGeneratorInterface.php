<?php

namespace DM\LighthouseSchemaGenerator\Support\Contracts;

interface DirectiveGeneratorInterface
{
    public static function generate(string $methodName, string $relatedClassName, string $relationName): string;
}