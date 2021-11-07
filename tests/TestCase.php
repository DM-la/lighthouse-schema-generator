<?php

declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use DM\LighthouseSchemaGenerator\SchemaGeneratorServiceProvider;

class TestCase extends BaseTestCase
{
    /**
     * Get package providers.
     *
     * @param  Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            SchemaGeneratorServiceProvider::class,
        ];
    }

    /**
     * @param Application $app
     */
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);
        $app['config']['lighthouse.schema.register'] = $app['path.config'] . '/graphql/schema.graphql';
    }
}