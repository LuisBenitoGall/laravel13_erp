<?php

namespace App\Domains\Admin\Enums;

enum CompanyAccountStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
