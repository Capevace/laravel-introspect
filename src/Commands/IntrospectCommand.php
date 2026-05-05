<?php

namespace Mateffy\Introspect\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Query\Contracts\PaginationInterface;
use Mateffy\Introspect\Query\Contracts\QueryPerformerInterface;
use Mateffy\Introspect\Query\DSL\QueryBuilder;
use Mateffy\Introspect\Query\DSL\QueryParser;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

/**
 * Base command for all introspect commands
 * Provides shared functionality for DSL parsing, output formatting, pagination
 */
abstract class IntrospectCommand extends Command
{
    protected QueryParser $queryParser;

    protected QueryBuilder $queryBuilder;

    public function __construct()
    {
        parent::__construct();
        $this->queryParser = new QueryParser;
        $this->queryBuilder = new QueryBuilder;
    }

    /**
     * Configure common options for all query commands
     */
    protected function configureCommonOptions(): void
    {
        $this->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format (json, jsonl, table, csv, raw)', 'json');
        $this->addOption('compact', null, InputOption::VALUE_NONE, 'Output compact/minified JSON');
        $this->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of results');
        $this->addOption('offset', null, InputOption::VALUE_REQUIRED, 'Skip N results');
        $this->addOption('count', null, InputOption::VALUE_NONE, 'Return count only');
        $this->addOption('fields', null, InputOption::VALUE_REQUIRED, 'Comma-separated list of fields to include');
        $this->addOption('query', null, InputOption::VALUE_REQUIRED, 'Query DSL expression');
        $this->addOption('query-file', null, InputOption::VALUE_REQUIRED, 'Path to file containing query DSL');
    }

    /**
     * Apply pagination and query DSL to a query
     */
    protected function configureQuery(QueryPerformerInterface&PaginationInterface $query): void
    {
        // Apply pagination
        if ($this->option('offset') !== null) {
            $query->offset((int) $this->option('offset'));
        }

        if ($this->option('limit') !== null) {
            $query->limit((int) $this->option('limit'));
        }

        // Apply DSL query if provided
        $dslQuery = $this->getDslQuery();
        if ($dslQuery !== null) {
            $this->applyDslQuery($query, $dslQuery);
        }
    }

    /**
     * Get the DSL query from options
     */
    protected function getDslQuery(): ?string
    {
        if ($this->option('query-file')) {
            $path = $this->option('query-file');
            if (! file_exists($path)) {
                throw new \InvalidArgumentException("Query file not found: {$path}");
            }

            return file_get_contents($path);
        }

        return $this->option('query');
    }

    /**
     * Apply DSL query to the query object (to be implemented by subclasses)
     */
    abstract protected function applyDslQuery($query, string $dsl): void;

    /**
     * Output results in the requested format
     */
    protected function outputResults(Collection $results, array $defaultFields = []): void
    {
        $format = $this->option('format') ?? 'json';
        $compact = $this->option('compact');
        $countOnly = $this->option('count');
        $fields = $this->option('fields');

        // Handle count-only mode
        if ($countOnly) {
            $this->line($results->count());

            return;
        }

        // Apply field selection if specified
        if ($fields !== null) {
            $fieldList = array_map('trim', explode(',', $fields));
            $results = $this->selectFields($results, $fieldList);
        }

        // Output based on format
        switch ($format) {
            case 'json':
                $this->outputJson($results, $compact);
                break;
            case 'jsonl':
                $this->outputJsonl($results);
                break;
            case 'table':
                $this->outputTable($results, $defaultFields);
                break;
            case 'csv':
                $this->outputCsv($results);
                break;
            case 'raw':
                $this->outputRaw($results);
                break;
            default:
                throw new \InvalidArgumentException("Unknown format: {$format}");
        }
    }

    /**
     * Output as JSON
     */
    protected function outputJson(Collection $results, bool $compact = false): void
    {
        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if (! $compact) {
            $flags |= JSON_PRETTY_PRINT;
        }

        // Output raw array for backward compatibility
        $this->line(json_encode($results->values(), $flags));
    }

    /**
     * Output as JSON Lines (one JSON object per line)
     */
    protected function outputJsonl(Collection $results): void
    {
        foreach ($results as $item) {
            $this->line(json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Output as table
     */
    protected function outputTable(Collection $results, array $defaultFields): void
    {
        if ($results->isEmpty()) {
            $this->warn('No results found.');

            return;
        }

        // Auto-detect headers from first result if no defaults provided
        $first = $results->first();
        if (empty($defaultFields) && is_array($first)) {
            $defaultFields = array_keys($first);
        } elseif (empty($defaultFields) && is_object($first)) {
            $defaultFields = array_keys(get_object_vars($first));
        }

        $table = new Table($this->output);
        $table->setHeaders($defaultFields);

        $rows = $results->map(function ($item) use ($defaultFields) {
            $row = [];
            foreach ($defaultFields as $field) {
                $value = is_array($item) ? ($item[$field] ?? null) : ($item->$field ?? null);
                $row[] = $this->formatTableValue($value);
            }

            return $row;
        })->toArray();

        $table->setRows($rows);
        $table->render();
    }

    /**
     * Format a value for table display
     */
    protected function formatTableValue($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        if (is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Output as CSV
     */
    protected function outputCsv(Collection $results): void
    {
        if ($results->isEmpty()) {
            return;
        }

        $first = $results->first();
        $headers = is_array($first) ? array_keys($first) : array_keys(get_object_vars($first));

        // Output headers
        $this->line(implode(',', array_map([$this, 'escapeCsv'], $headers)));

        // Output rows
        foreach ($results as $item) {
            $row = [];
            foreach ($headers as $field) {
                $value = is_array($item) ? ($item[$field] ?? null) : ($item->$field ?? null);
                $row[] = $this->formatCsvValue($value);
            }
            $this->line(implode(',', $row));
        }
    }

    /**
     * Format a value for CSV
     */
    protected function formatCsvValue($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            $value = implode('|', $value);
        }

        return $this->escapeCsv((string) $value);
    }

    /**
     * Escape a CSV value
     */
    protected function escapeCsv(string $value): string
    {
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }

    /**
     * Output raw (simple list)
     */
    protected function outputRaw(Collection $results): void
    {
        foreach ($results as $item) {
            if (is_string($item)) {
                $this->line($item);
            } else {
                $this->line(json_encode($item, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }
        }
    }

    /**
     * Select specific fields from results
     */
    protected function selectFields(Collection $results, array $fields): Collection
    {
        return $results->map(function ($item) use ($fields) {
            $selected = [];
            foreach ($fields as $field) {
                // Support dot notation for nested fields
                $selected[$field] = $this->getNestedValue($item, $field);
            }

            return $selected;
        });
    }

    /**
     * Get a nested value using dot notation
     */
    protected function getNestedValue($item, string $field)
    {
        $parts = explode('.', $field);
        $value = $item;

        foreach ($parts as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } elseif (is_object($value) && isset($value->$part)) {
                $value = $value->$part;
            } elseif (is_object($value) && method_exists($value, $part)) {
                $value = $value->$part();
            } else {
                return null;
            }
        }

        return $value;
    }
}
