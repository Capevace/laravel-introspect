<?php

namespace Mateffy\Introspect\Baseline;

readonly class FieldChange
{
    public function __construct(
        public mixed $from,
        public mixed $to,
    ) {}

    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}
