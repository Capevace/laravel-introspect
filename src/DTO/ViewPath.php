<?php

namespace Mateffy\Introspect\DTO;

use InvalidArgumentException;

readonly class ViewPath
{
    public function __construct(
        public string $path,
        public ?string $namespace = null
    )
    {
    }
}
