<?php

namespace App\Domains\Admin\Enums;

enum PaymentMethodStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
