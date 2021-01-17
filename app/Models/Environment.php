<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    public static function isTesting(): bool
    {
        return env('APP_ENV') === 'local';
    }

    public static function randomIP(): string
    {
        return mt_rand(0, 255) . "." . mt_rand(0, 255) . "." . mt_rand(0, 255) . "." . mt_rand(0, 255);
    }
}
