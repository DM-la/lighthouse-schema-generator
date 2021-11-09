<?php

declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use DM\LighthouseSchemaGenerator\SchemaGeneratorServiceProvider;

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
        $app['config']['lighthouse.schema.register'] = $app['path.config'] . '/graphql/schema.graphql';
    }
}