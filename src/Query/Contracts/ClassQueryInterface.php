<?php

namespace Mateffy\Introspect\Query\Contracts;

use Mateffy\Introspect\Query\Query;

interface ClassQueryInterface extends Query
{
    /**
     * @param  class-string  $classpath
     */
    public function whereExtends(string $classpath): self;

    /**
     * @param  class-string  $classpath
     */
    public function whereDoesntExtend(string $classpath): self;

    /**
     * @param  class-string|class-string[]  $interface
     */
    public function whereImplements(string|array $interface): self;

    /**
     * @param  class-string|class-string[]  $interface
     */
    public function whereDoesntImplement(string|array $interface): self;

    /**
     * @param  class-string|class-string[]  $trait
     */
    public function whereUses(string|array $trait): self;

    /**
     * @param  class-string|class-string[]  $trait
     */
    public function whereDoesntUse(string|array $trait): self;

    public function whereNameContains(string|array $text): self;
    public function whereNameDoesntContain(string|array $text, bool $all = true): self;
    public function whereNameStartsWith(string|array $text): self;
    public function whereNameDoesntStartWith(string|array $text, bool $all = true): self;
    public function whereNameEndsWith(string|array $text): self;
    public function whereNameDoesntEndWith(string|array $text, bool $all = true): self;
    public function whereNameEquals(string|array $text): self;
    public function whereNameDoesntEqual(string|array $text, bool $all = true): self;

}
