<?php

declare(strict_types=1);

namespace Mateffy\Introspect\DTO;

/**
 * Represents a reference to external entities (issues, PRs, specs, etc.)
 * Spec-compatible with the tests package schema
 */
readonly class TestReference
{
    public function __construct(
        public string $type,
        public string $value,
        public ?string $label = null,
        public ?string $url = null,
    ) {}

    public function toArray(): array
    {
        $result = [
            'type' => $this->type,
            'value' => $this->value,
        ];

        if ($this->label !== null) {
            $result['label'] = $this->label;
        }

        if ($this->url !== null) {
            $result['url'] = $this->url;
        }

        return $result;
    }
}
