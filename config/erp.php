<?php

return [

    'user_profile' => [
        /**
         * Spatie permission slug that allows changing another user's
         * `is_public` / `status` from the unified user edit form.
         * Null until product defines the slug (self always allowed).
         */
        'visibility_permission' => null,
    ],

];
