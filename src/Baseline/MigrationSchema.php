<?php

namespace Mateffy\Introspect\Baseline;

readonly class MigrationSchema
{
    /**
     * @param  array<string, TableSchema>  $tables
     */
    public function __construct(
        public array $tables,
    ) {}

    public function toArray(): array
    {
        $tables = [];
        foreach ($this->tables as $name => $table) {
            $tables[$name] = $table->toArray();
        }

        return ['tables' => $tables];
    }
}
