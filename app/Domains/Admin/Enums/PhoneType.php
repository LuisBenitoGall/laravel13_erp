<?php

namespace App\Domains\Admin\Enums;

enum PhoneType: string
{
    case Mobile = 'mobile';
    case Landline = 'landline';
    case Fax = 'fax';
    case Other = 'other';
}
