<?php

namespace DM\LighthouseSchemaGenerator\Support;

use DM\LighthouseSchemaGenerator\Support\Contracts\DirectiveGeneratorInterface;

class SingleRelationDirectiveGenerator implements DirectiveGeneratorInterface
{
    /**
     * @param string $methodName
     * @param string $relatedClassName
     * @param string $relationName
     * @return string
     */
    public static function generate(string $methodName, string $relatedClassName, string $relationName): string
    {
        return "    {$methodName}: $relatedClassName @{$relationName}\n";
    }
}