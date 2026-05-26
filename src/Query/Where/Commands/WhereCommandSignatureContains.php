<?php

namespace Mateffy\Introspect\Query\Where\Commands;

use Mateffy\Introspect\DTO\Command;
use Mateffy\Introspect\Query\Where\CommandWhere;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Illuminate\Support\Collection;

class WhereCommandSignatureContains implements CommandWhere
{
    use NotInverter;

    public Collection $texts;

    public function __construct(array|Collection $texts, public bool $not = false, public bool $all = true)
    {
        $this->texts = collect($texts);
    }

    public function filter(Command $value): bool
    {
        $signature = $value->signature;

        if ($this->all) {
            $passes = $this->texts->every(fn (string $text) => str_contains($signature, $text));
        } else {
            $passes = $this->texts->some(fn (string $text) => str_contains($signature, $text));
        }

        return $this->invert($passes, $this->not);
    }
}
