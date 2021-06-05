<?php

declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Tests;

use DM\LighthouseSchemaGenerator\SchemaGeneratorServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            SchemaGeneratorServiceProvider::class,
        ];
    }
}