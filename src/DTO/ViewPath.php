<?php

namespace Mateffy\Introspect\DTO;

readonly class ViewPath
{
    public function __construct(
        public string $path,
        public ?string $namespace = null
    ) {}
}
