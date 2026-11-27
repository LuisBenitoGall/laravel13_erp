<?php

namespace App\Domains\Catalog\Enums;

enum ProductType: string
{
    case Physical = 'physical';
    case Service = 'service';
}
