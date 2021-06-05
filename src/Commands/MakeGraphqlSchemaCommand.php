<?php

declare(strict_types=1);

namespace DM\LighthouseSchemaGenerator\Commands;

use Illuminate\Console\Command;

class MakeGraphqlSchemaCommand extends Command
{
    protected $name = 'lighthouse:ide-helper';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:graphql-schema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lighthouse schema generator';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Fresh command!!!');
    }
}