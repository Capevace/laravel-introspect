<?php

namespace Mateffy\Introspect\Commands;

use Illuminate\Console\Command;

class IntrospectSchemaCommand extends Command
{
    protected $signature = 'introspect:schema
                            {--resource= : Specific resource (views, routes, classes, models)}
                            {--format=json : Output format (json, table)}
                            {--compact : Minified JSON output}';

    protected $description = 'Discover available filters and query capabilities';

    public function handle(): int
    {
        try {
            $resource = $this->option('resource');
            $format = $this->option('format') ?? 'json';
            $compact = $this->option('compact');

            $schema = $this->getSchema($resource);

            // Output based on format
            if ($format === 'json') {
                $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
                if (! $compact) {
                    $flags |= JSON_PRETTY_PRINT;
                }
                $this->line(json_encode($schema, $flags));
            } elseif ($format === 'table') {
                $this->outputAsTable($schema, $resource);
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    protected function getSchema(?string $resource): array
    {
        $allResources = [
            'views' => $this->getViewsSchema(),
            'routes' => $this->getRoutesSchema(),
            'classes' => $this->getClassesSchema(),
            'models' => $this->getModelsSchema(),
        ];

        if ($resource !== null) {
            if (! isset($allResources[$resource])) {
                throw new \InvalidArgumentException("Unknown resource: {$resource}. Available: ".implode(', ', array_keys($allResources)));
            }

            return [
                'resource' => $resource,
                'capabilities' => $allResources[$resource],
            ];
        }

        return [
            'resources' => $allResources,
            'dsl_operators' => $this->getDslOperators(),
            'output_formats' => ['json', 'jsonl', 'table', 'csv', 'raw'],
        ];
    }

    protected function getViewsSchema(): array
    {
        return [
            'filters' => [
                ['name' => 'name', 'type' => 'string', 'operators' => ['=', '!=', '^=', '$=', '*='], 'aliases' => ['EQ', 'NEQ', 'STARTS_WITH', 'ENDS_WITH', 'CONTAINS'], 'supports_wildcard' => true, 'description' => 'View name pattern'],
                ['name' => 'uses', 'type' => 'string', 'operators' => ['=', '?='], 'aliases' => ['EQ', 'HAS'], 'supports_wildcard' => true, 'description' => 'Views that use this component'],
                ['name' => 'usedBy', 'type' => 'string', 'operators' => ['=', '?=', '*='], 'aliases' => ['EQ', 'HAS', 'CONTAINS'], 'supports_wildcard' => true, 'description' => 'Views used by this view'],
            ],
            'output_fields' => ['name'],
            'example_queries' => [
                'name = components.*.button',
                'name STARTS_WITH admin::',
                'name ^= filament:: AND usedBy *= dashboard',
            ],
        ];
    }

    protected function getRoutesSchema(): array
    {
        return [
            'filters' => [
                ['name' => 'name', 'type' => 'string', 'operators' => ['=', '!=', '^=', '$=', '*='], 'aliases' => ['EQ', 'NEQ', 'STARTS_WITH', 'ENDS_WITH', 'CONTAINS'], 'supports_wildcard' => true, 'description' => 'Route name'],
                ['name' => 'path', 'type' => 'string', 'operators' => ['=', '!=', '^=', '$=', '*='], 'aliases' => ['EQ', 'NEQ', 'STARTS_WITH', 'ENDS_WITH', 'CONTAINS'], 'supports_wildcard' => true, 'description' => 'URL path pattern'],
                ['name' => 'controller', 'type' => 'string', 'operators' => ['=', '!='], 'aliases' => ['EQ', 'NEQ'], 'supports_method' => true, 'description' => 'Controller class'],
                ['name' => 'middleware', 'type' => 'array', 'operators' => ['?=', '?&', '?|', '!='], 'aliases' => ['HAS', 'HAS_ALL', 'HAS_ANY', 'NOT_HAS'], 'description' => 'Middleware applied'],
                ['name' => 'method', 'type' => 'array', 'operators' => ['?=', '?&', '?|'], 'aliases' => ['HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'HTTP methods'],
                ['name' => 'hasParam', 'type' => 'string', 'operators' => ['=', '?=', '?&', '?|'], 'aliases' => ['EQ', 'HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'Route parameters'],
            ],
            'output_fields' => ['name', 'path', 'methods', 'controller', 'middleware', 'parameters', 'domain'],
            'example_queries' => [
                'path ^= api/ && middleware ?= auth',
                'path STARTS_WITH admin AND middleware HAS_ALL auth,verified',
                '(method = POST || method = PUT) && name *= users',
            ],
        ];
    }

    protected function getClassesSchema(): array
    {
        return [
            'filters' => [
                ['name' => 'name', 'type' => 'string', 'operators' => ['=', '!=', '^=', '$=', '*='], 'aliases' => ['EQ', 'NEQ', 'STARTS_WITH', 'ENDS_WITH', 'CONTAINS'], 'supports_wildcard' => true, 'description' => 'Class name'],
                ['name' => 'extends', 'type' => 'string', 'operators' => ['=', '!='], 'aliases' => ['EQ', 'NEQ'], 'description' => 'Parent class'],
                ['name' => 'implements', 'type' => 'array', 'operators' => ['=', '?=', '?&', '?|'], 'aliases' => ['EQ', 'HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'Implemented interfaces'],
                ['name' => 'uses', 'type' => 'array', 'operators' => ['=', '?=', '?&', '?|'], 'aliases' => ['EQ', 'HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'Used traits'],
            ],
            'output_fields' => ['class'],
            'example_queries' => [
                'name $= Controller && extends = BaseController',
                'name ENDS_WITH Repository && uses HAS_ANY SoftDeletes,HasFactory',
                'implements HAS_ALL JsonSerializable,Arrayable',
            ],
        ];
    }

    protected function getModelsSchema(): array
    {
        return [
            'filters' => [
                // Inherited from classes
                ['name' => 'name', 'type' => 'string', 'operators' => ['=', '!=', '^=', '$=', '*='], 'aliases' => ['EQ', 'NEQ', 'STARTS_WITH', 'ENDS_WITH', 'CONTAINS'], 'supports_wildcard' => true, 'description' => 'Model class name'],
                ['name' => 'extends', 'type' => 'string', 'operators' => ['=', '!='], 'aliases' => ['EQ', 'NEQ'], 'description' => 'Parent class'],
                ['name' => 'implements', 'type' => 'array', 'operators' => ['=', '?=', '?&', '?|'], 'aliases' => ['EQ', 'HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'Implemented interfaces'],
                ['name' => 'uses', 'type' => 'array', 'operators' => ['=', '?=', '?&', '?|'], 'aliases' => ['EQ', 'HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'Used traits'],
                // Model-specific
                ['name' => 'hasProperty', 'type' => 'string', 'operators' => ['=', '?=', '?&', '?|'], 'aliases' => ['EQ', 'HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'Database properties'],
                ['name' => 'hasFillable', 'type' => 'string', 'operators' => ['=', '?=', '?&', '?|'], 'aliases' => ['EQ', 'HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'Fillable fields'],
                ['name' => 'hasHidden', 'type' => 'string', 'operators' => ['=', '?=', '?&', '?|'], 'aliases' => ['EQ', 'HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'Hidden fields'],
                ['name' => 'hasAppended', 'type' => 'string', 'operators' => ['=', '?=', '?&', '?|'], 'aliases' => ['EQ', 'HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'Appended accessors'],
                ['name' => 'hasReadable', 'type' => 'string', 'operators' => ['=', '?=', '?&', '?|'], 'aliases' => ['EQ', 'HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'Readable properties'],
                ['name' => 'hasWritable', 'type' => 'string', 'operators' => ['=', '?=', '?&', '?|'], 'aliases' => ['EQ', 'HAS', 'HAS_ALL', 'HAS_ANY'], 'description' => 'Writable properties'],
                ['name' => 'hasRelationship', 'type' => 'string', 'operators' => ['=', '!='], 'aliases' => ['EQ', 'NEQ'], 'description' => 'Relationship methods'],
            ],
            'output_fields' => ['class', 'name', 'properties', 'fillable', 'hidden', 'appends', 'casts', 'relationships'],
            'example_queries' => [
                'hasFillable HAS_ALL email,password && hasRelationship = user',
                'name *= User && hasHidden ?= password',
                'hasProperty HAS_ANY created_at,updated_at AND extends = BaseModel',
            ],
        ];
    }

    protected function getDslOperators(): array
    {
        return [
            ['symbol' => '=', 'text' => 'EQ', 'description' => 'Equals (supports wildcards)'],
            ['symbol' => '!=', 'text' => 'NEQ', 'description' => 'Not equals'],
            ['symbol' => '^=', 'text' => 'STARTS_WITH', 'description' => 'Starts with'],
            ['symbol' => '$=', 'text' => 'ENDS_WITH', 'description' => 'Ends with'],
            ['symbol' => '*=', 'text' => 'CONTAINS', 'description' => 'Contains'],
            ['symbol' => '!^=', 'text' => 'NOT_STARTS_WITH', 'description' => 'Does not start with'],
            ['symbol' => '!$=', 'text' => 'NOT_ENDS_WITH', 'description' => 'Does not end with'],
            ['symbol' => '!*=', 'text' => 'NOT_CONTAINS', 'description' => 'Does not contain'],
            ['symbol' => '?=', 'text' => 'HAS', 'description' => 'Has value (for arrays)'],
            ['symbol' => '?&', 'text' => 'HAS_ALL', 'description' => 'Has all values'],
            ['symbol' => '?|', 'text' => 'HAS_ANY', 'description' => 'Has any value'],
            ['symbol' => '&&', 'text' => 'AND', 'description' => 'Logical AND'],
            ['symbol' => '||', 'text' => 'OR', 'description' => 'Logical OR'],
            ['symbol' => '!', 'text' => 'NOT', 'description' => 'Logical NOT'],
        ];
    }

    protected function outputAsTable(array $schema, ?string $resource): void
    {
        if ($resource === null) {
            $this->info('Available Resources:');
            $this->newLine();
            foreach ($schema['resources'] as $name => $config) {
                $this->line("  • {$name}");
                $this->line('    Filters: '.implode(', ', array_column($config['filters'], 'name')));
            }
            $this->newLine();
            $this->info('DSL Operators:');
            $rows = [];
            foreach ($schema['dsl_operators'] as $op) {
                $rows[] = [$op['symbol'], $op['text'], $op['description']];
            }
            $this->table(['Symbol', 'Text', 'Description'], $rows);
        } else {
            $this->info("Resource: {$resource}");
            $this->newLine();

            $this->info('Filters:');
            $rows = [];
            foreach ($schema['capabilities']['filters'] as $filter) {
                $rows[] = [
                    $filter['name'],
                    $filter['type'],
                    implode(', ', $filter['operators']),
                    $filter['description'],
                ];
            }
            $this->table(['Name', 'Type', 'Operators', 'Description'], $rows);

            if (! empty($schema['capabilities']['example_queries'])) {
                $this->newLine();
                $this->info('Example Queries:');
                foreach ($schema['capabilities']['example_queries'] as $example) {
                    $this->line("  {$example}");
                }
            }
        }
    }
}
