<?php

namespace Mateffy\Introspect\DTO;

use Illuminate\Support\Collection;

readonly class ModelProperty
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ?string $default,
        public bool $readable,
        public bool $writable,
        public bool $fillable,
        public bool $hidden,
        public bool $appended,
        public bool $relation,
        public ?string $cast,
        public Collection $types,
    ) {}
}
