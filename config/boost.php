<?php

declare(strict_types=1);

return [

    'enabled' => env('BOOST_ENABLED', true),

    /*
    | Project rules (.ai/rules) are NOT the ERP constitution. Kernel + OpenSpec
    | mandan. Default off so Record Rule no crea un estándar paralelo.
    */
    'rules' => [
        'enabled' => env('BOOST_RULES_ENABLED', false),
        'scoped_guidelines' => env('BOOST_RULES_SCOPED_GUIDELINES', false),
    ],

    'guidelines' => [
        'exclude' => [
            'livewire/core',
            'livewire/2',
            'livewire/3',
            'livewire/4',
            'fluxui/core',
            'fluxui/free',
            'fluxui/pro',
            'folio/core',
            'herd/core',
            'sail/core',
            'volt/core',
            'wayfinder/core',
            'pennant/core',
            'inertia-vue/core',
            'inertia-vue/1',
            'inertia-vue/2',
            'inertia-vue/3',
            'inertia-svelte/core',
            'inertia-svelte/1',
            'inertia-svelte/2',
            'inertia-svelte/3',
        ],
    ],

    'skills' => [
        'exclude' => [
            'livewire-development',
            'fluxui-development',
            'folio-routing',
            'volt-development',
            'wayfinder-development',
            'pennant-development',
            'inertia-vue-development',
            'inertia-svelte-development',
            'deploying-to-cloud',
        ],
    ],

    'executable_paths' => [
        'php' => env('BOOST_PHP_EXECUTABLE_PATH'),
        'composer' => env('BOOST_COMPOSER_EXECUTABLE_PATH'),
        'npm' => env('BOOST_NPM_EXECUTABLE_PATH'),
        'vendor_bin' => env('BOOST_VENDOR_BIN_EXECUTABLE_PATH'),
        'current_directory' => env('BOOST_CURRENT_DIRECTORY_EXECUTABLE_PATH'),
    ],

    'browser_logs_watcher' => env('BOOST_BROWSER_LOGS_WATCHER', true),

    'browser_log_levels' => explode(',', env('BOOST_BROWSER_LOG_LEVELS', 'error,warning,info,debug')),

];
