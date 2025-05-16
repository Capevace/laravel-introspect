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
}
