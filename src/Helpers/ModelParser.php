<?php

namespace DM\LighthouseSchemaGenerator\Helpers;

use Exception;
use ReflectionMethod;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use DM\LighthouseSchemaGenerator\Support\DirectiveGenerator;

class ModelParser
{
    /** @var Reflection  */
    protected $reflection;

    public function __construct(Reflection $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * @param Model $model
     * @return string $data
     */
    public function generateSchema(Model $model): string
    {
        $data = '';
        $reflector = $this->reflection->reflectionObject($model);

        $data .= "type {$reflector->getShortName()} {\n";
        $data .= $this->parseColumns($model);

        $publicMethods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($publicMethods as $reflectionMethod) {
            try {
                $data .= $this->parseMethod($model, $reflectionMethod);
            } catch (Exception $exception) {
                continue;
            }
        }

        $data .= '}';

        return $data;
    }

    /**
     * @param Model $model
     * @param ReflectionMethod $method
     * @return string
     * @throws \ReflectionException
     */
    public function parseMethod(Model $model, ReflectionMethod $method): string
    {
        $data = '';

        $returnType = $this->reflection->getReturnType($method);
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

    /**
     * @param Model $model
     * @return string
     */
    protected function parseColumns(Model $model): string
    {
        $data = '';

        $table = $model->getTable();
        $columns = $this->getColumnListing($table);
        $connection = $model->getConnection();
        $types = $this->getTypes();

        foreach ($columns as $column) {
            $columnData = $connection->getDoctrineColumn($table, $column);
            $data .= "    {$column}: ";

            $columnType = $this->getColumnType($table, $column);

            switch (true) {
                case in_array($columnType, $types['intTypes']) && $columnData->getAutoincrement():
                    $data .= 'ID';
                    break;
                case in_array($columnType, $types['intTypes']):
                    $data .= "Int";
                    break;
                case in_array($columnType, $types['stringTypes']):
                    $data .= "String";
                    break;
                case $columnType === 'datetime':
                case in_array($columnType, $types['timeTypes']):
                    $data .= "DateTime";
                    break;
                case $columnType === 'date':
                    $data .= "Date";
                    break;
                case $columnType === 'datetimetz':
                    $data .= "DateTimeTz";
                    break;
                case in_array($columnType, $types['booleanTypes']):
                    $data .= "Boolean";
                    break;
                case in_array($columnType, $types['floatTypes']):
                    $data .= "Float";
                    break;
                case in_array($columnType, $types['jsonTypes']):
                    $data .= "Json";
                    break;
            }

            if ($columnData->getNotnull()) $data .= "!";

            $data .= "\n";
        }

        return $data;
    }

    /**
     * @param  string  $table
     * @param  string  $column
     * @return string
     */
    protected function getColumnType(string $table, string $column): string
    {
        return Schema::getColumnType($table, $column);
    }

    /**
     * Get the column listing for a given table.
     *
     * @param  string  $table
     * @return array
     */
    protected function getColumnListing(string $table): array
    {
        return Schema::getColumnListing($table);
    }

    protected function getTypes(): array
    {
        $intTypes = [
            'smallint',
            'mediumint',
            'int',
            'integer',
            'bigint',
            'year',
            'binary'
        ];

        $booleanTypes = [
            'boolean',
            'tinyint',
        ];

        $stringTypes = [
            'tinytext',
            'text',
            'mediumtext',
            'tinyblob',
            'blob',
            'mediumblob',
            'json',
            'string',
            'ascii_string',
            'array',
        ];

        $floatTypes = [
            'float',
            'decimal'
        ];

        $jsonTypes = [
            'json',
            'object'
        ];

        $timeTypes = [
            'date_immutable',
            'dateinterval',
            'datetime_immutable',
            'datetimetz_immutable',
            'time',
            'time_immutable',
            'timestamp'
        ];

        return compact(
            'intTypes',
            'booleanTypes',
            'stringTypes',
            'jsonTypes',
            'timeTypes',
            'floatTypes'
        );
    }
}