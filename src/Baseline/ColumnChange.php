<?php

namespace Mateffy\Introspect\Baseline;

readonly class ColumnChange
{
    /**
     * @param  array<string, FieldChange>  $changes
     */
    public function __construct(
        public array $changes,
    ) {}

    public function toArray(): array
    {
        return array_map(fn (FieldChange $c) => $c->toArray(), $this->changes);
    }
}
