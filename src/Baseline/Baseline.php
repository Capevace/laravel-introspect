<?php

namespace Mateffy\Introspect\Baseline;

use Illuminate\Support\Collection;
use Mateffy\Introspect\DTO\Route;

readonly class Baseline
{
    /**
     * @param  string  $timestamp  ISO 8601 timestamp
     * @param  Collection<int, Route>  $routes
     * @param  array<string, mixed>  $config  Flattened config key-value pairs
     */
    public function __construct(
        public string $timestamp,
        public Collection $routes,
        public MigrationSchema $migrations,
        public array $config,
    ) {}

    public function toArray(): array
    {
        return [
            'timestamp' => $this->timestamp,
            'routes' => $this->routes->map(fn (Route $route) => $route->toArray())->values()->all(),
            'migrations' => $this->migrations->toArray(),
            'config' => $this->config,
        ];
    }

    public function toJson(int $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT): string
    {
        return json_encode($this->toArray(), $flags);
    }

    public static function fromArray(array $data): self
    {
        $routes = collect($data['routes'] ?? [])->map(fn (array $route) => new Route(
            uri: $route['uri'],
            name: $route['name'],
            methods: $route['methods'],
            controller: $route['controller'],
            action: $route['action'],
            middlewares: $route['middlewares'],
        ));

        $tables = [];
        foreach ($data['migrations']['tables'] ?? [] as $tableName => $tableData) {
            $columns = [];
            foreach ($tableData['columns'] ?? [] as $columnName => $columnData) {
                $columns[$columnName] = new ColumnSchema(
                    type: $columnData['type'],
                    nullable: $columnData['nullable'] ?? true,
                    default: $columnData['default'] ?? null,
                    autoincrement: $columnData['autoincrement'] ?? false,
                    unsigned: $columnData['unsigned'] ?? false,
                );
            }

            $indexes = [];
            foreach ($tableData['indexes'] ?? [] as $indexName => $indexData) {
                $indexes[$indexName] = new IndexSchema(
                    columns: $indexData['columns'] ?? [],
                    unique: $indexData['unique'] ?? false,
                    primary: $indexData['primary'] ?? false,
                );
            }

            $tables[$tableName] = new TableSchema(
                columns: $columns,
                indexes: $indexes,
            );
        }

        return new self(
            timestamp: $data['timestamp'] ?? now()->toISOString(),
            routes: $routes,
            migrations: new MigrationSchema(tables: $tables),
            config: $data['config'] ?? [],
        );
    }

    public static function fromJson(string $json): self
    {
        return self::fromArray(json_decode($json, true));
    }
}
