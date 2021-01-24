<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    public static function isTesting(): bool
    {
        return env('APP_ENV') === 'local';
    }
}
