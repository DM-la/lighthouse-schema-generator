<?php

namespace DM\LighthouseSchemaGenerator\Support\Contracts;

interface DirectiveGeneratorInterface
{
    public static function generate(string $fieldName, string $classOrColumnName, string $relationName = ''): string;
}