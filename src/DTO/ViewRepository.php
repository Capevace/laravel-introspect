<?php

namespace Mateffy\Introspect\DTO;

use Illuminate\Support\Collection;

readonly class ViewRepository
{
    public function __construct(

        /**
         * @var Collection<string> $views
         */
        public Collection $views,
        public ?string $namespace
    ) {}

    public function getViewsAsAbsoluteString(): Collection
    {
        if ($this->namespace) {
            return $this->views
                ->map(fn ($view) => "{$this->namespace}::{$view}");
        } else {
            return $this->views;
        }
    }
}
