<?php

namespace DM\LighthouseSchemaGenerator\Support;

use DM\LighthouseSchemaGenerator\Support\Contracts\DirectiveGeneratorInterface;

class MultipleRelationDirectiveGenerator implements DirectiveGeneratorInterface
{
    public static function generate(string $methodName, string $relatedClassName, string $relationName): string
    {
        return "    {$methodName}: [{$relatedClassName}] @{$relationName}\n";
    }
}