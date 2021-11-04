<?php
declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Commands;

use Exception;
use ReflectionMethod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Safe\Exceptions\FilesystemException;
use Symfony\Component\Finder\SplFileInfo;
use DM\LighthouseSchemaGenerator\Helpers\File;
use DM\LighthouseSchemaGenerator\Helpers\Reflection;
use Illuminate\Database\Eloquent\Relations\Relation;

use Illuminate\Support\Collection;

class MakeGraphqlSchemaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:graphql-schema
                            {--models-path= : Path for models folder, relative to app path}
                            {--f|force : Rewrite schemes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lighthouse schema generator';

    /** @var File */
    protected $file;

    /** @var Reflection */
    protected $reflection;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(File $file, Reflection $reflection)
    {
        parent::__construct();

        $this->file = $file;
        $this->reflection = $reflection;
    }

    public function handle()
    {
        $path = $this->option('models-path') ?: '';
        $path = $this->file->exists(app_path($path)) ? $path : false;

        if ($path !== false) {
            $models = $this->getModels($path);

            $models->each(function ($model) {
                $content = $this->generateSchema($model);
                $graphqlSchemaFolder = pathinfo(config('lighthouse.schema.register'), PATHINFO_DIRNAME);
                $schemaFileName = $this->file->generateFileName(class_basename($model));
                $schemaPath = $graphqlSchemaFolder . '/' . $schemaFileName;

//                if ($this->fileOrDirectoryExists($schemaPath)) {
//                }

                try {
                    $schema = $this->file->filePutContents($schemaPath, $content);
                } catch (FilesystemException $exception) {
                    $this->error($exception->getMessage());
                    return true;
                }

                $schema ? $this->info("{$schemaFileName} file was generated") : $this->error("Generating `{$schemaFileName}` file was failed");
            });
        } else {
            $this->error('Directory does not exist!');
        }
    }

    /**
     * @param Model|object $model
     * @return string $data
     */
    private function generateSchema(object $model): string
    {
        $data = '';
        $reflector = $this->reflection->reflectionObject($model);

        $data .= "type {$reflector->getShortName()} {\n";
        $this->parseColumns($model, $data);

        $publicMethods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($publicMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();
            $returnType = $reflectionMethod->getReturnType();
            if ($returnType && ! $returnType->isBuiltin()) {
                try {
                    $relation = $this->reflection->reflectionClass($returnType->getName());
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
                } catch (Exception $exception) {
                    $this->error($exception->getMessage());
                    continue;
                }
            }
        }

        $data .= '}';

        return $data;
    }

    /**
     * @param string $path models path
     * @return Collection
     */
    private function getModels(string $path = ''): Collection
    {
        $models = collect($this->file->getAllFiles($path))->map(function (SplFileInfo $file) use ($path) {
            $path = $path ?  $path . '/' . $file->getRelativePathName(): $file->getRelativePathName();

            return $this->getNamespace($path);
        })->filter(function (string $class) {
            $valid = false;

            if (class_exists($class)) {
                $reflection = $this->reflection->reflectionClass($class);
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
    }

    /**
     * @param string $path
     * @return string
     */
    private function getNamespace(string $path): string
    {
        return sprintf(
            '\%s%s',
            app()->getNamespace(),
            strtr(substr($path, 0, strrpos($path, '.')), '/', '\\')
        );
    }

    /**
     * @param  string  $table
     * @param  string  $column
     * @return string
     */
    private function getColumnType(string $table, string $column): string
    {
        return Schema::getColumnType($table, $column);
    }

    /**
     * Get the column listing for a given table.
     *
     * @param  string  $table
     * @return array
     */
    public function getColumnListing(string $table): array
    {
        return Schema::getColumnListing($table);
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