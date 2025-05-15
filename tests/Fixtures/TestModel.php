<?php

namespace Mateffy\Introspect\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    protected $table = 'test_models';

    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
