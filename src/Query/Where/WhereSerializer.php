<?php

namespace Mateffy\Introspect\Query\Where;

use InvalidArgumentException;
use Mateffy\Introspect\Query\Where;

class WhereSerializer
{
    public function serialize(Where $where): array
    {
        return $where->toArray();
    }

    public function deserialize(array $data): Where
    {
        $class = match ($data['type']) {
            default => throw new InvalidArgumentException('Unknown where type: '.$data['type']),
            'nested' => NestedWhere::class,
            'nested:class' => Classes\NestedClassWhereInterface::class,
            //            'nested:view' => Views\NestedViewWhere::class,
            //            'nested:route' => Routes\NestedRouteWhere::class,
            //            'nested:controller' => Controllers\NestedControllerWhere::class,q

            // View wheres
            'view-used-by' => Views\WhereUsedByView::class,
            'view-uses' => Views\WhereUsesView::class,
            'view-name-starts-with' => Views\WhereViewNameStartsWith::class,
            'view-name-ends-with' => Views\WhereViewNameEndsWith::class,
            'view-name-contains' => Views\WhereViewNameContains::class,

            // Route wheres
            'route-name-starts-with' => Routes\WhereRouteNameStartsWith::class,
            'route-name-ends-with' => Routes\WhereNameEndsWith::class,
            'route-name-contains' => Routes\WhereRouteNameContains::class,
            'route-path-starts-with' => Routes\WhereRoutePathStartsWith::class,
            'route-path-ends-with' => Routes\WhereRoutePathEndsWith::class,
            'route-path-contains' => Routes\WhereRoutePathContains::class,
            'route-uses-controller' => Routes\WhereRouteUsesController::class,
            'route-uses-methods' => Routes\WhereRouteUsesMethods::class,
            'route-uses-middlewares' => Routes\WhereRouteUsesMiddlewares::class,

            // Class wheres
            'class-extends' => Classes\WhereExtendsClass::class,
            'class-implements' => Classes\WhereImplementsInterfaces::class,
            'class-uses-trait' => Classes\WhereUsesTraits::class,
        };

        return $class::fromArray($data);
    }
}
