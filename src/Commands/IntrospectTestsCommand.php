<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Commands;

use Illuminate\Support\Collection;
use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Query\Contracts\TestQueryInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to introspect and export Pest tests in spec-compatible format
 */
class IntrospectTestsCommand extends IntrospectCommand
{
    protected $signature = 'introspect:tests
                            {--include= : Glob pattern for test files to include (e.g., "tests/Feature/*")}
                            {--exclude= : Glob pattern for test files to exclude (e.g., "tests/Unit/*")}
                            {--description= : Filter by description containing text}
                            {--status= : Filter by status (active, todo)}
                            {--test-directory=tests : The directory containing tests}
                            {--app-directory=app : The application directory containing colocated tests}
                            {--pattern=*.php : Pattern for test files}
                            {--with-lines : Include line numbers (requires nikic/php-parser)}
                            {--format=json : Output format (json, jsonl, table, raw)}
                            {--compact : Minified JSON output}
                            {--limit= : Maximum results}
                            {--offset= : Skip N results}
                            {--count : Return count only}
                            {--fields= : Comma-separated fields (e.g., "id,description,caseStatus")}
                            {--output-file= : Write output to file instead of stdout}
                            {--query= : Query DSL expression}
                            {--query-file= : Path to file containing query DSL}';

    protected $description = 'Export all Pest tests in spec-compatible JSON format';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            // Build the query
            $query = Introspect::tests();

            // Apply file/directory options
            $this->applyPathOptions($query);

            // Apply filter options
            $this->applySimpleFilters($query);

            // Apply common options (pagination, DSL query)
            $this->configureQuery($query);

            // Execute query
            $results = $query->get();

            // Transform to arrays for output
            $transformed = $this->transformResults($results);

            // Output to file or stdout
            if ($file = $this->option('output-file')) {
                $this->outputToFile($transformed, $file);
            } else {
                $this->outputResults($transformed, ['id', 'description', 'caseStatus', 'location.file']);
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Error extracting test data: {$e->getMessage()}");
            if ($this->option('verbose')) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    /**
     * Apply path-related options to the query
     */
    protected function applyPathOptions(TestQueryInterface $query): void
    {
        $testDir = $this->option('test-directory');
        if (is_array($testDir)) {
            $testDir = $testDir[0] ?? 'tests';
        }
        if ($testDir) {
            $query->inTestDirectory($testDir);
        }

        $appDir = $this->option('app-directory');
        if (is_array($appDir)) {
            $appDir = $appDir[0] ?? 'app';
        }
        if ($appDir) {
            $query->inAppDirectory($appDir);
        }

        $pattern = $this->option('pattern');
        if (is_array($pattern)) {
            $pattern = $pattern[0] ?? '*.php';
        }
        if ($pattern) {
            $query->withPattern($pattern);
        }

        if ($this->option('with-lines')) {
            $query->withLineNumbers(true);
        }
    }

    /**
     * Apply simple filter options
     */
    protected function applySimpleFilters(TestQueryInterface $query): void
    {
        // Include pattern (file glob)
        if ($this->option('include')) {
            $query->whereFileMatches($this->option('include'));
        }

        // Exclude pattern (file glob)
        if ($this->option('exclude')) {
            $query->whereFileDoesntMatch($this->option('exclude'));
        }

        // Description filter
        if ($this->option('description')) {
            $query->whereDescriptionContains($this->option('description'));
        }

        // Status filter
        if ($this->option('status')) {
            $query->whereStatusEquals($this->option('status'));
        }
    }

    /**
     * Apply DSL query
     */
    protected function applyDslQuery($query, string $dsl): void
    {
        $ast = $this->queryParser->parseQuery($dsl);
        $this->queryBuilder->applyToTests($query, $ast);
    }

    /**
     * Transform Test DTOs to arrays for output
     *
     * @param Collection<int, Test> $tests
     * @return Collection<int, array>
     */
    protected function transformResults(Collection $tests): Collection
    {
        return $tests->map(fn (Test $test) => $test->toArray());
    }

    /**
     * Output results to a file
     *
     * @param Collection<int, array> $results
     */
    protected function outputToFile(Collection $results, string $file): void
    {
        $format = $this->option('format') ?? 'json';
        $compact = $this->option('compact');

        $content = match ($format) {
            'json' => $this->buildSpecExport($results, $compact),
            'jsonl' => $results->map(fn ($item) => json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))->implode("\n"),
            default => $this->buildSpecExport($results, $compact),
        };

        file_put_contents($file, $content);

        $this->info("Test metadata written to: {$file}");
        $this->info("Exported {$results->count()} tests");
    }

    /**
     * Override outputJson to use spec-compatible format by default
     *
     * @param Collection<int, array> $results
     */
    protected function outputJson(Collection $results, bool $compact = false): void
    {
        $json = $this->buildSpecExport($results, $compact);
        $this->line($json);
    }

    /**
     * Build spec-compatible test export JSON
     *
     * @param Collection<int, array> $results
     */
    protected function buildSpecExport(Collection $results, bool $compact = false): string
    {
        $export = [
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'source' => 'laravel-pest',
                'count' => $results->count(),
            ],
            'tests' => $results->values()->toArray(),
        ];

        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if (! $compact) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($export, $flags);
    }

    /**
     * Override outputTable to handle nested location object
     *
     * @param Collection<int, array> $results
     */
    protected function outputTable(Collection $results, array $defaultFields): void
    {
        if ($results->isEmpty()) {
            $this->warn('No tests found.');

            return;
        }

        // Flatten location fields for table display
        $flattened = $results->map(function ($item) {
            $flat = [
                'id' => $item['id'] ?? '',
                'description' => $item['description'] ?? '',
                'caseStatus' => $item['caseStatus'] ?? '',
                'file' => $item['location']['file'] ?? '',
            ];

            if (isset($item['location']['line'])) {
                $flat['line'] = $item['location']['line'];
            }

            return $flat;
        });

        parent::outputTable($flattened, ['id', 'description', 'caseStatus', 'file']);
    }
}
