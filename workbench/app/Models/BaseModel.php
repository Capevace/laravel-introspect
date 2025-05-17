<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    // Base model implementation

    protected $fillable = [
        'nested_not_overridden',
    ];
}
