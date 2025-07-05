<?php

namespace App\Models;

enum AuthTypeEnum: string {
    case Email = 'Email';
    case Google = 'Google';
    case Apple = 'Apple';
    case Facebook = 'Facebook';
    case Twitter = 'Twitter';
    case Microsoft = 'Microsoft';
    case Other = 'Other'; // For any other authentication methods not listed
}