<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Json File Path
    |--------------------------------------------------------------------------
    |
    | Here you may specify the path to the JSON file that will be used to
    |
    */
    'data_source' => base_path('dt-tables.json'),

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
