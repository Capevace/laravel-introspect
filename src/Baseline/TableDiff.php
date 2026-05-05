<?php

namespace Mateffy\Introspect\Baseline;

readonly class TableDiff
{
    /**
     * @param  array<string, array>  $addedColumns
     * @param  array<string, array>  $removedColumns
     * @param  array<string, ColumnChange>  $changedColumns
     * @param  array<string, array>  $addedIndexes
     * @param  array<string, array>  $removedIndexes
     * @param  array<string, FieldChange>  $changedIndexes
     */
    public function __construct(
        public array $addedColumns,
        public array $removedColumns,
        public array $changedColumns,
        public array $addedIndexes,
        public array $removedIndexes,
        public array $changedIndexes,
    ) {}

    public function toArray(): array
    {
        $changedColumns = [];
        foreach ($this->changedColumns as $name => $change) {
            $changedColumns[$name] = $change->toArray();
        }

        $changedIndexes = [];
        foreach ($this->changedIndexes as $name => $change) {
            $changedIndexes[$name] = $change->toArray();
        }

        return [
            'added_columns' => $this->addedColumns,
            'removed_columns' => $this->removedColumns,
            'changed_columns' => $changedColumns,
            'added_indexes' => $this->addedIndexes,
            'removed_indexes' => $this->removedIndexes,
            'changed_indexes' => $changedIndexes,
        ];
    }
}
