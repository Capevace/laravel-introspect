<?php

namespace Mateffy\Introspect\Baseline;

readonly class RouteChange
{
    /**
     * @param  array<string, FieldChange>  $changes
     */
    public function __construct(
        public string $key,
        public string $uri,
        public ?string $name,
        public array $changes,
    ) {}

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'uri' => $this->uri,
            'name' => $this->name,
            'changes' => array_map(fn (FieldChange $c) => $c->toArray(), $this->changes),
        ];
    }
}
