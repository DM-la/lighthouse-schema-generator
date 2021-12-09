<?php

namespace DM\LighthouseSchemaGenerator\Support;

class DirectiveGenerator
{
    /**
     * @param string $fieldName
     * @param string $classOrColumnName
     * @param string $relationName
     * @return string
     */
    public static function generate(string $fieldName, string $classOrColumnName, string $relationName): string
    {
        $data = '';

        if (in_array($relationName, RelationTypes::SINGLE_RELATION_TYPES)) {
            $data = SingleRelationDirectiveGenerator::generate($fieldName, $classOrColumnName, lcfirst($relationName));
        } elseif (in_array($relationName, RelationTypes::MULTIPLE_RELATION_TYPES)) {
            $data = MultipleRelationDirectiveGenerator::generate($fieldName, $classOrColumnName, lcfirst($relationName));
        }

        return $data;
    }
}
