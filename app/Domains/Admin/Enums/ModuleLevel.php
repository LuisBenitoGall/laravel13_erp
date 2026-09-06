<?php

namespace App\Domains\Admin\Enums;

enum ModuleLevel: string
{
    case AdminOnly = 'admin_only';
    case Basic = 'basic';
    case Optional = 'optional';
}
