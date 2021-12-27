<?php

declare(strict_types=1);

namespace DmLa\LighthouseSchemaGenerator\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use DmLa\LighthouseSchemaGenerator\SchemaGeneratorServiceProvider;

class TestCase extends BaseTestCase
{
    /**
    * {@inheritDoc}
    */
    protected function getPackageProviders($app): array
    {
        return [
            SchemaGeneratorServiceProvider::class,
        ];
    }

    /**
    * {@inheritDoc}
    */
    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);
        $config =  $app['config'];
        $config['lighthouse.schema.register'] = $app['path.config'] . '/graphql/schema.graphql';
        $config['database.default'] = 'mysql';
        $config['database.connections.mysql'] = $this->mysqlOptions();
    }

    /**
     * @return array<string, mixed>
     */
    protected function mysqlOptions(): array
    {
        return [
            'driver' => 'mysql',
            'database' => env('DB_DATABASE', 'lighthouse_schema_generator_db'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'connection' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', 'mysql'),
            'port' => env('DB_PORT', '3306'),
        ];
    }
}
