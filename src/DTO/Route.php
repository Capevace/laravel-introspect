<?php

namespace Mateffy\Introspect\DTO;

readonly class Route
{
    public function __construct(
        public string $uri,
        public ?string $name,
        public array $methods,
        public ?string $controller,
        public ?string $action,
        public array $middlewares,
    ) {}

    public static function fromRoute(\Illuminate\Routing\Route $route): self
    {
        $controller = $route->getControllerClass();
        $action = $route->getActionMethod();

        if ($controller !== null && $controller === $action) {
            $action = '__invoke';
        }

        return new self(
            uri: $route->uri(),
            name: $route->getName(),
            methods: $route->methods(),
            controller: $controller,
            action: $action,
            middlewares: $route->gatherMiddleware(),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'uri' => $this->uri,
            'methods' => $this->methods,
            'controller' => $this->controller,
            'action' => $this->action,
            'middlewares' => $this->middlewares,
        ];
    }
}
