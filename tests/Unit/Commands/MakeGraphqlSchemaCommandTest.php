<?php

declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Tests\Unit\Commands;

use DM\LighthouseSchemaGenerator\Tests\TestCase;

class MakeGraphqlSchemaCommandTest extends TestCase
{
    public function testCommandWithWrongModelPath(): void
    {
        $this->artisan('make:graphql-schema', ['--models-path' => 'test/test/test'])
            ->expectsOutput('Directory does not exist!')
            ->assertExitCode(0);
    }
}