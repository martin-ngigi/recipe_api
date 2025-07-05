<?php

namespace App\Models;

enum UserRoleEnum: string {
    case Admin = 'Admin';
    case Customer = 'Customer';
    case Chef = 'Chef';
}