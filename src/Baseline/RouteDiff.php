<?php

namespace Mateffy\Introspect\Baseline;

readonly class RouteDiff
{
    /**
     * @param  array<array-key, array>  $added
     * @param  array<array-key, array>  $removed
     * @param  array<array-key, RouteChange>  $changed
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
            'changed' => array_map(fn (RouteChange $c) => $c->toArray(), $this->changed),
        ];
    }
}
