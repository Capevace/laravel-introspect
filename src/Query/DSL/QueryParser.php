<?php

namespace Mateffy\Introspect\Query\DSL;

/**
 * Compact DSL Parser for AI-friendly queries
 *
 * Supports:
 * - Symbol operators: =, !=, ^=, $=, *=, ?&, ?|, etc.
 * - Text operators: EQ, NEQ, STARTS_WITH, ENDS_WITH, CONTAINS, HAS_ALL, etc.
 * - Boolean logic: &&, ||, AND, OR, !, NOT
 * - Grouping with parentheses
 *
 * Examples:
 * - "name = api.users.*"
 * - "path ^= api && middleware ?= auth"
 * - "(method = POST || method = PUT) && middleware ?& auth,verified"
 * - "hasFillable HAS_ALL email,password AND hasRelationship EQ user"
 */
class QueryParser
{
    /**
     * Operator definitions with both symbol and text variants
     */
    protected const OPERATORS = [
        // Equality
        '=' => ['aliases' => ['EQ', 'EQUALS'], 'type' => 'equals'],
        '!=' => ['aliases' => ['NEQ', 'NOT_EQ', 'NOT_EQUALS'], 'type' => 'not_equals'],

        // String matching
        '^=' => ['aliases' => ['STARTS_WITH', 'SW'], 'type' => 'starts_with'],
        '$=' => ['aliases' => ['ENDS_WITH', 'EW'], 'type' => 'ends_with'],
        '*=' => ['aliases' => ['CONTAINS', 'HAS', 'INCLUDES'], 'type' => 'contains'],

        // Negated string matching
        '!^=' => ['aliases' => ['NOT_STARTS_WITH', 'NOT_SW'], 'type' => 'not_starts_with'],
        '!$=' => ['aliases' => ['NOT_ENDS_WITH', 'NOT_EW'], 'type' => 'not_ends_with'],
        '!*=' => ['aliases' => ['NOT_CONTAINS', 'NOT_HAS', 'NOT_INCLUDES'], 'type' => 'not_contains'],

        // Array operations
        '?=' => ['aliases' => ['HAS', 'INCLUDES', 'CONTAINS'], 'type' => 'has'],
        '?&' => ['aliases' => ['HAS_ALL', 'CONTAINS_ALL', 'INCLUDES_ALL'], 'type' => 'has_all'],
        '?|' => ['aliases' => ['HAS_ANY', 'INCLUDES_ANY', 'CONTAINS_ANY'], 'type' => 'has_any'],
    ];

    /**
     * Boolean operators
     */
    protected const BOOLEAN_OPS = [
        '&&' => 'and',
        'AND' => 'and',
        '||' => 'or',
        'OR' => 'or',
    ];

    /**
     * NOT operators
     */
    protected const NOT_OPS = ['!', 'NOT'];

    /**
     * Tokenize the query string
     */
    protected function tokenize(string $query): array
    {
        $tokens = [];
        $length = strlen($query);
        $i = 0;

        while ($i < $length) {
            $char = $query[$i];

            // Skip whitespace
            if (ctype_space($char)) {
                $i++;

                continue;
            }

            // Parentheses
            if ($char === '(' || $char === ')') {
                $tokens[] = ['type' => 'paren', 'value' => $char];
                $i++;

                continue;
            }

            // Check for multi-character operators first (longer ones have priority)
            // Sort operators by length descending to check longer ones first
            $operators = array_keys(self::OPERATORS);
            usort($operators, fn ($a, $b) => strlen($b) <=> strlen($a));

            $foundOp = null;
            foreach ($operators as $op) {
                $opLen = strlen($op);
                if ($i + $opLen <= $length && substr($query, $i, $opLen) === $op) {
                    $foundOp = $op;
                    break; // Found the longest match
                }
            }

            if ($foundOp !== null) {
                $tokens[] = ['type' => 'operator', 'value' => $foundOp];
                $i += strlen($foundOp);

                continue;
            }

            // Check for boolean operators (check longer first)
            $booleanOps = array_keys(self::BOOLEAN_OPS);
            usort($booleanOps, fn ($a, $b) => strlen($b) <=> strlen($a));

            foreach ($booleanOps as $op) {
                $opLen = strlen($op);
                if ($i + $opLen <= $length && substr($query, $i, $opLen) === $op) {
                    $tokens[] = ['type' => 'boolean', 'value' => self::BOOLEAN_OPS[$op]];
                    $i += $opLen;

                    continue 2;
                }
            }

            // Check for NOT operators (only standalone ! or NOT)
            foreach (self::NOT_OPS as $op) {
                $opLen = strlen($op);
                if ($i + $opLen <= $length && substr($query, $i, $opLen) === $op) {
                    // For '!' make sure it's not followed by = (which would be !=)
                    if ($op === '!' && $i + 1 < $length && $query[$i + 1] === '=') {
                        continue; // Skip, this will be caught as != operator
                    }
                    $tokens[] = ['type' => 'not', 'value' => $op];
                    $i += $opLen;

                    continue 2;
                }
            }

            // Check for text operators
            $textOp = $this->matchTextOperator(substr($query, $i));
            if ($textOp !== null) {
                $tokens[] = ['type' => 'operator', 'value' => $textOp];
                $i += strlen($textOp);

                continue;
            }

            // Quoted strings
            if ($char === '"' || $char === "'") {
                $quote = $char;
                $j = $i + 1;
                $value = '';
                while ($j < $length && $query[$j] !== $quote) {
                    if ($query[$j] === '\\' && $j + 1 < $length) {
                        $j++;
                        $value .= $query[$j];
                    } else {
                        $value .= $query[$j];
                    }
                    $j++;
                }
                $tokens[] = ['type' => 'value', 'value' => $value];
                $i = $j + 1;

                continue;
            }

            // Identifiers/values (unquoted)
            if (ctype_alnum($char) || $char === '_' || $char === '.' || $char === '\\' || $char === ':' || $char === '-' || $char === '*') {
                $j = $i;
                while ($j < $length && (ctype_alnum($query[$j]) || in_array($query[$j], ['_', '.', '\\', ':', '-', '*', ',']))) {
                    $j++;
                }
                $value = substr($query, $i, $j - $i);

                // Determine if it's a field or value based on context
                $tokens[] = ['type' => 'identifier', 'value' => $value];
                $i = $j;

                continue;
            }

            // Unknown character, skip
            $i++;
        }

        return $tokens;
    }

