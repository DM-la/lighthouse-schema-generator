<?php

declare(strict_types=1);

namespace DmLa\LighthouseSchemaGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Safe\Exceptions\FilesystemException;
use DmLa\LighthouseSchemaGenerator\Helpers\FileUtils;
use DmLa\LighthouseSchemaGenerator\Parsers\ModelParser;
use DmLa\LighthouseSchemaGenerator\Helpers\ModelsUtils;

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

    /**
     * @param ModelsUtils $modelsUtils
     * @param FileUtils $fileUtils
     * @param ModelParser $modelParser
     */
    public function handle(ModelsUtils $modelsUtils, FileUtils $fileUtils, ModelParser $modelParser): void
    {
        $modelsPath = $this->getModelsPathFromOption();
        $path = $fileUtils->exists(app_path($modelsPath)) ? $modelsPath : false;

        if ($path !== false) {
            $files = $fileUtils->getAllFiles($path);
            $models = $modelsUtils->getModels($files, $path);
            $schemaFolder = $this->getGraphqlSchemaPath();

            /** @var bool $force */
            $force = $this->option('force');

            $models->each(function (Model $model) use ($schemaFolder, $force, $fileUtils, $modelParser) {
                $schemaFileName = $fileUtils->generateFileName(class_basename($model));
                $schemaPath = "{$schemaFolder}/{$schemaFileName}";

                if (! $force && $fileUtils->exists($schemaPath)) {
                    $question = "The {$schemaFileName} file exists. Do you want to rewrite file?";
                    if (! $this->confirm($question)) {
                        return true;
                    }
                }

                $content = $modelParser->parse($model);

                try {
                    $schema = $fileUtils->filePutContents($schemaPath, $content);
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
     * Path for models folder, relative to app path
     * @return string
     */
    protected function getModelsPathFromOption(): string
    {
        $modelsPathOption = $this->option('models-path');
        return $modelsPathOption && is_string($modelsPathOption) ? $modelsPathOption : '';
    }

    protected function getGraphqlSchemaPath(): string
    {
        $lighthouseSchemaRegister = config('lighthouse.schema.register');
        $schemaGraphql = $lighthouseSchemaRegister && is_string($lighthouseSchemaRegister) ?
                        $lighthouseSchemaRegister :
                        base_path('graphql/schema.graphql');

        return pathinfo(
            $schemaGraphql,
            PATHINFO_DIRNAME
        );
    }
}
