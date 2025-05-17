<?php

namespace Mateffy\Introspect\Query\Where\Views;

use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\ViewWhere;
use Mateffy\Introspect\View\ViewAnalysis;

class WhereUsesView implements ViewWhere
{
    use NotInverter;

    public function __construct(public string $viewToUse, public bool $not = false, public bool $lax = false)
    {
    }

    public function filter(string $value): bool
    {
        $analysis = app(ViewAnalysis::class);
        $included = $analysis->viewIncludesView(
			containerView: $value,
			includedView: $this->viewToUse,
			lax: $this->lax,
		);

		return $this->invert($included, $this->not);
    }
}
