<?php

namespace App\Domains\Banks\Enums;

enum BankStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