    /**
     * Match a text operator at the current position
     */
    protected function matchTextOperator(string $remaining): ?string
    {
        foreach (self::OPERATORS as $symbol => $def) {
            foreach ($def['aliases'] as $alias) {
                $len = strlen($alias);
                if (substr($remaining, 0, $len) === $alias) {
                    // Make sure it's followed by whitespace or end
                    if ($len >= strlen($remaining) || ctype_space($remaining[$len])) {
                        return $symbol;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Parse tokens into an AST
     */
    protected function parse(array $tokens): array
    {
        $this->position = 0;
        $this->tokens = $tokens;

        $result = $this->parseExpression();

        return $result;
    }

    protected int $position = 0;

    protected array $tokens = [];

    protected function parseExpression(): array
    {
        return $this->parseOr();
    }

    protected function parseOr(): array
    {
        $left = $this->parseAnd();

        while ($this->peek() && $this->peek()['type'] === 'boolean' && $this->peek()['value'] === 'or') {
            $this->consume(); // consume OR
            $right = $this->parseAnd();

            // Flatten nested ORs
            if (isset($left['or'])) {
                $left['or'][] = $right;
            } else {
                $left = ['or' => [$left, $right]];
            }
        }

        return $left;
    }

    protected function parseAnd(): array
    {
        $left = $this->parseUnary();

        while ($this->peek() && $this->peek()['type'] === 'boolean' && $this->peek()['value'] === 'and') {
            $this->consume(); // consume AND
            $right = $this->parseUnary();

            // Flatten nested ANDs
            if (isset($left['and'])) {
                $left['and'][] = $right;
            } else {
                $left = ['and' => [$left, $right]];
            }
        }

        return $left;
    }

    protected function parseUnary(): array
    {
        // Handle NOT
        if ($this->peek() && $this->peek()['type'] === 'not') {
            $this->consume(); // consume NOT
            $operand = $this->parseUnary();

            return ['not' => $operand];
        }

        // Handle parentheses
        if ($this->peek() && $this->peek()['type'] === 'paren' && $this->peek()['value'] === '(') {
            $this->consume(); // consume '('
            $expr = $this->parseExpression();
            $this->expect('paren', ')');

            return $expr;
        }

        return $this->parseCondition();
    }

    protected function parseCondition(): array
    {
        $field = $this->expect('identifier')['value'];

        $opToken = $this->expect('operator');
        $operator = self::OPERATORS[$opToken['value']]['type'];

        $value = $this->expect('identifier')['value'];

        // Handle array values (comma-separated)
        $values = str_contains($value, ',')
            ? array_map('trim', explode(',', $value))
            : [$value];

        return [
            'field' => $field,
            'operator' => $operator,
            'value' => $values,
        ];
    }

    protected function peek(): ?array
    {
        return $this->tokens[$this->position] ?? null;
    }

    protected function consume(): ?array
    {
        return $this->tokens[$this->position++] ?? null;
    }

    protected function expect(string $type, ?string $value = null): array
    {
        $token = $this->consume();

        if ($token === null || $token['type'] !== $type) {
            throw new \InvalidArgumentException("Expected {$type} but got ".($token['type'] ?? 'null'));
        }

        if ($value !== null && $token['value'] !== $value) {
            throw new \InvalidArgumentException("Expected '{$value}' but got '{$token['value']}'");
        }

        return $token;
    }

    /**
     * Parse a query string into an AST
     */
    public function parseQuery(string $query): array
    {
        $tokens = $this->tokenize($query);

        return $this->parse($tokens);
    }
}
