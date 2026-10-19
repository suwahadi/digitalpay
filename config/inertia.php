<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Server Side Rendering
    |--------------------------------------------------------------------------
    |
    | These options configures if and how Inertia uses Server Side Rendering
    | to pre-render the initial visits made to your application's pages.
    |
    */

    'ssr' => [
        'enabled' => false,
        'url' => 'http://127.0.0.1:13714/render',
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing
    |--------------------------------------------------------------------------
    |
    | The values described here are used to locate Inertia components on the
    | filesystem. For instance, when using `assertInertia`, the assertion
    | will look for the component in a directory matching the given path.
    |
    */

    'testing' => [
        'ensure_pages_exist' => true,
        'page_paths' => [
            resource_path('js/Pages'),
        ],
    ],
];