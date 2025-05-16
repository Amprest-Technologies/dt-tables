<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel DataTable Defaults
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default settings for your DataTables. These
    | settings will be used when you create a new DataTable instance.
    |
    */
    'defaults' => [
        'settings' => [
            'buttons' => ['copy', 'colvis', 'csv', 'excel']
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Laravel DataTable Column Settings
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default settings for your DataTable columns.
    |
    */
    'columns' => [
        'search_types' => ['input', 'select'],
        'data_types' => ['string', 'num'],
    ],
];
