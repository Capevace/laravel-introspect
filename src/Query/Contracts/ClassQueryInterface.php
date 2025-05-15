<?php

namespace Mateffy\Introspect\Query\Contracts;

interface ClassQueryInterface extends QueryInterface
{
    /**
     * @param class-string $classpath
     */
	public function whereExtends(string $classpath): self;

    /**
     * @param class-string $classpath
     */
	public function whereDoesntExtend(string $classpath): self;

    /**
     * @param class-string|class-string[] $interface
     */
    public function whereImplements(string|array $interface): self;

    /**
     * @param class-string|class-string[] $interface
     */
    public function whereDoesntImplement(string|array $interface): self;

    /**
     * @param class-string|class-string[] $trait
     */
    public function whereUses(string|array $trait): self;

    /**
     * @param class-string|class-string[] $trait
     */
    public function whereDoesntUse(string|array $trait): self;
}
