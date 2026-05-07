<?php

declare(strict_types=1);

namespace Mateffy\Introspect\DTO;

/**
 * Represents a single test case in spec-compatible format
 */
readonly class Test
{
    public function __construct(
        public string $description,
        public TestLocation $location,
        public string $caseStatus = 'active',
        public ?string $executionStatus = null,
        public ?TestMetadata $metadata = null,
        public ?string $id = null,
    ) {}

    public function toArray(): array
    {
        $result = [
            'description' => $this->description,
            'caseStatus' => $this->caseStatus,
            'location' => $this->location->toArray(),
        ];

        if ($this->executionStatus !== null) {
            $result['executionStatus'] = $this->executionStatus;
        }

        if ($this->metadata !== null) {
            $metadataArray = $this->metadata->toArray();
            if (! empty($metadataArray)) {
                $result['metadata'] = $metadataArray;
            }
        }

        if ($this->id !== null) {
            $result['id'] = $this->id;
        }

        return $result;
    }

    /**
     * Generate a unique test ID from file path and description
     * Uses FNV-1a hash for deterministic ID generation
     */
    public static function generateId(string $file, string $description): string
    {
        // Normalize: lowercase, trim, collapse whitespace
        $normalizedFile = strtolower(trim($file));
        $normalizedDesc = strtolower(trim($description));
        $normalizedDesc = preg_replace('/\s+/', ' ', $normalizedDesc);

        // Create deterministic input
        $input = "{$normalizedFile}:{$normalizedDesc}";

        // FNV-1a hash
        $hash = 0x811c9dc5;
        for ($i = 0; $i < strlen($input); $i++) {
            $hash ^= ord($input[$i]);
            $hash = ($hash + (($hash << 1) + ($hash << 4) + ($hash << 7) + ($hash << 8) + ($hash << 24))) & 0xffffffff;
        }

        return 'TEST-' . str_pad(dechex($hash), 8, '0', STR_PAD_LEFT);
    }
}
