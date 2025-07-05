<?php

namespace App\Models;

enum GenderEnum: string {
    case Male = 'Male';
    case Female = 'Female';
    case Other = 'Other';
}