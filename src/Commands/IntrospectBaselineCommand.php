<?php

namespace Mateffy\Introspect\Commands;

use Illuminate\Console\Command;
use Mateffy\Introspect\Baseline\BaselineCollector;

class IntrospectBaselineCommand extends Command
{
    protected $signature = 'introspect:baseline
                            {--path= : Path to write the baseline JSON file (default: storage/introspect-baseline.json)}
                            {--compact : Minified JSON output}';

    protected $description = 'Create a baseline snapshot of routes, migrations, and config';

    public function handle(): int
    {
        $path = $this->option('path') ?? storage_path('introspect-baseline.json');
        $compact = $this->option('compact');

        $this->info('Collecting baseline data...');

        $collector = new BaselineCollector;
        $baseline = $collector->collect();

        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if (! $compact) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $json = $baseline->toJson($flags);
        file_put_contents($path, $json);

        $routeCount = $baseline->routes->count();
        $tableCount = count($baseline->migrations->tables);
        $configCount = count($baseline->config);

        $this->info("Baseline written to: {$path}");
        $this->line("  Routes: {$routeCount}");
        $this->line("  Tables: {$tableCount}");
        $this->line("  Config keys: {$configCount}");

        return self::SUCCESS;
    }
}
