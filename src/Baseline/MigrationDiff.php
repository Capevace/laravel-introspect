<?php

namespace Mateffy\Introspect\Baseline;

readonly class MigrationDiff
{
    /**
     * @param  array<string, array>  $addedTables
     * @param  array<string, array>  $removedTables
     * @param  array<string, TableDiff>  $changedTables
     */
    public function __construct(
        public array $addedTables,
        public array $removedTables,
        public array $changedTables,
    ) {}

    public function toArray(): array
    {
        $changedTables = [];
        foreach ($this->changedTables as $name => $diff) {
            $changedTables[$name] = $diff->toArray();
        }

        return [
            'added_tables' => $this->addedTables,
            'removed_tables' => $this->removedTables,
            'changed_tables' => $changedTables,
        ];
    }
}
