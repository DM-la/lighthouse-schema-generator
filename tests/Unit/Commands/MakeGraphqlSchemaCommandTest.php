<?php

declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Tests\Unit\Commands;

use DM\LighthouseSchemaGenerator\Tests\TestCase;

class MakeGraphqlSchemaCommandTest extends TestCase
{
    public function testCommand()
    {
        $this->artisan('make:graphql-schema')
            ->expectsOutput('Fresh command!!!');
    }
}