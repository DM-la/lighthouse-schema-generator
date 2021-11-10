<?php
declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Commands;

use Illuminate\Console\Command;
use Safe\Exceptions\FilesystemException;
use DM\LighthouseSchemaGenerator\Helpers\FileUtils;
use DM\LighthouseSchemaGenerator\Parsers\ModelParser;
use DM\LighthouseSchemaGenerator\Helpers\ModelsUtils;

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

    /** @var ModelsUtils */
    private $modelsUtils;

    /** @var FileUtils */
    private $fileUtils;

    /** @var ModelParser */
    private $modelParser;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ModelsUtils $modelsUtils, FileUtils $fileUtils, ModelParser $modelParser)
    {
        parent::__construct();

        $this->modelsUtils = $modelsUtils;
        $this->fileUtils = $fileUtils;
        $this->modelParser = $modelParser;
    }

    public function handle(): void
    {
        $modelsPath = $this->getModelsPathFromOption();
        $path = $this->fileUtils->exists(app_path($modelsPath)) ? $modelsPath : false;

        if ($path !== false) {
            $files = $this->fileUtils->getAllFiles($path);
            $models = $this->modelsUtils->getModels($files, $path);
            $schemaFolder = $this->getGraphqlSchemaPath();

            /** @var bool $force */
            $force = $this->option('force');

            $models->each(function ($model) use ($schemaFolder, $force) {
                $schemaFileName = $this->fileUtils->generateFileName(class_basename($model));
                $schemaPath = "{$schemaFolder}/{$schemaFileName}";

                if (! $force && $this->fileUtils->exists($schemaPath)) {
                    $question = "The {$schemaFileName} file exists. Do you want to rewrite file?";
                    if (! $this->confirm($question)) return true;
                }

                $content = $this->modelParser->parse($model);

                try {
                    $schema = $this->fileUtils->filePutContents($schemaPath, $content);
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