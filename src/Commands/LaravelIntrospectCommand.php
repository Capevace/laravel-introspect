<?php

namespace Mateffy\Introspect\Commands;

use Illuminate\Console\Command;

class LaravelIntrospectCommand extends Command
{
    public $signature = 'laravel-introspect';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
