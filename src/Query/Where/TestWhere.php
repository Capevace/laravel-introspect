<?php

declare(strict_types=1);

namespace Mateffy\Introspect\Query\Where;

use Mateffy\Introspect\DTO\Test;
use Mateffy\Introspect\Query\Where;

/**
 * Interface for test filters
 */
interface TestWhere extends Where
{
    /**
     * Filter a test
     */
    public function filter(Test $test): bool;
}
