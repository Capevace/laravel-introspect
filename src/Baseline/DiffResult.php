<?php

namespace Mateffy\Introspect\Baseline;

readonly class DiffResult
{
    public function __construct(
        public RouteDiff $routes,
        public MigrationDiff $migrations,
        public ConfigDiff $config,
    ) {}

    public function toArray(): array
    {
        return [
            'routes' => $this->routes->toArray(),
            'migrations' => $this->migrations->toArray(),
            'config' => $this->config->toArray(),
        ];
    }

    public function toJson(int $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT): string
    {
        return json_encode($this->toArray(), $flags);
    }

    public function hasChanges(): bool
    {
        return ! empty($this->routes->added)
            || ! empty($this->routes->removed)
            || ! empty($this->routes->changed)
            || ! empty($this->migrations->addedTables)
            || ! empty($this->migrations->removedTables)
            || ! empty($this->migrations->changedTables)
            || ! empty($this->config->added)
            || ! empty($this->config->removed)
            || ! empty($this->config->changed);
    }
}
