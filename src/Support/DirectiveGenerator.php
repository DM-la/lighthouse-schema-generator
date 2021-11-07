<?php

namespace DM\LighthouseSchemaGenerator\Support;

class DirectiveGenerator
{
    /**
     * @param string $methodName
     * @param string $relatedClassName
     * @param string $relationName
     * @return string
     */
    public static function generate(string $methodName, string $relatedClassName, string $relationName): string
    {
        $data = '';

        if (in_array($relationName, RelationTypes::SINGLE_RELATION_TYPES)) {
            $data = SingleRelationDirectiveGenerator::generate($methodName, $relatedClassName, lcfirst($relationName));
        } elseif (in_array($relationName, RelationTypes::MULTIPLE_RELATION_TYPES)) {
            $data = MultipleRelationDirectiveGenerator::generate($methodName, $relatedClassName, lcfirst($relationName));
        }

        return $data;
    }
}