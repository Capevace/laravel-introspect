<?php

namespace Mateffy\Introspect\Baseline;

readonly class IndexSchema
{
    /**
     * @param  array<string>  $columns
     */
    public function __construct(
        public array $columns,
        public bool $unique,
        public bool $primary,
    ) {}

    public function toArray(): array
    {
        return [
            'columns' => $this->columns,
            'unique' => $this->unique,
            'primary' => $this->primary,
        ];
    }
}
