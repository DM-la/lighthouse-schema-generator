<?php

declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Commands;

use Cloudinary\Transformation\Expression\StringRelationalOperatorBuilderTrait;
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
            $relations = [];
            $data = '';
            $reflector = (new ReflectionClass(get_class($model)));

            $graphqlSchemaFolder = pathinfo(config('lighthouse.schema.register'), PATHINFO_DIRNAME);
            $schemaPath = $graphqlSchemaFolder . '/' . strtolower($reflector->getShortName() . '.graphql');
            $data .= "type {$reflector->getShortName()} {\n";

//            $columns = Schema::getColumnListing($model->getTable());
//            /** @var \Illuminate\Database\Schema\Builder $schemaBuilder */
//            $schemaBuilder = \DB::getSchemaBuilder();
//            dd(gettype($description[0]));
            $description = \DB::select("describe {$model->getTable()}");
            foreach($description as $value) {
//                dd($schemaBuilder->getColumnType($model->getTable(), $value->Field));
                $data .= "    {$value->Field}: ";
                if ($value->Key == 'PRI' && $value->Extra == 'auto_increment') {
                    $data .= "ID!";
                } else {
                    if($value->Type == 'timestamp') $data .= "DateTime";
                    if($value->Type == 'varchar(255)') $data .= "String";
                    if($value->Null == 'NO') $data .= "!";
                }
                $data .= "\n";
            }

            foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                $returnType = $reflectionMethod->getReturnType();

                if ($returnType) {
                    if (in_array(class_basename($returnType->getName()), ['HasOne', 'HasMany', 'BelongsTo', 'BelongsToMany', 'MorphToMany', 'MorphTo'])) {
                        $relations[] = $reflectionMethod;
                    }
                }
            }

            $data .= '}';

            $schema = \Safe\file_put_contents($schemaPath, $data);
            dd($relations);
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
}