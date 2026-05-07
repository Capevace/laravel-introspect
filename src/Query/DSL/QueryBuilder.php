<?php

namespace Mateffy\Introspect\Query\DSL;

use Mateffy\Introspect\Query\Contracts\ClassQueryInterface;
use Mateffy\Introspect\Query\Contracts\ModelQueryInterface;
use Mateffy\Introspect\Query\Contracts\RouteQueryInterface;
use Mateffy\Introspect\Query\Contracts\TestQueryInterface;
use Mateffy\Introspect\Query\Contracts\ViewQueryInterface;

/**
 * Builds queries from parsed DSL AST
 */
class QueryBuilder
{
    /**
     * Apply a parsed query to a ViewQuery
     */
    public function applyToViews(ViewQueryInterface $query, array $ast): ViewQueryInterface
    {
        $this->applyNode($query, $ast, 'view');

        return $query;
    }

    /**
     * Apply a parsed query to a RouteQuery
     */
    public function applyToRoutes(RouteQueryInterface $query, array $ast): RouteQueryInterface
    {
        $this->applyNode($query, $ast, 'route');

        return $query;
    }

    /**
     * Apply a parsed query to a ClassQuery
     */
    public function applyToClasses(ClassQueryInterface $query, array $ast): ClassQueryInterface
    {
        $this->applyNode($query, $ast, 'class');

        return $query;
    }

    /**
     * Apply a parsed query to a ModelQuery
     */
    public function applyToModels(ModelQueryInterface $query, array $ast): ModelQueryInterface
    {
        $this->applyNode($query, $ast, 'model');

        return $query;
    }

    /**
     * Apply a parsed query to a TestQuery
     */
    public function applyToTests(TestQueryInterface $query, array $ast): TestQueryInterface
    {
        $this->applyNode($query, $ast, 'test');

        return $query;
    }

    /**
     * Recursively apply an AST node to a query
     */
    protected function applyNode($query, array $node, string $context): void
    {
        if (isset($node['and'])) {
            // AND is implicit in query building - just apply all conditions
            foreach ($node['and'] as $child) {
                $this->applyNode($query, $child, $context);
            }
        } elseif (isset($node['or'])) {
            // OR requires using the or() callback method
            $query->or(function ($subQuery) use ($node, $context) {
                foreach ($node['or'] as $child) {
                    $this->applyNode($subQuery, $child, $context);
                }

                return $subQuery;
            });
        } elseif (isset($node['not'])) {
            // NOT - we need to negate the condition
            // This is handled by the individual condition methods
            $this->applyNotNode($query, $node['not'], $context);
        } else {
            // Leaf condition
            $this->applyCondition($query, $node, $context);
        }
    }

    /**
     * Apply a negated condition
     */
    protected function applyNotNode($query, array $node, string $context): void
    {
        if (isset($node['and'])) {
            // NOT(A AND B) = (NOT A) OR (NOT B) - De Morgan's law
            $conditions = [];
            foreach ($node['and'] as $child) {
                $conditions[] = ['not' => $child];
            }
            $this->applyNode($query, ['or' => $conditions], $context);
        } elseif (isset($node['or'])) {
            // NOT(A OR B) = (NOT A) AND (NOT B) - De Morgan's law
            foreach ($node['or'] as $child) {
                $this->applyNotNode($query, $child, $context);
            }
        } elseif (isset($node['not'])) {
            // Double negation - just apply the inner node
            $this->applyNode($query, $node['not'], $context);
        } else {
            // Negate the leaf condition by modifying operator
            $this->applyCondition($query, $node, $context, true);
        }
    }

    /**
     * Apply a single condition to the query
     */
    protected function applyCondition($query, array $node, string $context, bool $negate = false): void
    {
        $field = $node['field'];
        $operator = $node['operator'];
        $values = $node['value'];

        // If negated, flip the operator
        if ($negate) {
            $operator = $this->negateOperator($operator);
        }

        // Get the first value for single-value operations
        $singleValue = $values[0] ?? null;

        switch ($context) {
            case 'view':
                $this->applyViewCondition($query, $field, $operator, $values, $singleValue);
                break;
            case 'route':
                $this->applyRouteCondition($query, $field, $operator, $values, $singleValue);
                break;
            case 'class':
                $this->applyClassCondition($query, $field, $operator, $values, $singleValue);
                break;
            case 'model':
                $this->applyModelCondition($query, $field, $operator, $values, $singleValue);
                break;
            case 'test':
                $this->applyTestCondition($query, $field, $operator, $values, $singleValue);
                break;
        }
    }

