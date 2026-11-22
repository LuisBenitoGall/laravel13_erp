<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Marca del producto (Coopmercio)
    |--------------------------------------------------------------------------
    |
    | name / logo_url / mark_url son configurables por entorno. Los colores
    | canónicos documentan la paleta; el front operativo usa @theme en CSS.
    |
    */

    'name' => env('APP_BRAND_NAME', 'Coopmercio'),

    'logo_url' => env('APP_LOGO_URL', '/images/branding/coopmercio-horizontal-color.svg'),

    'mark_url' => env('APP_MARK_URL', '/images/branding/coopmercio-symbol-color.svg'),

    'colors' => [
        'primary' => '#10A37F',
        'secondary' => '#633D56',
    ],

];
