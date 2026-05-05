<?php

namespace Mateffy\Introspect\Commands;

use Illuminate\Console\Command;
use Mateffy\Introspect\Baseline\Baseline;
use Mateffy\Introspect\Baseline\BaselineCollector;
use Mateffy\Introspect\Baseline\DiffEngine;

class IntrospectDiffCommand extends Command
{
    protected $signature = 'introspect:diff
                            {--path= : Path to the baseline JSON file (default: storage/introspect-baseline.json)}
                            {--compact : Minified JSON output}';

    protected $description = 'Compare current state against a baseline and output the diff';

    public function handle(): int
    {
        $path = $this->option('path') ?? storage_path('introspect-baseline.json');
        $compact = $this->option('compact');

        if (! file_exists($path)) {
            $this->error("Baseline file not found: {$path}");
            $this->info('Run introspect:baseline first to create a baseline snapshot.');

            return self::FAILURE;
        }

        if ($this->output->isVerbose()) {
            $this->info('Loading baseline...');
        }
        $oldBaseline = Baseline::fromJson(file_get_contents($path));

        if ($this->output->isVerbose()) {
            $this->info('Collecting current state...');
        }
        $collector = new BaselineCollector;
        $newBaseline = $collector->collect();

        if ($this->output->isVerbose()) {
            $this->info('Computing diff...');
        }
        $diffEngine = new DiffEngine;
        $diff = $diffEngine->diff($oldBaseline, $newBaseline);

        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if (! $compact) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $this->line($diff->toJson($flags));

        return $diff->hasChanges() ? self::SUCCESS : self::SUCCESS;
    }
}
