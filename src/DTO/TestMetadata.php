<?php

declare(strict_types=1);

namespace Mateffy\Introspect\DTO;

use Illuminate\Support\Collection;

/**
 * Represents test metadata including notes, tags, and references
 * Spec-compatible with the tests package schema
 */
readonly class TestMetadata
{
    /**
     * @param Collection<string, mixed>|null $custom
     */
    public function __construct(
        public ?string $notes = null,
        public ?array $tags = null,
        public ?array $references = null,
        public ?Collection $custom = null,
    ) {}

    public function toArray(): array
    {
        $result = [];

        if ($this->notes !== null) {
            $result['notes'] = $this->notes;
        }

        if ($this->tags !== null) {
            $result['tags'] = $this->tags;
        }

        if ($this->references !== null && count($this->references) > 0) {
            $result['references'] = array_map(
                fn (TestReference $ref) => $ref->toArray(),
                $this->references
            );
        }

        if ($this->custom !== null && $this->custom->isNotEmpty()) {
            $result['custom'] = $this->custom->toArray();
        }

        return $result;
    }
}