    protected function applyViewCondition(ViewQueryInterface $query, string $field, string $operator, array $values, ?string $singleValue): void
    {
        $field = strtolower($field);

        switch ($field) {
            case 'name':
                $this->applyStringOperator($query, $operator, $values, [
                    'equals' => 'whereNameEquals',
                    'not_equals' => 'whereNameDoesntEqual',
                    'starts_with' => 'whereNameStartsWith',
                    'not_starts_with' => 'whereNameDoesntStartWith',
                    'ends_with' => 'whereNameEndsWith',
                    'not_ends_with' => 'whereNameDoesntEndWith',
                    'contains' => 'whereNameContains',
                    'not_contains' => 'whereNameDoesntContain',
                ]);
                break;
            case 'uses':
            case 'use':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereUses($singleValue);
                } else {
                    $query->whereDoesntUse($singleValue);
                }
                break;
            case 'usedby':
            case 'used_by':
                if ($operator === 'has' || $operator === 'equals' || $operator === 'contains') {
                    $query->whereUsedBy($singleValue);
                } else {
                    $query->whereNotUsedBy($singleValue);
                }
                break;
        }
    }

    protected function applyRouteCondition(RouteQueryInterface $query, string $field, string $operator, array $values, ?string $singleValue): void
    {
        $field = strtolower($field);

        switch ($field) {
            case 'name':
                $this->applyStringOperator($query, $operator, $values, [
                    'equals' => 'whereNameEquals',
                    'not_equals' => 'whereNameDoesntEqual',
                    'starts_with' => 'whereNameStartsWith',
                    'not_starts_with' => 'whereNameDoesntStartWith',
                    'ends_with' => 'whereNameEndsWith',
                    'not_ends_with' => 'whereNameDoesntEndWith',
                    'contains' => 'whereNameContains',
                    'not_contains' => 'whereNameDoesntContain',
                ]);
                break;
            case 'path':
                $this->applyStringOperator($query, $operator, $values, [
                    'equals' => 'wherePathEquals',
                    'not_equals' => 'wherePathDoesntEqual',
                    'starts_with' => 'wherePathStartsWith',
                    'not_starts_with' => 'wherePathDoesntStartWith',
                    'ends_with' => 'wherePathEndsWith',
                    'not_ends_with' => 'wherePathDoesntEndWith',
                    'contains' => 'wherePathContains',
                    'not_contains' => 'wherePathDoesntContain',
                ]);
                break;
            case 'middleware':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereUsesMiddleware($singleValue);
                } elseif ($operator === 'not_equals' || $operator === 'not_has') {
                    $query->whereDoesntUseMiddleware($singleValue);
                } elseif ($operator === 'has_all') {
                    $query->whereUsesMiddlewares($values, all: true);
                } elseif ($operator === 'has_any') {
                    $query->whereUsesMiddlewares($values, all: false);
                }
                break;
            case 'method':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereUsesMethod($singleValue);
                } elseif ($operator === 'not_equals' || $operator === 'not_has') {
                    $query->whereDoesntUseMethod($singleValue);
                } elseif ($operator === 'has_all') {
                    $query->whereUsesMethods($values, all: true);
                } elseif ($operator === 'has_any') {
                    $query->whereUsesMethods($values, all: false);
                }
                break;
            case 'controller':
                if ($operator === 'equals' || $operator === 'has') {
                    $query->whereUsesController($singleValue);
                } else {
                    $query->whereDoesntUseController($singleValue);
                }
                break;
            case 'hasparam':
            case 'has_parameter':
            case 'parameter':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereHasParameter($singleValue);
                } elseif ($operator === 'not_has' || $operator === 'not_equals') {
                    $query->whereDoesntHaveParameter($singleValue);
                } elseif ($operator === 'has_all') {
                    $query->whereHasParameters($values, all: true);
                } elseif ($operator === 'has_any') {
                    $query->whereHasParameters($values, all: false);
                }
                break;
        }
    }

    protected function applyClassCondition(ClassQueryInterface $query, string $field, string $operator, array $values, ?string $singleValue): void
    {
        $field = strtolower($field);

        switch ($field) {
            case 'name':
                $this->applyStringOperator($query, $operator, $values, [
                    'equals' => 'whereNameEquals',
                    'not_equals' => 'whereNameDoesntEqual',
                    'starts_with' => 'whereNameStartsWith',
                    'not_starts_with' => 'whereNameDoesntStartWith',
                    'ends_with' => 'whereNameEndsWith',
                    'not_ends_with' => 'whereNameDoesntEndWith',
                    'contains' => 'whereNameContains',
                    'not_contains' => 'whereNameDoesntContain',
                ]);
                break;
            case 'extends':
                if ($operator === 'equals' || $operator === 'has') {
                    $query->whereExtends($singleValue);
                } else {
                    $query->whereDoesntExtend($singleValue);
                }
                break;
            case 'implements':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereImplements($singleValue);
                } elseif ($operator === 'not_has' || $operator === 'not_equals') {
                    $query->whereDoesntImplement($singleValue);
                } elseif ($operator === 'has_all') {
                    $query->whereImplements($values, all: true);
                } elseif ($operator === 'has_any') {
                    $query->whereImplements($values, all: false);
                }
                break;
            case 'uses':
            case 'use':
            case 'uses_trait':
            case 'trait':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereUses($singleValue);
                } elseif ($operator === 'not_has' || $operator === 'not_equals') {
                    $query->whereDoesntUse($singleValue);
                } elseif ($operator === 'has_all') {
                    $query->whereUses($values, all: true);
                } elseif ($operator === 'has_any') {
                    $query->whereUses($values, all: false);
                }
                break;
        }
    }

    protected function applyModelCondition(ModelQueryInterface $query, string $field, string $operator, array $values, ?string $singleValue): void
    {
        $field = strtolower($field);

        // Handle class-based conditions (inherits from ClassQuery)
        if (in_array($field, ['name', 'extends', 'implements', 'uses', 'use', 'trait'])) {
            $this->applyClassCondition($query, $field, $operator, $values, $singleValue);

            return;
        }

        switch ($field) {
            case 'hasproperty':
            case 'has_property':
            case 'property':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereHasProperty($singleValue);
                } elseif ($operator === 'not_has' || $operator === 'not_equals') {
                    $query->whereDoesntHaveProperty($singleValue);
                } elseif ($operator === 'has_all') {
                    $query->whereHasProperties($values, all: true);
                } elseif ($operator === 'has_any') {
                    $query->whereHasProperties($values, all: false);
                }
                break;
            case 'hasfillable':
            case 'has_fillable':
            case 'fillable':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereHasFillable($singleValue);
                } elseif ($operator === 'not_has' || $operator === 'not_equals') {
                    $query->whereDoesntHaveFillable($singleValue);
                } elseif ($operator === 'has_all') {
                    $query->whereHasFillableProperties($values, all: true);
                } elseif ($operator === 'has_any') {
                    $query->whereHasFillableProperties($values, all: false);
                }
                break;
            case 'hashidden':
            case 'has_hidden':
            case 'hidden':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereHasHidden($singleValue);
                } elseif ($operator === 'not_has' || $operator === 'not_equals') {
                    $query->whereDoesntHaveHidden($singleValue);
                } elseif ($operator === 'has_all') {
                    $query->whereHasHiddenProperties($values, all: true);
                } elseif ($operator === 'has_any') {
                    $query->whereHasHiddenProperties($values, all: false);
                }
                break;
            case 'hasappended':
            case 'has_appended':
            case 'appended':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereHasAppended($singleValue);
                } elseif ($operator === 'not_has' || $operator === 'not_equals') {
                    $query->whereDoesntHaveAppended($singleValue);
                } elseif ($operator === 'has_all') {
                    $query->whereHasAppendedProperties($values, all: true);
                } elseif ($operator === 'has_any') {
                    $query->whereHasAppendedProperties($values, all: false);
                }
                break;
            case 'hasreadable':
            case 'has_readable':
            case 'readable':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereHasReadable($singleValue);
                } elseif ($operator === 'not_has' || $operator === 'not_equals') {
                    $query->whereDoesntHaveReadable($singleValue);
                } elseif ($operator === 'has_all') {
                    $query->whereHasReadableProperties($values, all: true);
                } elseif ($operator === 'has_any') {
                    $query->whereHasReadableProperties($values, all: false);
                }
                break;
            case 'haswritable':
            case 'has_writable':
            case 'writable':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereHasWritable($singleValue);
                } elseif ($operator === 'not_has' || $operator === 'not_equals') {
                    $query->whereDoesntHaveWritable($singleValue);
                } elseif ($operator === 'has_all') {
                    $query->whereHasWritableProperties($values, all: true);
                } elseif ($operator === 'has_any') {
                    $query->whereHasWritableProperties($values, all: false);
                }
                break;
            case 'hasrelationship':
            case 'has_relationship':
            case 'relationship':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereHasRelationship($singleValue);
                } else {
                    $query->whereDoesntHaveRelationship($singleValue);
                }
                break;
        }
    }

    /**
     * Apply a string operator using the method map
     */
    protected function applyStringOperator($query, string $operator, array $values, array $methodMap): void
    {
        $method = $methodMap[$operator] ?? null;

        if ($method === null) {
            throw new \InvalidArgumentException("Operator '{$operator}' not supported for this field");
        }

        $query->$method($values);
    }

    /**
     * Get the negated version of an operator
     */
    protected function negateOperator(string $operator): string
    {
        $negations = [
            'equals' => 'not_equals',
            'not_equals' => 'equals',
            'starts_with' => 'not_starts_with',
            'not_starts_with' => 'starts_with',
            'ends_with' => 'not_ends_with',
            'not_ends_with' => 'ends_with',
            'contains' => 'not_contains',
            'not_contains' => 'contains',
            'has' => 'not_has',
            'not_has' => 'has',
            'has_all' => 'not_has_all',
            'not_has_all' => 'has_all',
            'has_any' => 'not_has_any',
            'not_has_any' => 'has_any',
        ];

        return $negations[$operator] ?? $operator;
    }

    protected function applyTestCondition(TestQueryInterface $query, string $field, string $operator, array $values, ?string $singleValue): void
    {
        $field = strtolower($field);

        switch ($field) {
            case 'file':
            case 'path':
                if ($operator === 'equals' || $operator === 'has') {
                    $query->whereFileMatches($singleValue);
                } else {
                    $query->whereFileDoesntMatch($singleValue);
                }
                break;
            case 'description':
                $this->applyStringOperator($query, $operator, $values, [
                    'equals' => 'whereDescriptionEquals',
                    'not_equals' => 'whereDescriptionDoesntEqual',
                    'starts_with' => 'whereDescriptionStartsWith',
                    'not_starts_with' => 'whereDescriptionDoesntStartWith',
                    'ends_with' => 'whereDescriptionEndsWith',
                    'not_ends_with' => 'whereDescriptionDoesntEndWith',
                    'contains' => 'whereDescriptionContains',
                    'not_contains' => 'whereDescriptionDoesntContain',
                ]);
                break;
            case 'status':
            case 'casestatus':
                if ($operator === 'equals' || $operator === 'has') {
                    $query->whereStatusEquals($singleValue);
                } else {
                    $query->whereStatusDoesntEqual($singleValue);
                }
                break;
            case 'tag':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereHasTag($singleValue);
                } else {
                    $query->whereDoesntHaveTag($singleValue);
                }
                break;
            case 'reference':
            case 'reftype':
                if ($operator === 'has' || $operator === 'equals') {
                    $query->whereHasReferenceType($singleValue);
                } else {
                    $query->whereDoesntHaveReferenceType($singleValue);
                }
                break;
        }
    }
}
