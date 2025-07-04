<?php

namespace Mateffy\Introspect\Tests\Fixtures;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * This is the short description in the first line of the model.
 *
 * This is a second line of the description.
 * This is another line.
 *
 * @property string $name
 * @property string $email
 * @property string $password
 * @property ?string $remember_token
 * @property-read DateTimeInterface $created_at
 * @property-read DateTimeInterface $updated_at
 */
class TestModelWithDocblock extends Model
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
