<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DT Table Settings
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default settings for your DataTables.
    |
    */
    'settings' => [
        'buttons' => ['copy', 'colvis', 'excel'],
        'theme' => 'bootstrap',
        'loader' => [
            'enabled' => true,
            'message' => 'Loading data, please wait...',
            'image' => null,
        ],
        'behaviour' => [
            'page_length' => 10,
            'ordering' => true,
            'searching' => true,
            'paging' => true,
            'info' => true,
            'scroll_x' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DT Table Server Side
    |--------------------------------------------------------------------------
    |
    | Tables given a `source` page, search and sort in the database, one page per
    | request. `middleware` guards the endpoints, so add `auth` to restrict them,
    | `max_length` caps how many rows one request may return, and `count_cache`
    | is how many seconds a row count is reused (0 counts on every request).
    |
    */
    'server_side' => [
        'middleware' => ['web'],
        'max_length' => 1000,
        'count_cache' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | DT Table Theme
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default theme options for your DataTables. The theme
    | will be used to style the DataTable components.
    |
    */
    'themes' => [
        'bootstrap' => [
            'buttons' => 'btn btn-primary btn-sm',
            'input' => 'form-control form-control-sm',
            'select' => 'form-control form-control-sm',
        ],
        'tailwind' => [
            'buttons' => '',
            'input' => '',
            'select' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DT Table Column Settings
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default settings for your DataTable columns.
    |
    */
    'columns' => [
        'search_types' => ['none', 'input', 'select'],
    ],
];
