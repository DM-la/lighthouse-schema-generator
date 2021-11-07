<?php
declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Commands;

use Illuminate\Console\Command;
use Safe\Exceptions\FilesystemException;
use DM\LighthouseSchemaGenerator\Helpers\Utils;
use DM\LighthouseSchemaGenerator\Helpers\FileUtils;
use DM\LighthouseSchemaGenerator\Helpers\ModelParser;

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

    /** @var Utils */
    private $utils;

    /** @var FileUtils */
    private $fileUtils;

    /** @var ModelParser */
    private $modelParser;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Utils $utils, FileUtils $fileUtils, ModelParser $modelParser)
    {
        parent::__construct();

        $this->utils = $utils;
        $this->fileUtils = $fileUtils;
        $this->modelParser = $modelParser;
    }

    public function handle()
    {
        $path = $this->option('models-path') ?: '';
        $path = $this->fileUtils->exists(app_path($path)) ? $path : false;

        if ($path !== false) {
            $files = $this->fileUtils->getAllFiles($path);
            $models = $this->utils->getModels($files, $path);

            $models->each(function ($model) {
                $content = $this->modelParser->generateSchema($model);
                $graphqlSchemaFolder = pathinfo(config('lighthouse.schema.register'), PATHINFO_DIRNAME);
                $schemaFileName = $this->fileUtils->generateFileName(class_basename($model));
                $schemaPath = $graphqlSchemaFolder . '/' . $schemaFileName;

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
}