<?php

namespace Tests\Stubs;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use MediaWiki\Bot\Command;

class CommandExample extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command-example';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command Example';

    public function getArguments()
    {
        return [];
    }

    public function getOptions()
    {
        return [];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
    }
}
