<?php
declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Commands;

use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;
use ReflectionException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Safe\Exceptions\FilesystemException;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Database\Eloquent\Relations\Relation;

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
            $content = $this->generateSchema($model);
            $graphqlSchemaFolder = pathinfo(config('lighthouse.schema.register'), PATHINFO_DIRNAME);
            $schemaFileName = $this->generateFileName(class_basename($model));
            $schemaPath = $graphqlSchemaFolder . '/' . $schemaFileName;

            try {
                $schema = $this->filePutContents($schemaPath, $content);
            } catch (FilesystemException $exception) {
                $this->error($exception->getMessage());
                return true;
            }

            $schema ? $this->info("{$schemaFileName} file was generated") : $this->error("Generating `{$schemaFileName}` file was failed");
        });
    }

    /**
     * @param Model $model
     * @return string $data
     */
    private function generateSchema($model): string
    {
        $data = '';
        $reflector = $this->reflectionObject($model);

        $data .= "type {$reflector->getShortName()} {\n";
        $this->parseColumns($model, $data);

        $publicMethods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType && ! $returnType->isBuiltin()) {
                try {
                    $relation = $this->reflectionClass($returnType->getName());
                    if (
                        $reflectionMethod->hasReturnType()
                        && $reflectionMethod->getNumberOfParameters() == 0
                        && $relation->isSubclassOf(Relation::class)
                    ) {
                        $relatedClass = $reflectionMethod->invoke($model)->getRelated();
                        $relatedClassName = class_basename($relatedClass);

                        $relationName = $relation->getShortName();
                        if ($relationName == 'BelongsTo') {
                            $data .= "    {$methodName}: $relatedClassName @{$relationName}\n";
                        }
                    }
                } catch (\Exception $exception) {
                    $this->error($exception->getMessage());
                    continue;
                }
            }
        }

        $data .= '}';

        return $data;
    }

    /**
     * @return Collection
     */
    private function getModels(): Collection
    {
        $models = collect($this->getAllFiles(app_path()))->map(function (SplFileInfo $file) {
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
                $reflection = $this->reflectionClass($class);
                $valid = $reflection->isSubclassOf(Model::class) && (! $reflection->isAbstract());
            }

            return $valid;
        })->map(function (string $modelNamespace) {
            return (new $modelNamespace);
        });

        return $models->values();
    }

    /**
     * @param Model $model
     * @param string $data
     */
    private function parseColumns($model, string &$data): void
    {
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);
        $connection = $model->getConnection();
        $types = $this->getTypes();

        foreach ($columns as $column) {
            $columnData = $connection->getDoctrineColumn($table, $column);
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

    /**
     * @param string|object $objectOrClass
     * @return ReflectionClass
     * @throws ReflectionException
     */
    private function reflectionClass($objectOrClass): ReflectionClass
    {
        return (new ReflectionClass($objectOrClass));
    }

    /**
     * @param object $object
     * @return ReflectionObject
     */
    private function reflectionObject(object $object): ReflectionObject
    {
        return (new ReflectionObject($object));
    }

    /**
     * @param string $name
     * @return string
     */
    private function generateFileName(string $name): string
    {
        return strtolower( "{$name}.graphql");
    }

    /**
     * @param string $path
     * @return SplFileInfo[]
     */
    private function getAllFiles(string $path)
    {
        return File::allFiles($path);
    }

    /**
     * @param string $path
     * @param string $content
     * @return int
     * @throws FilesystemException
     */
    private function filePutContents(string $path, string $content): int
    {
        return \Safe\file_put_contents($path, $content);
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