<?php

namespace Mateffy\Introspect\Support;

use Closure;
use Illuminate\Support\Collection;

use function Laravel\Prompts\table;
use function Laravel\Prompts\warning;

trait ControllableConsoleOutput
{
    public function outputResults(Collection $results, Closure $row, ?array $headers = null)
    {
        $format = $this->hasOption('format')
            ? $this->option('format', 'text')
            : false;

        $count = $this->hasOption('count') && $this->option('count', false);

        if ($count) {
            $this->line($results->count());

            return;
        }

        if ($format === 'json') {
            $this->line(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } elseif ($results->isNotEmpty()) {
            $formatted = $results->map($row);
            $headers = $headers ?? array_keys($formatted->first());

            table(headers: $headers, rows: $formatted);
        } else {
            warning('No results found.');
        }
    }
}
