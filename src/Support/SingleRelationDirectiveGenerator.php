<?php

namespace DmLa\LighthouseSchemaGenerator\Support;

use DmLa\LighthouseSchemaGenerator\Support\Contracts\DirectiveGeneratorInterface;

class SingleRelationDirectiveGenerator implements DirectiveGeneratorInterface
{
    /**
     * @param string $fieldName
     * @param string $classOrColumnName
     * @param string $relationName
     * @return string
     */
    public static function generate(string $fieldName, string $classOrColumnName, string $relationName = ''): string
    {
        return "    {$fieldName}: $classOrColumnName @{$relationName}\n";
    }
}
