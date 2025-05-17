<?php

namespace Mateffy\Introspect\Query\Where\Generic;

use Illuminate\Support\Collection;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;

trait WhereTextComparison
{
    use NotInverter;

    public Collection $texts;

    public function __construct(array|Collection $texts, public bool $not = false, public bool $all = true, public bool $caseless = false)
    {
        $this->texts = collect($texts);
    }

    public function filter($value): bool
    {
        $name = $this->getStandardizedValue($value);

        if ($this->all) {
            $passes = $this->getStandardizedTexts()
                ->every(fn (string $text) => $this->compare(needle: $text, haystack: $name));
        } else {
            $passes = $this->getStandardizedTexts()
                ->some(fn (string $text) => $this->compare(needle: $text, haystack: $name));
        }

        return $this->invert($passes, $this->not);
    }

    protected function getStandardizedValue($value): ?string
    {
        $name = $this->getName($value);

        if ($this->caseless && $name) {
            return strtolower($name);
        }

        return $name;
    }

    protected function getStandardizedTexts(): Collection
    {
        if ($this->caseless) {
            return $this->texts->map(fn (?string $text) => $text !== null ? strtolower($text) : null);
        }

        return $this->texts;
    }

    abstract protected function getName($value): ?string;

    abstract protected function compare(?string $needle, ?string $haystack): bool;
}
