<?php

namespace Mateffy\Introspect\Commands;

use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;

class IntrospectViewsCommand extends IntrospectCommand
{
    protected $signature = 'introspect:views
                            {--name= : Filter by view name (supports wildcards)}
                            {--name-not= : Exclude views matching pattern}
                            {--name-starts= : Name starts with text}
                            {--name-ends= : Name ends with text}
                            {--name-contains= : Name contains text}
                            {--uses= : Views that use a specific view}
                            {--doesnt-use= : Views that don\'t use a specific view}
                            {--used-by= : Views used by a specific view}
                            {--not-used-by= : Views not used by a specific view}
                            {--format=json : Output format (json, jsonl, table, csv, raw)}
                            {--compact : Minified JSON output}
                            {--limit= : Maximum results}
                            {--offset= : Skip N results}
                            {--count : Return count only}
                            {--fields= : Comma-separated fields}
                            {--query= : Query DSL expression}
                            {--query-file= : Path to query file}';

    protected $description = 'Query views in your application';

    public function handle(): int
    {
        try {
            $query = Introspect::views();

            // Apply simple filter options
            $this->applySimpleFilters($query);

            // Apply common options (pagination, DSL query)
            $this->configureQuery($query);

            // Execute and output
            $results = $query->get();

            $this->outputResults($results, ['name']);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    protected function applySimpleFilters(ViewQueryInterface $query): void
    {
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

        if ($this->option('uses')) {
            $query->whereUses($this->option('uses'));
        }

        if ($this->option('doesnt-use')) {
            $query->whereDoesntUse($this->option('doesnt-use'));
        }

        if ($this->option('used-by')) {
            $query->whereUsedBy($this->option('used-by'));
        }

        if ($this->option('not-used-by')) {
            $query->whereNotUsedBy($this->option('not-used-by'));
        }
    }

    protected function applyDslQuery($query, string $dsl): void
    {
        $ast = $this->queryParser->parseQuery($dsl);
        $this->queryBuilder->applyToViews($query, $ast);
    }
}
