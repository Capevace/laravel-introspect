<?php

namespace Mateffy\Introspect\DTO;

use Symfony\Component\Console\Command\Command as SymfonyCommand;

readonly class Command
{
    public function __construct(
        public string $name,
        public string $signature,
        public ?string $description,
        public string $namespace,
    ) {}

    public static function fromSymfonyCommand(SymfonyCommand $command): self
    {
        $name = $command->getName();

        return new self(
            name: $name,
            signature: $command->getSynopsis(),
            description: $command->getDescription() ?: null,
            namespace: str_contains($name, ':')
                ? explode(':', $name)[0]
                : '_root',
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'signature' => $this->signature,
            'description' => $this->description,
            'namespace' => $this->namespace,
        ];
    }
}
