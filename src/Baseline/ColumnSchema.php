<?php

namespace Mateffy\Introspect\Baseline;

readonly class ColumnSchema
{
    public function __construct(
        public string $type,
        public bool $nullable,
        public mixed $default,
        public bool $autoincrement,
        public bool $unsigned,
    ) {}

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'nullable' => $this->nullable,
            'default' => $this->default,
            'autoincrement' => $this->autoincrement,
            'unsigned' => $this->unsigned,
        ];
    }
}
