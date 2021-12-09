<?php

namespace DM\LighthouseSchemaGenerator\Parsers;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\ConnectionInterface;
use DM\LighthouseSchemaGenerator\Helpers\SchemaUtils;

class ColumnsParser
{
    /**
     * @param Model $model
     * @return string
     */
    public function parse(Model $model): string
    {
        $data = '';

        $table = $model->getTable();
        $columns = SchemaUtils::getColumnListing($table);
        $connection = $model->getConnection();
        $types = $this->getTypes();

        foreach ($columns as $column) {
            $data .= $this->processColumn($connection, $table, $column, $types);
        }

        return $data;
    }

    /**
     * @param ConnectionInterface $connection
     * @param string $table
     * @param string $column
     * @param array $types
     * @return string
     */
    protected function processColumn(ConnectionInterface $connection, string $table, string $column, array $types): string
    {
        $data = '';
        /** @var Connection $connection */
        $columnData = $connection->getDoctrineColumn($table, $column);
        $data .= "    {$column}: ";

        $columnType = SchemaUtils::getColumnType($table, $column);

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

        return $data;
    }

    /**
     * @return array
     */
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