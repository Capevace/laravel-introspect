<?php

namespace Mateffy\Introspect\Commands;

use Illuminate\Console\Command;
use Mateffy\Introspect\Facades\Introspect;

class IntrospectModelCommand extends Command
{
    protected $signature = 'introspect:model
                            {class : The model class name}
                            {--schema : Output as JSON Schema}
                            {--format=json : Output format (json, table)}
                            {--compact : Minified JSON output}';

    protected $description = 'Get detailed information about a specific Eloquent model';

    public function handle(): int
    {
        try {
            $class = $this->argument('class');
            $schema = $this->option('schema');
            $format = $this->option('format') ?? 'json';
            $compact = $this->option('compact');

            // Get the model detail
            $model = Introspect::model($class);

            if ($schema) {
                // Output JSON Schema
                $output = $model->schema();
            } else {
                // Output full model info with only available properties
                $output = [
                    'class' => $model->classpath,
                    'name' => class_basename($model->classpath),
                    'description' => $model->description,
                    'properties' => $model->properties->map(fn ($prop) => [
                        'types' => $prop->types->toArray(),
                        'description' => $prop->description,
                        'default' => $prop->default,
                        'nullable' => $prop->types->contains('null'),
                        'readable' => $prop->readable,
                        'writable' => $prop->writable,
                    ])->toArray(),
                ];
            }

            // Output based on format
            if ($format === 'json') {
                $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
                if (! $compact) {
                    $flags |= JSON_PRETTY_PRINT;
                }
                $this->line(json_encode($output, $flags));
            } elseif ($format === 'table') {
                $this->outputAsTable($output);
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    protected function outputAsTable(array $data): void
    {
        // Handle both schema output (has 'title') and regular output (has 'class')
        $className = $data['class'] ?? ($data['title'] ?? 'Unknown');
        $this->info("Model: {$className}");
        $this->newLine();

        // Basic info
        $this->table(
            ['Property', 'Value'],
            [
                ['Name', $data['name'] ?? ($data['title'] ?? 'N/A')],
                ['Description', $data['description'] ?? 'N/A'],
            ]
        );

        $this->newLine();

        // Properties
        if (! empty($data['properties'])) {
            $this->info('Properties:');
            $rows = [];
            foreach ($data['properties'] as $name => $prop) {
                // Handle both schema format (has 'type') and regular format (has 'types')
                $types = [];
                if (isset($prop['types'])) {
                    $types = is_array($prop['types']) ? $prop['types'] : [$prop['types']];
                } elseif (isset($prop['type'])) {
                    $types = is_array($prop['type']) ? $prop['type'] : [$prop['type']];
                }
                $typeStr = implode('|', $types);
                $access = [];
                if ($prop['readable'] ?? false) {
                    $access[] = 'R';
                }
                if ($prop['writable'] ?? false) {
                    $access[] = 'W';
                }
                $rows[] = [$name, $typeStr, implode('', $access)];
            }
            $this->table(['Name', 'Types', 'Access'], $rows);
        }
    }
}
