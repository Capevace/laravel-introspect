<?php

namespace Mateffy\Introspect\Query\Contracts;

use Mateffy\Introspect\Query\Query;

interface ViewQueryInterface extends Query
{
    /**
     * @param string|string[] $view`
     */
    public function whereUses(string|array $view): self;

    /**
     * @param string|string[] $view`
     */
    public function whereDoesntUse(string|array $view): self;

    /**
     * @param string|string[] $view`
     */
    public function whereUsedBy(string|array $view): self;

    /**
     * @param string|string[] $view`
     */
    public function whereNotUsedBy(string|array $view): self;

    public function whereNameStartsWith(string $text): self;
    public function whereNameDoesntStartWith(string $text): self;
    public function whereNameEndsWith(string $text): self;
    public function whereNameDoesntEndWith(string $text): self;
    public function whereNameContains(string $text): self;
    public function whereNameDoesntContain(string $text): self;
    public function whereNameEquals(string $text): self;
    public function whereNameDoesntEqual(string $text): self;
}
