<?php

namespace Workbench\App\Models;

use Workbench\App\Interfaces\TestInterface;
use Workbench\App\Traits\TestTrait;

class TestModel extends BaseModel implements TestInterface
{
    use TestTrait;

    public function test_method(): void
    {
        // Implementation of TestInterface method
    }

    public function testMethod(): void
    {

    }
}
