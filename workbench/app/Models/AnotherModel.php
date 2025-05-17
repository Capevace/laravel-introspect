<?php

namespace Workbench\App\Models;

use Workbench\App\Interfaces\AnotherInterface;
use Workbench\App\Traits\AnotherTrait;

class AnotherModel extends BaseModel implements AnotherInterface
{
    use AnotherTrait;

    public function anotherMethod(): void
    {
        // Implementation of AnotherInterface method
    }
}
