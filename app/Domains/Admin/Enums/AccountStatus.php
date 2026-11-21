<?php

namespace App\Domains\Admin\Enums;

enum AccountStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
