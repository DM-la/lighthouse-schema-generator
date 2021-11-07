<?php
declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator;

use Illuminate\Support\ServiceProvider;
use DM\LighthouseSchemaGenerator\Commands\MakeGraphqlSchemaCommand;

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
        \DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }
}