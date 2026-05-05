<?php

namespace Mateffy\Introspect\Baseline;

readonly class TableSchema
{
    /**
     * @param  array<string, ColumnSchema>  $columns
     * @param  array<string, IndexSchema>  $indexes
     */
    public function __construct(
        public array $columns,
        public array $indexes,
    ) {}

    public function toArray(): array
    {
        $columns = [];
        foreach ($this->columns as $name => $column) {
            $columns[$name] = $column->toArray();
        }

        $indexes = [];
        foreach ($this->indexes as $name => $index) {
            $indexes[$name] = $index->toArray();
        }

        return [
            'columns' => $columns,
            'indexes' => $indexes,
        ];
    }
}
