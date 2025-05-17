<?php

namespace Mateffy\Introspect\Query\Where\Views;

use Illuminate\Support\Collection;
use Mateffy\Introspect\Facades\Introspect;
use Mateffy\Introspect\Query\Where\Concerns\NotInverter;
use Mateffy\Introspect\Query\Where\ViewWhere;
use Mateffy\Introspect\View\ViewAnalysis;

class WhereUsedByView implements ViewWhere
{
    use NotInverter;

    /**
     * @var Collection<string>|null
     */
    public ?Collection $viewNames = null;

    public function __construct(public string $usedByView, public bool $not = false, public bool $lax = false)
    {
        // We support wildcards in the view name, so we need to check if the view name contains a wildcard.
        // In that case, we can't directly use $usedByView as the view name, so instead we go through them all to find the matching ones.

        if (str_contains($this->usedByView, '*')) {
            $this->viewNames = Introspect::views()
                ->whereNameEquals($this->usedByView)
                ->get();
        }
    }

    public function filter(string $value): bool
    {
        $analysis = app(ViewAnalysis::class);
        $included = false;

        if ($this->viewNames) {
            foreach ($this->viewNames as $viewName) {
                $included = $analysis->viewIncludesView(
                    containerView: $viewName,
                    includedView: $value,
                    lax: $this->lax,
                );

                if ($included) {
                    break;
                }
            }
        } elseif (view()->exists($this->usedByView)) {
            $included = $analysis->viewIncludesView(
                containerView: $this->usedByView,
                includedView: $value,
                lax: $this->lax,
            );
        }

        return $this->invert($included, $this->not);
    }
}
