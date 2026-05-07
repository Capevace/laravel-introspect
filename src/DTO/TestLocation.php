<?php

declare(strict_types=1);

namespace Mateffy\Introspect\DTO;

/**
 * Represents a test location in source code
 * Spec-compatible with the tests package schema
 */
readonly class TestLocation
{
    public function __construct(
        public string $file,
        public ?int $line = null,
        public ?int $endLine = null,
        public ?string $methodName = null,
    ) {}

    public function toArray(): array
    {
        $result = [
            'file' => $this->file,
        ];

        if ($this->line !== null) {
            $result['line'] = $this->line;
        }

        if ($this->endLine !== null) {
            $result['endLine'] = $this->endLine;
        }

        if ($this->methodName !== null) {
            $result['methodName'] = $this->methodName;
        }

        return $result;
    }
}
