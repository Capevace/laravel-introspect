<?php

namespace Mateffy\Introspect\Commands;

use Illuminate\Console\Command;
use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Support\ControllableConsoleOutput;
use Mateffy\Introspect\Support\PaginatableConsoleOutput;

class IntrospectViewsCommand extends Command
{
    use ControllableConsoleOutput;
    use PaginatableConsoleOutput;

	protected $signature = 'introspect:views {--name=} {--name-not=} {--used-by=} {--not-used-by=} {--uses=} {--doesnt-use=} {--format=text} {--count} {--limit=} {--offset=}';

	protected $description = 'Query the views in your application';

	public function handle(): void
	{
        $name = $this->option('name');
        $nameNot = $this->option('name-not');
        $usedBy = $this->option('used-by');
        $notUsedBy = $this->option('not-used-by');
        $uses = $this->option('uses');
        $doesntUse = $this->option('doesnt-use');

        $query = Introspect::views();

        if ($name) {
            $query->whereNameEquals($name);
        }

        if ($nameNot) {
            $query->whereNameDoesntEqual($nameNot);
        }

        if ($usedBy) {
            $query->whereUsedBy($usedBy);
        }

        if ($notUsedBy) {
            $query->whereNotUsedBy($notUsedBy);
        }

        if ($uses) {
            $query->whereUses($uses);
        }

        if ($doesntUse) {
            $query->whereDoesntUse($doesntUse);
        }

        $this->outputResults($this->paginate($query)->get(), fn (string $view) => [
            'Name' => $view,
        ]);
	}
}
