<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait TokenGenerator
{
    public function generateUUID()
    {
        $uuid = Str::uuid(100); // This generates a token
        $uuidWithoutHyphens = str_replace('-', '', $uuid); // Remove hyphens
        return $uuidWithoutHyphens;
    }

    public function generateToken($length = null)
    {
        // If $length is null, set it to 50
        $length = $length ?? 50;
        $token = Str::random($length);
        return $token;
    }

}
