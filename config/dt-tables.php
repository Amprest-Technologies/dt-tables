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
        'theme' => 'bootstrap5',
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
        'bootstrap5' => [
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
