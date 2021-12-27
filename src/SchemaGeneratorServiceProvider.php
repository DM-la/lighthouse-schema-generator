<?php

declare(strict_types=1);

namespace DmLa\LighthouseSchemaGenerator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use DmLa\LighthouseSchemaGenerator\Commands\MakeGraphqlSchemaCommand;

class SchemaGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerDoctrineTypeMapping();
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeGraphqlSchemaCommand::class,
            ]);
        }
    }

    private function registerDoctrineTypeMapping(): void
    {
        //TODO: Hotfix ignore phpstan error
        if (! defined('__PHPSTAN_RUNNING__')) {
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        }
    }
}
