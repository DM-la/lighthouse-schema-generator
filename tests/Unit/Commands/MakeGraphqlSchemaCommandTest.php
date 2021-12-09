<?php

declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Tests\Unit\Commands;

use Artisan;
use DM\LighthouseSchemaGenerator\Helpers\FileUtils;
use DM\LighthouseSchemaGenerator\Helpers\ModelsUtils;
use DM\LighthouseSchemaGenerator\Tests\TestCase;
use Mockery\MockInterface;

class MakeGraphqlSchemaCommandTest extends TestCase
{
    public function testCommandWithWrongModelPath(): void
    {
        $this->artisan('make:graphql-schema', ['--models-path' => 'test/test/test'])
            ->expectsOutput('Directory does not exist!')
            ->assertExitCode(0);
    }

//        $this->partialMock(FileUtils::class, function (MockInterface $mock) {
//            $mock->shouldReceive('exists')->once()->andReturnTrue();
//            $mock->shouldReceive('getAllFiles')->once()->andReturn([]);
//        });
//
//        $this->partialMock(ModelsUtils::class, function (MockInterface $mock) {
//            $mock->shouldReceive('getModels')->once()->andReturn(collect([]));
//        });
}
