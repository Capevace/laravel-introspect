<?php

namespace Mateffy\Introspect\Baseline;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Mateffy\Introspect\DTO\Route;

class BaselineCollector
{
    public function collect(): Baseline
    {
        return new Baseline(
            timestamp: now()->toISOString(),
            routes: $this->collectRoutes(),
            migrations: $this->collectMigrations(),
            config: $this->collectConfig(),
        );
    }

    /**
     * @return Collection<int, Route>
     */
    protected function collectRoutes(): Collection
    {
        /** @var Router $router */
        $router = app('router');

        return collect($router->getRoutes())
            ->map(fn (\Illuminate\Routing\Route $route) => Route::fromRoute($route))
            ->values();
    }

    protected function collectMigrations(): MigrationSchema
    {
        $tables = collect(Schema::getTableListing());

        $tableSchemas = $tables->mapWithKeys(function (string $table) {
            return [$table => $this->collectTableSchema($table)];
        });

        return new MigrationSchema(
            tables: $tableSchemas->toArray(),
        );
    }

    protected function collectTableSchema(string $table): TableSchema
    {
        $columnNames = Schema::getColumnListing($table);

        $columns = collect($columnNames)->mapWithKeys(function (string $column) use ($table) {
            return [$column => $this->collectColumnSchema($table, $column)];
        })->toArray();

        $indexes = $this->collectIndexes($table);

        return new TableSchema(
            columns: $columns,
            indexes: $indexes,
        );
    }

    protected function collectColumnSchema(string $table, string $column): ColumnSchema
    {
        $type = 'unknown';
        $nullable = true;
        $default = null;
        $autoincrement = false;
        $unsigned = false;

        try {
            $type = Schema::getColumnType($table, $column);
        } catch (\Throwable) {
            // Some drivers may not support this
        }

        try {
            $connection = Schema::getConnection();

            if (method_exists($connection, 'getDoctrineColumn')) {
                $doctrineColumn = $connection->getDoctrineColumn($table, $column);

                $type = $doctrineColumn->getType()?->getName() ?? $type;
                $nullable = ! $doctrineColumn->getNotnull();
                $default = $doctrineColumn->getDefault();
                $autoincrement = $doctrineColumn->getAutoincrement();
                $unsigned = $doctrineColumn->getUnsigned();
            }
        } catch (\Throwable) {
            // Doctrine DBAL may not be available or column may not support it
        }

        return new ColumnSchema(
            type: $type,
            nullable: $nullable,
            default: $default,
            autoincrement: $autoincrement,
            unsigned: $unsigned,
        );
    }

    /**
     * @return array<string, IndexSchema>
     */
    protected function collectIndexes(string $table): array
    {
        $indexes = [];

        try {
            $connection = Schema::getConnection();

            if (method_exists($connection, 'getDoctrineSchemaManager')) {
                $doctrineManager = $connection->getDoctrineSchemaManager();
                $doctrineTable = $doctrineManager->listTableDetails($table);

                foreach ($doctrineTable->getIndexes() as $index) {
                    $indexes[$index->getName()] = new IndexSchema(
                        columns: $index->getColumns(),
                        unique: $index->isUnique(),
                        primary: $index->isPrimary(),
                    );
                }
            } else {
                $indexes = $this->collectIndexesRaw($table);
            }
        } catch (\Throwable) {
            // Doctrine DBAL may not be available; try raw approach for SQLite/MySQL
            $indexes = $this->collectIndexesRaw($table);
        }

        return $indexes;
    }

    /**
     * @return array<string, IndexSchema>
     */
    protected function collectIndexesRaw(string $table): array
    {
        $indexes = [];

        try {
            $connection = Schema::getConnection();
            $driver = $connection->getDriverName();

            if ($driver === 'sqlite') {
                $rows = $connection->select("PRAGMA index_list('{$table}')");
                foreach ($rows as $row) {
                    $indexName = $row->name;
                    $isUnique = (bool) ($row->unique ?? false);
                    $isPrimary = str_starts_with($indexName, 'sqlite_master_') || $indexName === 'primary';

                    $indexColumns = $connection->select("PRAGMA index_info('{$indexName}')");
                    $columns = array_map(fn ($col) => $col->name, $indexColumns);

                    $indexes[$indexName] = new IndexSchema(
                        columns: $columns,
                        unique: $isUnique,
                        primary: $isPrimary,
                    );
                }
            } elseif ($driver === 'mysql' || $driver === 'mariadb') {
                $rows = $connection->select("SHOW INDEX FROM `{$table}`");
                $grouped = [];
                foreach ($rows as $row) {
                    $indexName = $row->Key_name;
                    if (! isset($grouped[$indexName])) {
                        $grouped[$indexName] = [
                            'columns' => [],
                            'unique' => ! ($row->Non_unique ?? 1),
                            'primary' => ($row->Key_name ?? '') === 'PRIMARY',
                        ];
                    }
                    $grouped[$indexName]['columns'][] = $row->Column_name;
                }

                foreach ($grouped as $name => $data) {
                    $indexes[$name] = new IndexSchema(
                        columns: $data['columns'],
                        unique: $data['unique'],
                        primary: $data['primary'],
                    );
                }
            } elseif ($driver === 'pgsql') {
                $rows = $connection->select("
                    SELECT
                        i.relname as index_name,
                        ix.indisunique as is_unique,
                        ix.indisprimary as is_primary,
                        array_agg(a.attname ORDER BY array_position(ix.indkey, a.attnum)) as columns
                    FROM pg_index ix
                    JOIN pg_class t ON t.oid = ix.indrelid
                    JOIN pg_class i ON i.oid = ix.indexrelid
                    JOIN pg_attribute a ON a.attrelid = t.oid AND a.attnum = ANY(ix.indkey)
                    WHERE t.relname = '{$table}'
                    GROUP BY i.relname, ix.indisunique, ix.indisprimary
                ");
                foreach ($rows as $row) {
                    $indexes[$row->index_name] = new IndexSchema(
                        columns: explode(',', trim($row->columns, '{}')),
                        unique: (bool) $row->is_unique,
                        primary: (bool) $row->is_primary,
                    );
                }
            }
        } catch (\Throwable) {
            // If everything fails, return empty indexes
        }

        return $indexes;
    }

    protected function collectConfig(): array
    {
        $allConfig = Config::all();

        return $this->flattenConfig($allConfig);
    }

    protected function flattenConfig(array $config, string $prefix = ''): array
    {
        $flat = [];

        foreach ($config as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value) && ! empty($value) && ! array_is_list($value)) {
                $flat = array_merge($flat, $this->flattenConfig($value, $fullKey));
            } else {
                $flat[$fullKey] = $this->serializeConfigValue($value);
            }
        }

        return $flat;
    }

    protected function serializeConfigValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($v) => $this->serializeConfigValue($v), $value);
        }

        if (is_object($value) && ! ($value instanceof \UnitEnum)) {
            return get_class($value);
        }

        if ($value instanceof \UnitEnum) {
            return $value->value ?? $value->name;
        }

        if (is_resource($value)) {
            return '(resource)';
        }

        if (is_callable($value)) {
            return '(callable)';
        }

        return $value;
    }
}
