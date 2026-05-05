<?php

namespace Mateffy\Introspect\Commands;

use Mateffy\Introspect\DTO\Model;
use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;

class IntrospectModelsCommand extends IntrospectCommand
{
    protected $signature = 'introspect:models
                            {--name= : Filter by model name (supports wildcards)}
                            {--name-starts= : Name starts with text}
                            {--name-ends= : Name ends with text}
                            {--name-contains= : Name contains text}
                            {--extends= : Filter by parent class}
                            {--implements= : Filter by interface}
                            {--uses= : Filter by trait}
                            {--has-property= : Filter by property existence}
                            {--has-properties= : Filter by all properties (comma-separated)}
                            {--has-fillable= : Filter by fillable field}
                            {--has-fillable-all= : Filter by all fillable fields (comma-separated)}
                            {--has-fillable-any= : Filter by any fillable field (comma-separated)}
                            {--has-hidden= : Filter by hidden field}
                            {--has-hidden-all= : Filter by all hidden fields (comma-separated)}
                            {--has-hidden-any= : Filter by any hidden field (comma-separated)}
                            {--has-appended= : Filter by appended field}
                            {--has-appended-all= : Filter by all appended fields (comma-separated)}
                            {--has-appended-any= : Filter by any appended field (comma-separated)}
                            {--has-readable= : Filter by readable field}
                            {--has-readable-all= : Filter by all readable fields (comma-separated)}
                            {--has-readable-any= : Filter by any readable field (comma-separated)}
                            {--has-writable= : Filter by writable field}
                            {--has-writable-all= : Filter by all writable fields (comma-separated)}
                            {--has-writable-any= : Filter by any writable field (comma-separated)}
                            {--has-relationship= : Filter by relationship existence}
                            {--format=json : Output format (json, jsonl, table, csv, raw)}
                            {--compact : Minified JSON output}
                            {--limit= : Maximum results}
                            {--offset= : Skip N results}
                            {--count : Return count only}
                            {--fields= : Comma-separated fields}
                            {--query= : Query DSL expression}
                            {--query-file= : Path to query file}';

    protected $description = 'Query Eloquent models in your application';

    public function handle(): int
    {
        try {
            $query = Introspect::models();

            // Apply simple filter options
            $this->applySimpleFilters($query);

            // Apply common options (pagination, DSL query)
            $this->configureQuery($query);

            // Execute and output
            $results = $query->get();

            // Transform model DTOs to arrays
            $transformed = $results->map(fn ($model) => $this->transformModel($model));

            $this->outputResults($transformed, ['class', 'name']);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    protected function applySimpleFilters(ModelQueryInterface $query): void
    {
        // Name filters (inherited from ClassQuery)
        if ($this->option('name')) {
            $query->whereNameEquals($this->option('name'));
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

        // Class filters (inherited from ClassQuery)
        if ($this->option('extends')) {
            $query->whereExtends($this->option('extends'));
        }

        if ($this->option('implements')) {
            $interfaces = array_map('trim', explode(',', $this->option('implements')));
            $query->whereImplements($interfaces);
        }

        if ($this->option('uses')) {
            $traits = array_map('trim', explode(',', $this->option('uses')));
            $query->whereUses($traits);
        }

        // Property filters
        if ($this->option('has-property')) {
            $query->whereHasProperty($this->option('has-property'));
        }

        if ($this->option('has-properties')) {
            $props = array_map('trim', explode(',', $this->option('has-properties')));
            $query->whereHasProperties($props, all: true);
        }

        // Fillable filters
        if ($this->option('has-fillable')) {
            $query->whereHasFillable($this->option('has-fillable'));
        }

        if ($this->option('has-fillable-all')) {
            $fields = array_map('trim', explode(',', $this->option('has-fillable-all')));
            $query->whereHasFillableProperties($fields, all: true);
        }

        if ($this->option('has-fillable-any')) {
            $fields = array_map('trim', explode(',', $this->option('has-fillable-any')));
            $query->whereHasFillableProperties($fields, all: false);
        }

        // Hidden filters
        if ($this->option('has-hidden')) {
            $query->whereHasHidden($this->option('has-hidden'));
        }

        if ($this->option('has-hidden-all')) {
            $fields = array_map('trim', explode(',', $this->option('has-hidden-all')));
            $query->whereHasHiddenProperties($fields, all: true);
        }

        if ($this->option('has-hidden-any')) {
            $fields = array_map('trim', explode(',', $this->option('has-hidden-any')));
            $query->whereHasHiddenProperties($fields, all: false);
        }

        // Appended filters
        if ($this->option('has-appended')) {
            $query->whereHasAppended($this->option('has-appended'));
        }

        if ($this->option('has-appended-all')) {
            $fields = array_map('trim', explode(',', $this->option('has-appended-all')));
            $query->whereHasAppendedProperties($fields, all: true);
        }

        if ($this->option('has-appended-any')) {
            $fields = array_map('trim', explode(',', $this->option('has-appended-any')));
            $query->whereHasAppendedProperties($fields, all: false);
        }

        // Readable filters
        if ($this->option('has-readable')) {
            $query->whereHasReadable($this->option('has-readable'));
        }

        if ($this->option('has-readable-all')) {
            $fields = array_map('trim', explode(',', $this->option('has-readable-all')));
            $query->whereHasReadableProperties($fields, all: true);
        }

        if ($this->option('has-readable-any')) {
            $fields = array_map('trim', explode(',', $this->option('has-readable-any')));
            $query->whereHasReadableProperties($fields, all: false);
        }

        // Writable filters
        if ($this->option('has-writable')) {
            $query->whereHasWritable($this->option('has-writable'));
        }

        if ($this->option('has-writable-all')) {
            $fields = array_map('trim', explode(',', $this->option('has-writable-all')));
            $query->whereHasWritableProperties($fields, all: true);
        }

        if ($this->option('has-writable-any')) {
            $fields = array_map('trim', explode(',', $this->option('has-writable-any')));
            $query->whereHasWritableProperties($fields, all: false);
        }

        // Relationship filters
        if ($this->option('has-relationship')) {
            $query->whereHasRelationship($this->option('has-relationship'));
        }
    }

    protected function applyDslQuery($query, string $dsl): void
    {
        $ast = $this->queryParser->parseQuery($dsl);
        $this->queryBuilder->applyToModels($query, $ast);
    }

    protected function transformModel(Model $model): array
    {
        return [
            'class' => $model->class,
            'name' => $model->name,
            'properties' => $model->properties,
            'fillable' => $model->fillable,
            'hidden' => $model->hidden,
            'appends' => $model->appends,
            'casts' => $model->casts,
            'relationships' => $model->relationships,
        ];
    }
}
