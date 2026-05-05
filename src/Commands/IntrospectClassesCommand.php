<?php

namespace Mateffy\Introspect\Commands;

use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;

class IntrospectClassesCommand extends IntrospectCommand
{
    protected $signature = 'introspect:classes
                            {--name= : Filter by class name (supports wildcards)}
                            {--name-not= : Exclude classes matching pattern}
                            {--name-starts= : Name starts with text}
                            {--name-ends= : Name ends with text}
                            {--name-contains= : Name contains text}
                            {--extends= : Filter by parent class}
                            {--doesnt-extend= : Exclude parent class}
                            {--implements= : Filter by interface (can be comma-separated)}
                            {--doesnt-implement= : Exclude interface}
                            {--uses= : Filter by trait (can be comma-separated)}
                            {--doesnt-use= : Exclude trait}
                            {--format=json : Output format (json, jsonl, table, csv, raw)}
                            {--compact : Minified JSON output}
                            {--limit= : Maximum results}
                            {--offset= : Skip N results}
                            {--count : Return count only}
                            {--fields= : Comma-separated fields}
                            {--query= : Query DSL expression}
                            {--query-file= : Path to query file}';

    protected $description = 'Query classes in your application';

    public function handle(): int
    {
        try {
            $query = Introspect::classes();

            // Apply simple filter options
            $this->applySimpleFilters($query);

            // Apply common options (pagination, DSL query)
            $this->configureQuery($query);

            // Execute and output
            $results = $query->get();

            $this->outputResults($results, ['class']);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    protected function applySimpleFilters(ClassQueryInterface $query): void
    {
        // Name filters
        if ($this->option('name')) {
            $query->whereNameEquals($this->option('name'));
        }

        if ($this->option('name-not')) {
            $query->whereNameDoesntEqual($this->option('name-not'));
        }

        if ($this->option('name-starts')) {
            $query->whereNameStartsWith($this->option('name-starts'));
        }

        if ($this->option('name-ends')) {
            $query->whereNameEndsWith($this->option('name-ends'));
        }

        if ($this->option('name-contains')) {
            $query->whereNameContains($this->option('name-contains'));
        }

        // Extension filters
        if ($this->option('extends')) {
            $query->whereExtends($this->option('extends'));
        }

        if ($this->option('doesnt-extend')) {
            $query->whereDoesntExtend($this->option('doesnt-extend'));
        }

        // Interface filters
        if ($this->option('implements')) {
            $interfaces = array_map('trim', explode(',', $this->option('implements')));
            $query->whereImplements($interfaces);
        }

        if ($this->option('doesnt-implement')) {
            $interfaces = array_map('trim', explode(',', $this->option('doesnt-implement')));
            $query->whereDoesntImplement($interfaces);
        }

        // Trait filters
        if ($this->option('uses')) {
            $traits = array_map('trim', explode(',', $this->option('uses')));
            $query->whereUses($traits);
        }

        if ($this->option('doesnt-use')) {
            $traits = array_map('trim', explode(',', $this->option('doesnt-use')));
            $query->whereDoesntUse($traits);
        }
    }

    protected function applyDslQuery($query, string $dsl): void
    {
        $ast = $this->queryParser->parseQuery($dsl);
        $this->queryBuilder->applyToClasses($query, $ast);
    }
}
