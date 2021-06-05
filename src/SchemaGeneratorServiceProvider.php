<?php

declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator;

use DM\LighthouseSchemaGenerator\Commands\MakeGraphqlSchemaCommand;
use Illuminate\Support\ServiceProvider;

class SchemaGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
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
}