<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query\Builder;

use Mateffy\Introspect\Query\Where\Tests\WhereTestDescriptionContains;
use Mateffy\Introspect\Query\Where\Tests\WhereTestDescriptionEndsWith;
use Mateffy\Introspect\Query\Where\Tests\WhereTestDescriptionEquals;
use Mateffy\Introspect\Query\Where\Tests\WhereTestDescriptionStartsWith;
use Mateffy\Introspect\Query\Where\Tests\WhereTestFileMatches;
use Mateffy\Introspect\Query\Where\Tests\WhereTestHasReferenceType;
use Mateffy\Introspect\Query\Where\Tests\WhereTestHasTag;
use Mateffy\Introspect\Query\Where\Tests\WhereTestStatusEquals;

/**
 * Trait providing filter methods for test queries
 */
trait WhereTests
{
    abstract public function addWhere(\Mateffy\Introspect\Query\Where\Where $where): static;

    public function whereFileMatches(string $pattern, bool $not = false): static
    {
        $this->addWhere(new WhereTestFileMatches($pattern, $not));

        return $this;
    }

    public function whereFileDoesntMatch(string $pattern): static
    {
        return $this->whereFileMatches($pattern, true);
    }

    public function whereDescriptionEquals(string $text, bool $not = false): static
    {
        $this->addWhere(new WhereTestDescriptionEquals($text, $not));

        return $this;
    }

    public function whereDescriptionDoesntEqual(string $text): static
    {
        return $this->whereDescriptionEquals($text, true);
    }

    public function whereDescriptionStartsWith(string $text, bool $not = false): static
    {
        $this->addWhere(new WhereTestDescriptionStartsWith($text, $not));

        return $this;
    }

    public function whereDescriptionDoesntStartWith(string $text): static
    {
        return $this->whereDescriptionStartsWith($text, true);
    }

    public function whereDescriptionEndsWith(string $text, bool $not = false): static
    {
        $this->addWhere(new WhereTestDescriptionEndsWith($text, $not));

        return $this;
    }

    public function whereDescriptionDoesntEndWith(string $text): static
    {
        return $this->whereDescriptionEndsWith($text, true);
    }

    public function whereDescriptionContains(string $text, bool $not = false): static
    {
        $this->addWhere(new WhereTestDescriptionContains($text, $not));

        return $this;
    }

    public function whereDescriptionDoesntContain(string $text): static
    {
        return $this->whereDescriptionContains($text, true);
    }

    public function whereStatusEquals(string $status, bool $not = false): static
    {
        $this->addWhere(new WhereTestStatusEquals($status, $not));

        return $this;
    }

    public function whereStatusDoesntEqual(string $status): static
    {
        return $this->whereStatusEquals($status, true);
    }

    public function whereHasTag(string $tag, bool $not = false): static
    {
        $this->addWhere(new WhereTestHasTag($tag, $not));

        return $this;
    }

    public function whereDoesntHaveTag(string $tag): static
    {
        return $this->whereHasTag($tag, true);
    }

    public function whereHasReferenceType(string $type, bool $not = false): static
    {
        $this->addWhere(new WhereTestHasReferenceType($type, $not));

        return $this;
    }

    public function whereDoesntHaveReferenceType(string $type): static
    {
        return $this->whereHasReferenceType($type, true);
    }
}
