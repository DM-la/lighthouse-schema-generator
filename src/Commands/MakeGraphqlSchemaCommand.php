<?php

declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Commands;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Finder\SplFileInfo;
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Collection;

class MakeGraphqlSchemaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:graphql-schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lighthouse schema generator';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->getModels()->each(function ($model) {
            /** @var Model $model */
            $model = new $model;
            $data = '';
            $reflector = (new ReflectionClass(get_class($model)));

            $graphqlSchemaFolder = pathinfo(config('lighthouse.schema.register'), PATHINFO_DIRNAME);
            $schemaPath = $graphqlSchemaFolder . '/' . strtolower($reflector->getShortName() . '.graphql');
            $data .= "type {$reflector->getShortName()} {\n";
            $this->parseColumns($model, $data);

            foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                $methodName = $reflectionMethod->getName();
                if (
                    $reflectionMethod->class !== get_class($model)
                    || ! empty($reflectionMethod->getParameters())
                    || $methodName == __FUNCTION__
                ) {
                    continue;
                }

                $relation = $reflectionMethod->invoke($model);

                if ($relation instanceof Relation) {
                    $relationName = (new ReflectionClass($relation))->getShortName();
                    $relationClass = (new ReflectionClass($relation->getRelated()))->getShortName();
                    if ($relationName == 'HasOne' || $relationName == 'BelongsTo' || $relationName == 'MorphTo') {
                        $data .= "    {$methodName}: {$relationClass} @{$relationName}\n";
                    }
                }
            }
            $data .= '}';

            $schema = \Safe\file_put_contents($schemaPath, $data);
        });
    }

    /**
     * @return Collection
     */
    private function getModels(): Collection
    {
        $models = collect(File::allFiles(app_path()))->map(function (SplFileInfo $file) {
            $path = $file->getRelativePathName();
            $class = sprintf(
                '\%s%s',
                app()->getNamespace(),
                strtr(substr($path, 0, strrpos($path, '.')), '/', '\\')
            );

            return $class;
        })->filter(function (string $class) {
            $valid = false;

            if (class_exists($class)) {
                $reflection = new ReflectionClass($class);
                $valid = $reflection->isSubclassOf(Model::class) && (! $reflection->isAbstract());
            }

            return $valid;
        });

        return $models;
    }

    /**
     * @param $model
     * @param string $data
     */
    private function parseColumns($model, string &$data): void
    {
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);
        $types = $this->getTypes();

        foreach ($columns as $column) {
            /** @var \Doctrine\DBAL\Schema\Column $columnData */
            $columnData = Schema::getConnection()->getDoctrineColumn($table, $column);
            $data .= "    {$column}: ";

            $columnType = Schema::getColumnType($table, $column);
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
    }

    private function getTypes(): array
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
            'object'
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
        );
    }
}