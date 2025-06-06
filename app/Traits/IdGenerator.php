<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait IdGenerator
{
    public function generateID()
    {
        $uuid = Str::uuid(30); // This generates a token
        $uuidWithoutHyphens = str_replace('-', '', $uuid); // Remove hyphens
        return $uuidWithoutHyphens;
    }

    public function generateRandomString($length){
        $randomCapitalString = Str::random($length); // Generates a random string with 10 characters
        
        return strtoupper($randomCapitalString);
    }

}
