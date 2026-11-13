<?php

namespace App\Domains\Admin\Enums;

enum BankStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
