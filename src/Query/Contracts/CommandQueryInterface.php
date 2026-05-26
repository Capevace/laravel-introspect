<?php

namespace Mateffy\Introspect\Query\Contracts;

use Mateffy\Introspect\Query\Query;

interface CommandQueryInterface extends Query
{
    public function whereNameEquals(string $name): static;

    public function whereNameDoesntEqual(string $name): static;

    public function whereNameStartsWith(string $text): static;

    public function whereNameDoesntStartWith(string $text): static;

    public function whereNameEndsWith(string $text): static;

    public function whereNameDoesntEndWith(string $text): static;

    public function whereNameContains(string $text): static;

    public function whereNameDoesntContain(string $text): static;

    // Signatures

    public function whereSignatureContains(string $text): static;

    public function whereSignatureDoesntContain(string $text): static;

    // Descriptions

    public function whereDescriptionContains(string $text): static;

    public function whereDescriptionDoesntContain(string $text): static;
}
