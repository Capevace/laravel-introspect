<?php

namespace Mateffy\Introspect\Baseline;

readonly class ConfigDiff
{
    /**
     * @param  array<string, mixed>  $added
     * @param  array<string, mixed>  $removed
     * @param  array<string, FieldChange>  $changed
     */
    public function __construct(
        public array $added,
        public array $removed,
        public array $changed,
    ) {}

    public function toArray(): array
    {
        return [
            'added' => $this->added,
            'removed' => $this->removed,
            'changed' => array_map(fn (FieldChange $c) => $c->toArray(), $this->changed),
        ];
    }
}
